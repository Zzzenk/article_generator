<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SubscriptionController extends AbstractController
{
    private UserRepository $userRepository;
    private Security $security;

    public function __construct(UserRepository $userRepository, Security $security)
    {
        $this->userRepository = $userRepository;
        $this->security = $security;
    }

    #[Route('/dashboard/subscription', name: 'app_dashboard_subscription')]
    public function subscription()
    {
        $this->checkSubscription();
        /** @var User|null $user */
        $user = $this->userRepository->findOneBy(['email' => $this->security->getUser()->getUserIdentifier()], []);
        $expiresAt = $user->getSubscriptionExpiresAt() ? $user->getSubscriptionExpiresAt()?->format('Y.m.d') : null;
        return $this->render('dashboard/dashboard_subscription.html.twig', [
            'expiresAt' => $expiresAt,
            'subscription' => $this->checkSubscription(),
        ]);
    }

    #[Route('/dashboard/subscription/order', name: 'app_dashboard_subscription_order')]
    public function orderSubscription(Request $request)
    {
        $subscription = $request->get('order_subscription');
        $userEmail = $this->security->getUser()->getUserIdentifier();
        $this->userRepository->orderSubscription($userEmail, $subscription);

        return $this->redirectToRoute('app_dashboard_subscription');
    }

    public function checkSubscription()
    {
        /** @var User|null $user */
        $user = $this->userRepository->findOneBy(['email' => $this->security->getUser()->getUserIdentifier()], []);

        if ($user->getSubscription() == 'FREE') {
            return 'FREE';
        } else if ($user->getSubscription() == 'PLUS' || $user->getSubscription() == 'PRO') {
            if ($user->getSubscriptionExpiresAt() < (new \DateTime('now'))) {
                $this->userRepository->resetSubscription($user->getEmail());
                return 'FREE';
            } else {
                return $user->getSubscription();
            }
        }
    }

}