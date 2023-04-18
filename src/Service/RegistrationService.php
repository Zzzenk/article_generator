<?php

namespace App\Service;

use App\Controller\RegistrationController;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationService extends AbstractController
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
    )
    {
    }

    public function register($emailVerifier, $user, $apiToken, $form)
    {
        $this->addFlash('verify_email', 'Для окончания регистрации подтвердите свой Email');

        // encode the plain password
        $user->setPassword(
            $this->userPasswordHasher->hashPassword(
                $user,
                $form->get('password')->getData()
            )
        )
            ->setRoles(["ROLE_USER", "ROLE_FREE"]);

        $apiToken->setToken(sha1(uniqid('token')));

        $this->entityManager->persist($user);
        $this->entityManager->persist($apiToken);
        $this->entityManager->flush();

        // generate a signed url and email it to the user
        /** @var EmailVerifier|null $emailVerifier */
        $emailVerifier->sendEmailConfirmation('app_verify_email', $user,
            (new TemplatedEmail())
                ->from(new Address($_ENV['NOREPLY_EMAIL'], RegistrationController::EMAIL_FROM))
                ->to($user->getEmail())
                ->subject('Подтвердите свой Email')
                ->htmlTemplate('email/confirmation_email.html.twig')
        );
    }
}