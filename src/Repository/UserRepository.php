<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

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
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
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

    public function orderSubscription(string $email, string $subscription)
    {
        $sql = 'UPDATE user u 
        SET u.subscription = :subscription, u.subscription_expires_at = :expiresAt
        WHERE u.email = :email';
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->bindValue('subscription', $subscription);
        $stmt->bindValue('expiresAt', (new \DateTime('+1 week'))->format('Y-m-d H-i-s'));
        $stmt->bindValue('email', $email);
        $stmt->executeStatement();

        return true;
    }

    public function resetSubscription(string $email)
    {
        $sql = 'UPDATE user u
        SET u.subscription = :subscription, u.subscription_expires_at = null
        WHERE u.email = :email';
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->bindValue('subscription', 'FREE');
        $stmt->bindValue('email', $email);
        $stmt->executeStatement();
    }

}
