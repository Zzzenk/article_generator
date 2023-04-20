<?php

namespace App\Controller;

use App\Repository\ApiTokenRepository;
use App\Security\EmailVerifier;
use App\Service\ProfileUpdateService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProfileController extends AbstractController
{
    #[Route('/dashboard/profile', name: 'app_dashboard_profile')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function profile(ApiTokenRepository $apiTokenRepository): Response
    {
        $user = $this->getUser();

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
    public function profileUpdate(Request $request, ProfileUpdateService $profileUpdateService, EmailVerifier $emailVerifier): Response
    {
        $user = $this->getUser();

        if ($request->query->has('newToken')) {
            $profileUpdateService->insertNewToken($user);
        }

        $profileUpdateService->updateProfile($request, $emailVerifier, $user);

        return $this->redirectToRoute('app_dashboard_profile');
    }

    #[Route('/verify/email_change', name: 'app_verify_email_change')]
    public function verifyEmailChange(Request $request, ApiTokenRepository $apiTokenRepository, ProfileUpdateService $profileUpdateService): Response
    {
        $user = $apiTokenRepository->findOneBy(['token' => $request->get('apiToken')])->getUser();

        $profileUpdateService->updateEmail($user->getNewEmail(), $user->getEmail());

        return $this->redirectToRoute('app_dashboard_profile');
    }
}