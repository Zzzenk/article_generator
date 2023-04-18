<?php

namespace App\Service;

use App\Controller\ProfileController;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mime\Address;

class ProfileUpdateService extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
    }

    public function updateProfile(Request $request, EmailVerifier $emailVerifier, $user)
    {
        if ($request->request->has('input')) {
            $newEmail = $request->get('input')['Email'];
            // check match password & confirm password
            if ($request->get('input')['Password'] != $request->get('input')['ConfirmPassword']) {
                $this->addFlash('profile_update_error', 'Пароли не совпадают');

                // check password length
            } elseif ($request->get('input')['Password'] != null && strlen($request->get('input')['Password']) < 6 || strlen($request->get('input')['Password']) > 100) {
                $this->addFlash('profile_update_error', 'Длинна пароля должна быть от 6 до 100 символов');

                // check is input email is the new one
            } elseif ($newEmail != $user->getEmail()) {
                // check existing email
                if ($this->userRepository->findOneBy(['email' => $newEmail]) != null) {
                    $this->addFlash('profile_update_error', 'Данный Email уже зарегистрирован');
                    return $this->redirectToRoute('app_dashboard_profile');
                }

                // generate a signed url and email it to the user
                $emailVerifier->changeUserEmail('app_verify_email_change',
                    $user,
                    (new TemplatedEmail())
                        ->from(new Address($_ENV['NOREPLY_EMAIL'], ProfileController::EMAIL_FROM))
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
    }
}