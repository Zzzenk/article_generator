<?php

namespace App\Controller;

use App\Repository\ApiTokenRepository;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProfileController extends AbstractController
{
    private ApiTokenRepository $apiTokenRepository;
    private EmailVerifier $emailVerifier;
    private UserRepository $userRepository;
    private Security $security;

    public function __construct(UserRepository $userRepository, Security $security, ApiTokenRepository $apiTokenRepository, EmailVerifier $emailVerifier)
    {
        $this->apiTokenRepository = $apiTokenRepository;
        $this->emailVerifier = $emailVerifier;
        $this->userRepository = $userRepository;
        $this->security = $security;
    }

    #[Route('/dashboard/profile', name: 'app_dashboard_profile')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function profile()
    {
        $user = $this->security->getUser();
        $apiToken = $this->apiTokenRepository->findOneBy(['user' => $user->getId()])->getToken();

        return $this->render('dashboard/dashboard_profile.html.twig', [
            'menuActive' => 'profile',
            'email' => $user->getEmail(),
            'firstName' => $user->getFirstName(),
            'apiToken' => $apiToken,
        ]);
    }

    #[Route('/dashboard/profile/update', name: 'app_dashboard_profile_update')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function profileUpdate(Request $request)
    {
        $user = $this->security->getUser();

        if ($request->query->has('newToken')) {
            $this->apiTokenRepository->genereteNewApiToken($user->getId());
        }

        if ($request->request->has('input')) {
            $newEmail = $request->get('input')['Email'];
            // check match password & confirm password
            if ($request->get('input')['Password'] != $request->get('input')['ConfirmPassword']) {
                $this->addFlash('profile_update_error', 'Пароли не совпадают');

            // check password length
            } elseif ($request->get('input')['Password'] != null && strlen($request->get('input')['Password']) < 6 || strlen($request->get('input')['Password']) > 100 ) {
                $this->addFlash('profile_update_error', 'Длинна пароля должна быть от 6 до 100 символов');

            // check is input email is the new one
            } elseif ($newEmail != $user->getEmail()) {
                // check existing email
                if ($this->userRepository->findOneBy(['email' => $newEmail]) != null) {
                $this->addFlash('profile_update_error', 'Данный Email уже зарегистрирован');
                return $this->redirectToRoute('app_dashboard_profile'); }

                // generate a signed url and email it to the user
                $this->emailVerifier->changeUserEmail('app_verify_email_change',
                    $user,
                    (new TemplatedEmail())
                        ->from(new Address('noreply@articlegenerator.ru', 'Article Generator - генератор статей'))
                        ->to($user->getEmail())
                        ->subject('Подтвердите изменение Email')
                        ->htmlTemplate('email/confirm_change_email.html.twig'),
                    $request->get('input')['Email']);

                $this->userRepository->setTempEmail($request->get('input')['Email'], $user->getEmail());
                $this->addFlash('email_confirm', 'Для завершения обновления Email перейдите по ссылке в письме');
            } else {
                $this->userRepository->updateProfile($request->get('input'), $user->getEmail());
                $this->addFlash('profile_changed', 'Профиль успешно изменен');
            }

        }
        return $this->redirectToRoute('app_dashboard_profile');
    }

    #[Route('/verify/email_change', name: 'app_verify_email_change')]
    public function verifyEmailChange(Request $request)
    {
        $id = $request->get('id');
        $user = $this->userRepository->find($id);

        $this->userRepository->updateEmail($user->getNewEmail(), $user->getEmail());

        return $this->redirectToRoute('app_dashboard_profile');
    }
}