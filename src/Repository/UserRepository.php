<?php

namespace App\Repository;

use App\Entity\GeneratedArticles;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{

    public function __construct(
        ManagerRegistry $registry,
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct($registry, User::class);
    }

    public function lastCreatedArticles($user): array
    {
        $date = (new \DateTime('-1 month'))->format('Y-m-d H:i:s');
        $qb = $this->em->createQueryBuilder();
        $query = $qb
            ->select('ga.template')
            ->from(GeneratedArticles::class, 'ga')
            ->andWhere('ga.createdAt > ?1', 'ga.user = ?2')
            ->setParameter(1, $date)
            ->setParameter(2, $user->getId())
            ->getQuery();
        return $query->execute();
    }

    public function last2Hours($user): bool
    {
        $qb = $this->em->createQueryBuilder();
        $query = $qb
            ->select('ga.template')
            ->from(GeneratedArticles::class, 'ga')
            ->andWhere('ga.createdAt > :createdAt')
            ->andWhere('ga.user = :user')
            ->setParameter('createdAt', (new \DateTime('-2 hours'))->format('Y-m-d H:i:s'))
            ->setParameter('user', $user->getId())
            ->getQuery();

        if (count($query->execute()) >= 2) {
            return false;
        } else {
            return true;
        }
    }
}
