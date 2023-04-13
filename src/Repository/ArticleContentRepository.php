<?php

namespace App\Repository;

use App\Entity\ArticleContent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ArticleContent>
 *
 * @method ArticleContent|null find($id, $lockMode = null, $lockVersion = null)
 * @method ArticleContent|null findOneBy(array $criteria, array $orderBy = null)
 * @method ArticleContent[]    findAll()
 * @method ArticleContent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleContentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArticleContent::class);
    }

    public function save(ArticleContent $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ArticleContent $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAllArticles()
    {
        $sql = 'SELECT body FROM article_content';
        return $this->getEntityManager()->getConnection()->prepare($sql)->executeQuery()->fetchAllAssociative();
    }

    public function themes()
    {
        $sql = 'SELECT theme FROM article_content';
        $array = $this->getEntityManager()->getConnection()->prepare($sql)->executeQuery()->fetchAllAssociativeIndexed();

        $themes = array();
        foreach ($array as $key => $row) {
            $themes[] = $key;
        }
        $themes = array_combine($themes, $themes);

        return array_unique($themes);
    }

}
