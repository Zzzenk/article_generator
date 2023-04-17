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

    public function save(UnregisteredUsers $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(UnregisteredUsers $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function checkIP($ip)
    {
        $sql = 'SELECT ip FROM unregistered_users';
        $ips = $this->getEntityManager()->getConnection()->executeQuery($sql)->fetchAllAssociative();

        if (array_search($ip, array_column($ips, 'ip')) === false) {
            return true;
        } else {
            return false;
        }
    }

    public function addIP($ip)
    {
        $sql = 'INSERT INTO unregistered_users (ip)
        VALUES (:ip)';
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->bindValue('ip', $ip);
        $stmt->executeStatement();
    }
}