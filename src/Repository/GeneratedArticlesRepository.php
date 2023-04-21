<?php

namespace App\Repository;

use App\Entity\GeneratedArticles;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
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
    public function __construct(
        ManagerRegistry $registry,
        private readonly EntityManagerInterface $em)
    {
        parent::__construct($registry, GeneratedArticles::class);
    }

    public function getArticleThemes(): array
    {
        $qb = $this->em->createQueryBuilder();
        $themesArray = $qb
            ->select('ac.code', 'ac.theme')
            ->from('App:ArticleContent', 'ac')
            ->indexBy('ac', 'ac.code')
            ->getQuery()
            ->getResult()
        ;

        foreach ($themesArray as $key => $value) {
            $themes[$key] = $value['theme'];
        }

        return $themes;
    }

    public function getArticleTemplate(int $id): array
    {
        $qb = $this->em->createQueryBuilder();
        $query = $qb
            ->select('ga.template')
            ->from(GeneratedArticles::class, 'ga')
            ->andWhere('ga.id = :id')
            ->setParameter('id', $id)
            ->getQuery();
        return $query->execute();
    }
}
