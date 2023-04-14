<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class SubscriptionController extends AbstractController
{
    private UserRepository $userRepository;
    private Security $security;
    private MailerInterface $mailer;

    public function __construct(UserRepository $userRepository, Security $security, MailerInterface $mailer)
    {
        $this->userRepository = $userRepository;
        $this->security = $security;
        $this->mailer = $mailer;
    }

    #[Route('/dashboard/subscription', name: 'app_dashboard_subscription')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function subscription()
    {
        $this->userRepository->checkSubscription(null);
        /** @var User|null $user */
        $user = $this->security->getUser();
        $expiresAt = $user->getSubscriptionExpiresAt() ? $user->getSubscriptionExpiresAt()?->format('Y.m.d') : null;
        return $this->render('dashboard/dashboard_subscription.html.twig', [
            'menuActive' => 'subscription',
            'expiresAt' => $expiresAt,
            'subscription' => $this->userRepository->checkSubscription(null),
        ]);
    }

    #[Route('/dashboard/subscription/order', name: 'app_dashboard_subscription_order')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function orderSubscription(Request $request)
    {
        /** @var User|null $user */
        $user = $this->security->getUser();;

        $subscription = $request->get('order_subscription');
        $userEmail = $this->security->getUser()->getUserIdentifier();
        $this->userRepository->orderSubscription($userEmail, $subscription);
        $email = new TemplatedEmail();
        $email
            ->to(new Address($userEmail, $user->getFirstName()))
            ->from(new Address('noreply@articlegenerator.ru', 'Article Generator'))
            ->subject('Оформление подписки')
            ->htmlTemplate('email/subscription_order.html.twig')
            ->context([
                'subscription' => $subscription,
                'expiresAt' => (new \DateTime('+7 days'))->format('Y.m.d H:i:s'),
            ]);
        $this->mailer->send($email);

        return $this->redirectToRoute('app_dashboard_subscription');
    }


}