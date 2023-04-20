<?php

namespace App\Repository;

use App\Entity\GeneratedArticles;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GeneratedArticles>
 *
 * @method GeneratedArticles|null find($id, $lockMode = null, $lockVersion = null)
 * @method GeneratedArticles|null findOneBy(array $criteria, array $orderBy = null)
 * @method GeneratedArticles[]    findAll()
 * @method GeneratedArticles[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GeneratedArticlesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GeneratedArticles::class);
    }

}
