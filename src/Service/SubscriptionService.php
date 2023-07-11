<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;

class SubscriptionService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly Security $security,
        private readonly UserRepository $userRepository,
    ) {
    }

    /**
     * @param MailerInterface $mailer
     * @param UserInterface $user
     * @param string $subscription
     * @return void
     * @throws TransportExceptionInterface
     */
    public function sendConfirmationEmail(MailerInterface $mailer, UserInterface $user, string $subscription): void
    {
        $email = new TemplatedEmail();
        $email
            ->to(new Address($user->getUserIdentifier(), $user->getFirstName()))
            ->from(new Address($_ENV['EMAIL_NO_REPLY'], $_ENV['EMAIL_FROM']))
            ->subject('Оформление подписки')
            ->htmlTemplate('email/subscription_order.html.twig')
            ->context([
                'subscription' => $subscription,
                'expiresAt' => (new \DateTime('+7 days'))->format('Y.m.d H:i:s'),
            ]);
        $mailer->send($email);
    }

    /**
     * @param UserInterface $user
     * @param array $newRole
     * @return void
     */
    public function refreshToken(UserInterface $user, array $newRole): void
    {
        $this->tokenStorage->setToken(
            new UsernamePasswordToken($user, 'main', $newRole)
        );
    }

    /**
     * @param UserInterface $user
     * @param string $subscription
     * @return void
     */
    public function orderSubscription(UserInterface $user, string $subscription): void
    {
        /** @var User|null $user */
        $oldRoles = implode(', ', $user->getRoles());
        $newRole = str_replace(['ROLE_FREE', 'ROLE_PLUS', 'ROLE_PRO'], 'ROLE_' . $subscription, $oldRoles);
        $newRoleArray = explode(', ', $newRole);

        $query = $user
            ->setRoles($newRoleArray)
            ->setSubscriptionExpiresAt((new \DateTime('+1 week')))
        ;
        $this->em->persist($query);
        $this->em->flush();

        $this->refreshToken($user, $newRoleArray);
    }

    /**
     * @param UserInterface $user
     * @return void
     */
    public function resetSubscription(UserInterface $user): void
    {
        /** @var User|null $user */

        $oldRoles = implode(', ', $user->getRoles());
        $newRole = str_replace([', ROLE_PLUS', ', ROLE_PRO'], ', ROLE_FREE', $oldRoles);

        $newRoleArray = explode(', ', $newRole);

        $this->em->persist($user->setRoles($newRoleArray));
        $this->em->flush();

        $this->refreshToken($user, $newRoleArray);
    }

    /**
     * @param UserInterface $user
     * @return string
     */
    public function checkSubscription(UserInterface $user): string
    {
        /** @var User|null $user */

        if (array_search('ROLE_FREE', $user->getRoles()) == 1) {
            return 'FREE';
        } else if (array_search('ROLE_PLUS', $user->getRoles())) {
            if ($user->getSubscriptionExpiresAt() < (new \DateTime())) {
                $this->resetSubscription($user);
                return 'FREE';
            } else {
                return 'PLUS';
            }
        } else if (array_search('ROLE_PRO', $user->getRoles())) {
            if ($user->getSubscriptionExpiresAt() < (new \DateTime())) {
                $this->resetSubscription($user);
                return 'FREE';
            } else {
                return 'PRO';
            }
        }
    }

    /**
     * @return bool
     */
    public function checkDisabledFree(): bool
    {
        if ($this->security->isGranted('ROLE_FREE')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param UserInterface $user
     * @return bool
     */
    public function checkDisabled2Hours(UserInterface $user): bool
    {
        /** @var User|null $user */

        if ($this->userRepository->last2Hours($user) === false) {
            if ($this->checkSubscription($user) == 'FREE' || $this->checkSubscription($user) == 'PLUS') {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
