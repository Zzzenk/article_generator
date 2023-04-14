<?php

namespace App\Repository;

use App\Entity\Module;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Module>
 *
 * @method Module|null find($id, $lockMode = null, $lockVersion = null)
 * @method Module|null findOneBy(array $criteria, array $orderBy = null)
 * @method Module[]    findAll()
 * @method Module[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ModuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Module::class);
    }

    public function save(Module $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Module $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function addTemplate($user_id, $title, $body)
    {
        $sql = 'INSERT INTO module (user_id, title, code)
        VALUES (:user_id, :title, :code)';
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->bindValue('user_id', $user_id);
        $stmt->bindValue('title', $title);
        $stmt->bindValue('code', $body);
        $stmt->executeStatement();
    }

    public function deleteTemplate($module_id, $user_id)
    {
        $sql = 'DELETE FROM module
        WHERE id = :module_id AND user_id = :user_id';
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->bindValue('module_id', $module_id);
        $stmt->bindValue('user_id', $user_id);
        $stmt->executeStatement();
    }

    public function getUserTemplates($user_id)
    {
        $sql = 'SELECT * FROM module
        WHERE user_id = :user_id';
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->bindValue('user_id', $user_id);
        return $stmt->executeQuery()->fetchAllAssociative();
    }

    public function defaultTemplates($imageFileName) : array
    {
        if ($imageFileName) {
            return [
                    '<div class="media">
    <img class="mr-3" src="{{ image }}" width="250" height="250" alt="">
    <div class="media-body">
        {{ paragraphs }}
    </div>
</div>',
                    '<div class="media">
    <div class="media-body">
        {{ paragraphs }}
    </div>
    <img class="ml-3" src="{{ image }}" width="250" height="250" alt="">
</div>',
                    '<div class="media">
    <div class="media-body">
        <p>{{ paragraph }}</p>
    </div>
    <img class="ml-3" src="{{ image }}" width="250" height="250" alt="">
</div>',
                    '<div class="media">
    <img class="mr-3" src="{{ image }}" width="250" height="250" alt="">
    <div class="media-body">
        <p>{{ paragraph }}</p>
    </div>
</div>',
                ];
        } else {
            return [
                    '<h3>{{ title }}</h3>
<p>{{ paragraph }}</p>',
                    '{{ paragraphs }}',
                    '<h1>{{ title }}</h1>
<p>{{ paragraph }}</p>',
                    '<div class="row">
    <div class="col-sm-6">
        {{ paragraphs }}      
    </div>
    <div class="col-sm-6">
        {{ paragraphs }}
    </div>
</div>',
                ];
        }
    }
}
