<?php

namespace App\Repository;

use App\Entity\ArticleImages;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ArticleImages>
 *
 * @method ArticleImages|null find($id, $lockMode = null, $lockVersion = null)
 * @method ArticleImages|null findOneBy(array $criteria, array $orderBy = null)
 * @method ArticleImages[]    findAll()
 * @method ArticleImages[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleImagesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArticleImages::class);
    }

}
