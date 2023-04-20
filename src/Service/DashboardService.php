<?php

namespace App\Service;

use App\Entity\GeneratedArticles;
use Doctrine\ORM\EntityManagerInterface;

class DashboardService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    )
    {
    }

    public function lastCreatedArticles($user)
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

    public function last2Hours($user)
    {
        $date = (new \DateTime('-2 hours'))->format('Y-m-d H:i:s');
        $qb = $this->em->createQueryBuilder();
        $query = $qb
            ->select('ga.template')
            ->from(GeneratedArticles::class, 'ga')
            ->andWhere('ga.createdAt > ?1', 'ga.user = ?2')
            ->setParameter(1, $date)
            ->setParameter(2, $user->getId())
            ->getQuery();

        if ($query->execute() >= 2) {
            return false;
        } else {
            return true;
        }
    }

    public function getArticleTemplate($id)
    {
        $qb = $this->em->createQueryBuilder();
        $query = $qb
            ->select('ga.template')
            ->from(GeneratedArticles::class, 'ga')
            ->andWhere('ga.id = ?1')
            ->setParameter(1, $id)
            ->getQuery();
        return $query->execute();
    }
}