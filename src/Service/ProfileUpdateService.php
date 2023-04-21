<?php

namespace App\Service;

use App\Repository\ApiTokenRepository;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ProfileUpdateService extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly ApiTokenRepository $apiTokenRepository
    ) {
    }

    /**
     * @param Request $request
     * @param EmailVerifier $emailVerifier
     * @param $user
     * @return RedirectResponse|void
     */
    public function updateProfile(Request $request, EmailVerifier $emailVerifier, $user)
    {
        if ($request->request->has('input')) {
            $input = $request->get('input');

            // check match password & confirm password
            if ($input['Password'] != $input['ConfirmPassword']) {
                $this->addFlash('profile_update_error', 'Пароли не совпадают');

                // check password length
            } elseif ($input['Password'] != null && strlen($input['Password']) < 6 || strlen($input['Password']) > 100) {
                $this->addFlash('profile_update_error', 'Длинна пароля должна быть от 6 до 100 символов');

                // check is input email is the new one
            } elseif ($input['Email'] != $user->getEmail()) {
                // check existing email
                if ($this->userRepository->findOneBy(['email' => $input['Email']]) != null) {
                    $this->addFlash('profile_update_error', 'Данный Email уже зарегистрирован');
                    return $this->redirectToRoute('app_dashboard_profile');
                }

                // generate a signed url and email it to the user
                $emailVerifier->changeUserEmail('app_verify_email_change',
                    $user,
                    (new TemplatedEmail())
                        ->from(new Address($_ENV['EMAIL_NO_REPLY'], $_ENV['EMAIL_FROM']))
                        ->to($user->getEmail())
                        ->subject('Подтвердите изменение Email')
                        ->htmlTemplate('email/confirm_change_email.html.twig'),
                    $input['Email']);

                $this->setTempEmail($input['Email'], $user->getEmail());
                $this->addFlash('email_confirm', 'Для завершения обновления Email перейдите по ссылке в письме');
            } else {
                //$this->userRepository->updateProfile($input, $user->getEmail());

                $user = $this->userRepository->findOneBy(['email' => $user->getEmail()]);
                $user->setFirstName($input['Name']);

                if ($input['Password'] != "") {
                    $user
                        ->setPassword(
                            $this->userPasswordHasher->hashPassword(
                                $user,
                                $input['Password']
                            ));
                }

                $this->em->persist($user);
                $this->em->flush();


                $this->addFlash('profile_changed', 'Профиль успешно изменен');
            }
        }
    }

    /**
     * @param int $userId
     * @return void
     */
    public function insertNewToken(int $userId): void
    {
        $token = $this->apiTokenRepository->findOneBy(['user' => $userId]);

        $this->em->persist($token->setToken($this->generateNewToken()));
        $this->em->flush();
    }

    /**
     * @return string
     */
    public function generateNewToken(): string
    {
        return sha1(uniqid('token'));
    }

    /**
     * @param string $newEmail
     * @param string $oldEmail
     * @return void
     */
    public function setTempEmail(string $newEmail, string $oldEmail): void
    {
        $em = $this->em;
        $user = $this->userRepository->findOneBy(['email' => $oldEmail]);

        $user->setNewEmail($newEmail);

        $em->persist($user);
        $em->flush();
    }

    /**
     * @param string $newEmail
     * @param string $oldEmail
     * @return void
     */
    public function updateEmail(string $newEmail, string $oldEmail): void
    {
        $em = $this->em;
        $user = $this->userRepository->findOneBy(['email' => $oldEmail]);

        $user->setEmail($newEmail);
        $user->setNewEmail(null);

        $em->persist($user);
        $em->flush();
    }
}
