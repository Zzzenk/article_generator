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

    public function themes()
    {
        $sql = 'SELECT code, theme FROM article_content';
        $array = $this->getEntityManager()->getConnection()->prepare($sql)->executeQuery()->fetchAllAssociative();

        foreach ($array as $arr) {
                $implodedArray[] = (implode(',', $arr));
        }

        $keysList = explode(',', implode(',', array_unique($implodedArray)));

        foreach ($keysList as $key => $item) {
            if (!is_float($key/2)) {
                $themes[$item] = null;
                $prevItem = $item;
            } else {
                $themes[$prevItem] = $item;
            }
        }

        return $themes;
    }

}
