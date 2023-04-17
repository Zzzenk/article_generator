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

    public function save(GeneratedArticles $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(GeneratedArticles $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function addArticle($user, $title, $article, $template, $imageFileName, $keywords)
    {
        $template['images'] = null;
        foreach ($template as $key => $value) {
            $templateArray[] = $key . '_' . $value;
        }
        $template = implode(',', $templateArray);

        $sql = 'INSERT INTO generated_articles (user_id, created_at, title, article, template, keywords)
        VALUES (:user, :created_at, :title, :article, :template, :keywords)';
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->bindValue('user', $user->getId());
        $stmt->bindValue('created_at', (new \DateTime('now'))->format('Y-m-d H:i:s'));
        $stmt->bindValue('title', $title);
        $stmt->bindValue('article', $article);
        $stmt->bindValue('template', $template);
        $stmt->bindValue('keywords', serialize($keywords));
        $stmt->executeStatement();

        $lastId = $this->getEntityManager()->getConnection()->lastInsertId();

        if ($imageFileName != null) {
            $this->addArticleImage($lastId, $imageFileName);
        }
    }

    public function addArticleImage($lastId, $imageFileName)
    {
        if (is_array($imageFileName)) {
            foreach ($imageFileName as $image) {
                $this->addArticleImageSql($lastId, $image);
            }
        } else {
            $this->addArticleImageSql($lastId, $imageFileName);
        }
    }

    public function addArticleImageSql($lastId, $image)
    {
        $sql = 'INSERT INTO article_images (article_id, image_link)
                VALUES (:article_id, :image_link)';
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->bindValue('article_id', $lastId);
        $stmt->bindValue('image_link', $image);
        $stmt->executeStatement();
    }

    public function lastCreatedArticles($user)
    {
        $date = (new \DateTime('-1 month'))->format('Y-m-d H:i:s');
        $sql = 'SELECT * FROM generated_articles
        WHERE created_at > :date
        AND user_id = :user_id';
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->bindValue('user_id', $user->getId());
        $stmt->bindValue('date', $date);

        return $stmt->executeQuery()->fetchAllAssociative();
    }

    public function last2Hours($user)
    {
        $date = (new \DateTime('-2 hours'))->format('Y-m-d H:i:s');
        $sql = 'SELECT * FROM generated_articles
        WHERE created_at > :date
        AND user_id = :user_id';
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->bindValue('user_id', $user->getId());
        $stmt->bindValue('date', $date);

        if (count($stmt->executeQuery()->fetchAllAssociative()) >= 2) {
            return false;
        } else {
            return true;
        }
    }

    public function getArticleTemplate($id)
    {
        $sql = 'SELECT template FROM generated_articles
        WHERE id = :id';
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->bindValue('id', $id);

        return $stmt->executeQuery()->fetchAllAssociative();
    }

}
