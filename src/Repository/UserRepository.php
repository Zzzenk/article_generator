<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{

    private UserPasswordHasherInterface $userPasswordHasher;
    private TokenStorageInterface $tokenStorage;
    private Security $security;
    private GeneratedArticlesRepository $generatedArticlesRepository;
    private ApiTokenRepository $apiTokenRepository;

    public function __construct(ManagerRegistry $registry,
                                UserPasswordHasherInterface $userPasswordHasher,
                                TokenStorageInterface $tokenStorage,
                                Security $security,
                                GeneratedArticlesRepository $generatedArticlesRepository,
                                ApiTokenRepository $apiTokenRepository)
    {
        parent::__construct($registry, User::class);
        $this->userPasswordHasher = $userPasswordHasher;
        $this->tokenStorage = $tokenStorage;
        $this->security = $security;
        $this->generatedArticlesRepository = $generatedArticlesRepository;
        $this->apiTokenRepository = $apiTokenRepository;
    }

    public function save(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);

        $this->save($user, true);
    }

    public function refreshToken(UserInterface $user, $newRole): void
    {
        $this->tokenStorage->setToken(
            new UsernamePasswordToken($user, 'main', $newRole)
        );
    }

    public function findRoles(string $email)
    {
        $sqlUser = 'SELECT roles FROM user
                    WHERE id = :user_id';
        $stmt = $this->getEntityManager()->getConnection()->prepare($sqlUser);
        $stmt->bindValue('user_id', $this->findOneBy(['email' => $email])->getId());
        return $stmt->executeQuery()->fetchAllAssociative()[0]['roles'];
    }

    public function orderSubscription(string $email, string $subscription)
    {
        $user = $this->findOneBy(['email' => $email]);
        $oldRoles = $this->findRoles($user->getEmail());

        $newRole = str_replace(['"ROLE_FREE"', '"ROLE_PLUS"', '"ROLE_PRO"'], '"ROLE_' . $subscription . '"', $oldRoles);
        $newRoleArray = (explode(' ', str_replace(['"', ',', '[', ']'], '', $newRole)));

        $sql = 'UPDATE user u
        SET u.roles = :roles, u.subscription_expires_at = :expiresAt
        WHERE u.email = :email';
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->bindValue('roles', $newRole);
        $stmt->bindValue('expiresAt', (new \DateTime('+1 week'))->format('Y-m-d H-i-s'));
        $stmt->bindValue('email', $email);
        $stmt->executeStatement();

        $this->refreshToken($user, $newRoleArray);

        return true;
    }

    public function resetSubscription(string $email)
    {
        $user = $this->findOneBy(['email' => $email]);
        $oldRoles = $this->findRoles($user->getEmail());
        $newRole = str_replace([', "ROLE_PLUS"', ', "ROLE_PRO"'], ', "ROLE_FREE"', $oldRoles);
        $newRoleArray = (explode(' ', str_replace(['"', ',', '[', ']'], '', $newRole)));

        $sql = 'UPDATE user u
        SET u.roles = :roles, u.subscription_expires_at = null
        WHERE u.email = :email';
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->bindValue('email', $email);
        $stmt->bindValue('roles', $newRole);
        $stmt->executeStatement();

        $this->refreshToken($user, $newRoleArray);
    }


    public function checkSubscription($token)
    {
        /** @var User|null $user */
        if ($token === null) {
            $user = $this->findOneBy(['email' => $this->security->getUser()->getUserIdentifier()]);
        } else {
            $user = $this->apiTokenRepository->findOneBy(['token' => $token])->getUser();
        }

        if (array_search('ROLE_FREE', $user->getRoles()) == 1) {
            return 'FREE';
        } else if (array_search('ROLE_PLUS', $user->getRoles())) {
            if ($user->getSubscriptionExpiresAt() < (new \DateTime('now'))) {
                $this->resetSubscription($user->getEmail());
                return 'FREE';
            } else {
                return 'PLUS';
            }
        } else if (array_search('ROLE_PRO', $user->getRoles())) {
            if ($user->getSubscriptionExpiresAt() < (new \DateTime('now'))) {
                $this->resetSubscription($user->getEmail());
                return 'FREE';
            } else {
                return 'PRO';
            }
        }
    }

    public function checkDisabledFree()
    {
        if ($this->security->isGranted('ROLE_FREE')) {
            return 'disabled';
        } else {
            return false;
        }
    }

    public function checkDisabled2Hours($token)
    {
        /** @var User|null $user */
        if ($token === null) {
            $user = $this->findOneBy(['email' => $this->security->getUser()->getUserIdentifier()]);
        } else {
            $user = $this->apiTokenRepository->findOneBy(['token' => $token]);
        }

        if ($this->generatedArticlesRepository->last2Hours($user) === false) {
            if ($this->checkSubscription($token) == 'FREE' || $this->checkSubscription($token) == 'PLUS') {
                return 'disabled';
            } else {
                return false;
            }
        } else {
            return false;
        }
    }


    public function updateProfile($newData, string $userEmail)
    {
        $em = $this->getEntityManager();
        $user = $this->findOneBy(['email' => $userEmail]);
        $user->setFirstName($newData['Name']);

        if ($newData['Password'] != "") {
            $user
                ->setPassword(
                $this->userPasswordHasher->hashPassword(
                    $user,
                    $newData['Password']
                ));
        }

        $em->persist($user);
        $em->flush();
    }

    public function setTempEmail(string $newEmail, string $oldEmail)
    {
        $em = $this->getEntityManager();
        $user = $this->findOneBy(['email' => $oldEmail]);

        $user->setNewEmail($newEmail);

        $em->persist($user);
        $em->flush();
    }

    public function updateEmail(string $newEmail, string $oldEmail)
    {
        $em = $this->getEntityManager();
        $user = $this->findOneBy(['email' => $oldEmail]);

        $user->setEmail($newEmail);
        $user->setNewEmail(null);

        $em->persist($user);
        $em->flush();
    }

}
