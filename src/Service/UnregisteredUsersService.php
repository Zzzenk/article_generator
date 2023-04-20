<?php

namespace App\Service;

use App\Entity\UnregisteredUsers;
use Doctrine\ORM\EntityManagerInterface;

class UnregisteredUsersService
{
    public function __construct(
        private readonly EntityManagerInterface $em
    )
    {
    }

    public function checkIP($ip)
    {
        $qb = $this->em->createQueryBuilder();
        $ips = $qb
            ->select('u.IP')
            ->from(UnregisteredUsers::class, 'u')
            ->where('u.IP LIKE :ip')
            ->setParameter('ip', $ip)
            ->getQuery()
            ->getResult()
            ;

        if (array_search($ip, array_column($ips, 'ip')) === false) {
            return true;
        } else {
            return false;
        }
    }

    public function addIP($ip)
    {
        $newIp = new UnregisteredUsers();
        $newIp->setIP($ip);
        $this->em->persist($newIp);
        $this->em->flush();
    }

}