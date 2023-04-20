<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\SecurityBundle\Security;
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
        private readonly DashboardService $dashboardService,
    ) {
    }

    public function sendConfirmationEmail(MailerInterface $mailer, $user, $subscription)
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

    public function refreshToken(UserInterface $user, $newRole): void
    {
        $this->tokenStorage->setToken(
            new UsernamePasswordToken($user, 'main', $newRole)
        );
    }

    public function findRoles($user)
    {
        $qb = $this->em->createQueryBuilder();
        return $qb
            ->select('u.roles')
            ->from(User::class, 'u')
            ->where('u.id = :id')
            ->setParameter('id', $user->getId())
            ->getQuery()
            ->getResult()
            ;
    }

    public function orderSubscription($user, string $subscription)
    {
        /** @var User|null $user */
        $oldRoles = implode(', ', $this->findRoles($user)[0]['roles']);

        $newRole = str_replace(['ROLE_FREE', 'ROLE_PLUS', 'ROLE_PRO'], 'ROLE_' . $subscription, $oldRoles);
        $newRoleArray = explode(', ', $newRole);

        $query = $user
            ->setRoles($newRoleArray)
            ->setSubscriptionExpiresAt((new \DateTime('+1 week')))
        ;
        $this->em->persist($query);
        $this->em->flush();

        $this->refreshToken($user, $newRoleArray);

        return true;
    }

    public function resetSubscription($user)
    {
        /** @var User|null $user */

        $oldRoles = implode(', ', $this->findRoles($user)[0]['roles']);
        $newRole = str_replace([', ROLE_PLUS', ', ROLE_PRO'], ', ROLE_FREE', $oldRoles);

        $newRoleArray = explode(', ', $newRole);

        $this->em->persist($user->setRoles($newRoleArray));
        $this->em->flush();

        $this->refreshToken($user, $newRoleArray);
    }


    public function checkSubscription($user)
    {
        /** @var User|null $user */

        if (array_search('ROLE_FREE', $user->getRoles()) == 1) {
            return 'FREE';
        } else if (array_search('ROLE_PLUS', $user->getRoles())) {
            if ($user->getSubscriptionExpiresAt() < (new \DateTime('now'))) {
                $this->resetSubscription($user);
                return 'FREE';
            } else {
                return 'PLUS';
            }
        } else if (array_search('ROLE_PRO', $user->getRoles())) {
            if ($user->getSubscriptionExpiresAt() < (new \DateTime('now'))) {
                $this->resetSubscription($user);
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

    public function checkDisabled2Hours($user)
    {
        /** @var User|null $user */

        if ($this->dashboardService->last2Hours($user) === false) {
            if ($this->checkSubscription($user) == 'FREE' || $this->checkSubscription($user) == 'PLUS') {
                return 'disabled';
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

}