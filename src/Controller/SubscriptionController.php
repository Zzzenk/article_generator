<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class SubscriptionController extends AbstractController
{
    const EMAIL_FROM = 'Article Generator - генератор статей';

    #[Route('/dashboard/subscription', name: 'app_dashboard_subscription')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function subscription(UserRepository $userRepository, Security $security): Response
    {
        $userRepository->checkSubscription(null);

        /** @var User|null $user */
        $user = $security->getUser();
        $expiresAt = $user->getSubscriptionExpiresAt() ? $user->getSubscriptionExpiresAt()?->format('Y.m.d') : null;
        return $this->render('dashboard/dashboard_subscription.html.twig', [
            'menuActive' => 'subscription',
            'expiresAt' => $expiresAt,
            'subscription' => $userRepository->checkSubscription(null),
        ]);
    }

    #[Route('/dashboard/subscription/order', name: 'app_dashboard_subscription_order')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function orderSubscription(Request $request, MailerInterface $mailer, UserRepository $userRepository, Security $security): Response
    {
        /** @var User|null $user */
        $user = $security->getUser();;

        $subscription = $request->get('order_subscription');
        $userEmail = $user->getUserIdentifier();
        $userRepository->orderSubscription($userEmail, $subscription);

        $email = new TemplatedEmail();
        $email
            ->to(new Address($userEmail, $user->getFirstName()))
            ->from(new Address($_ENV['NOREPLY_EMAIL'], SubscriptionController::EMAIL_FROM))
            ->subject('Оформление подписки')
            ->htmlTemplate('email/subscription_order.html.twig')
            ->context([
                'subscription' => $subscription,
                'expiresAt' => (new \DateTime('+7 days'))->format('Y.m.d H:i:s'),
            ]);
        $mailer->send($email);

        return $this->redirectToRoute('app_dashboard_subscription');
    }


}