<?php

namespace App\Repository;

use App\Entity\UnregisteredUsers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UnregisteredUsers>
 *
 * @method UnregisteredUsers|null find($id, $lockMode = null, $lockVersion = null)
 * @method UnregisteredUsers|null findOneBy(array $criteria, array $orderBy = null)
 * @method UnregisteredUsers[]    findAll()
 * @method UnregisteredUsers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UnregisteredUsersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UnregisteredUsers::class);
    }

}