<?php

namespace App\Controller;

use App\Service\SubscriptionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class SubscriptionController extends AbstractController
{
    #[Route('/dashboard/subscription', name: 'app_dashboard_subscription')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function subscription(SubscriptionService $subscriptionService): Response
    {
        $user = $this->getUser();

        $subscriptionService->checkSubscription($user);

        $expiresAt = $user->getSubscriptionExpiresAt() ? $user->getSubscriptionExpiresAt()?->format('Y.m.d') : null;

        return $this->render('dashboard/dashboard_subscription.html.twig', [
            'menuActive' => 'subscription',
            'expiresAt' => $expiresAt,
            'subscription' => $subscriptionService->checkSubscription($user),
        ]);
    }

    #[Route('/dashboard/subscription/order', name: 'app_dashboard_subscription_order')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function orderSubscription(Request $request, MailerInterface $mailer, SubscriptionService $subscriptionService): Response
    {
        $user = $this->getUser();

        $subscription = $request->get('order_subscription');

        $subscriptionService->orderSubscription($user, $subscription);
        $subscriptionService->sendConfirmationEmail($mailer, $user, $subscription);

        return $this->redirectToRoute('app_dashboard_subscription');
    }
}
