<?php

namespace App\Controller;

use App\Repository\ApiTokenRepository;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\Service\ProfileUpdateService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProfileController extends AbstractController
{
    const EMAIL_FROM = 'Article Generator - генератор статей';

    #[Route('/dashboard/profile', name: 'app_dashboard_profile')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function profile(ApiTokenRepository $apiTokenRepository, Security $security): Response
    {
        $user = $security->getUser();
        $apiToken = $apiTokenRepository->findOneBy(['user' => $user->getId()])->getToken();

        return $this->render('dashboard/dashboard_profile.html.twig', [
            'menuActive' => 'profile',
            'email' => $user->getEmail(),
            'firstName' => $user->getFirstName(),
            'apiToken' => $apiToken,
        ]);
    }

    #[Route('/dashboard/profile/update', name: 'app_dashboard_profile_update')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function profileUpdate(Request $request, ProfileUpdateService $profileUpdateService, ApiTokenRepository $apiTokenRepository, Security $security, EmailVerifier $emailVerifier): Response
    {
        $user = $security->getUser();

        if ($request->query->has('newToken')) {
            $apiTokenRepository->genereteNewApiToken($user->getId());
        }

        $profileUpdateService->updateProfile($request, $emailVerifier, $user);

        return $this->redirectToRoute('app_dashboard_profile');
    }

    #[Route('/verify/email_change', name: 'app_verify_email_change')]
    public function verifyEmailChange(Request $request, UserRepository $userRepository): Response
    {
        $user = $userRepository->find($request->get('id'));

        $userRepository->updateEmail($user->getNewEmail(), $user->getEmail());

        return $this->redirectToRoute('app_dashboard_profile');
    }
}