<?php

namespace App\Service;

use App\Entity\UnregisteredUsers;
use Doctrine\ORM\EntityManagerInterface;

class UnregisteredUsersService
{
    public function __construct(
        private readonly EntityManagerInterface $em
    ) {
    }

    /**
     * @param string $ip
     * @return void
     */
    public function addIP(string $ip): void
    {
        $newIp = new UnregisteredUsers();
        $newIp->setIP($ip);
        $this->em->persist($newIp);
        $this->em->flush();
    }
}
