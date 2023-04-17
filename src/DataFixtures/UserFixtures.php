<?php

namespace App\DataFixtures;

use App\Entity\ApiToken;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements OrderedFixtureInterface
{

    private UserPasswordHasherInterface $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function getOrder(): int
    {
        return 1;
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user
            ->setFirstName('Админ')
            ->setEmail('zzzenk@gmail.com')
            ->setRoles(["ROLE_ADMIN", "ROLE_PRO"])
            ->setIsVerified(true)
            ->setSubscriptionExpiresAt(new \DateTime('+1 year'))
            ->setPassword($this->userPasswordHasher->hashPassword(
                $user,
                '123456'
            ));
        $manager->persist(new ApiToken($user));
        $manager->persist($user);

        $user = new User();
        $user
            ->setFirstName('Test')
            ->setEmail('test@test.ru')
            ->setRoles(["ROLE_USER", "ROLE_PLUS"])
            ->setIsVerified(true)
            ->setSubscriptionExpiresAt(new \DateTime('+1 week'))
            ->setPassword($this->userPasswordHasher->hashPassword(
                $user,
                '123456'
            ));
        $manager->persist(new ApiToken($user));
        $manager->persist($user);

        $manager->flush();
    }
}
