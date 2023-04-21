<?php

namespace App\Repository;

use App\Entity\Module;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
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
    public function __construct(
        ManagerRegistry $registry,
        private readonly EntityManagerInterface $em
    )
    {
        parent::__construct($registry, Module::class);
    }

    public function deleteTemplate(int $moduleId): void
    {
        $template = $this->find($moduleId);
        $this->em->remove($template);
        $this->em->flush();
    }

    public function getUserTemplates(int $userId): array
    {
        $qb = $this->em->createQueryBuilder();
        return $qb
            ->select('m.id', 'm.code')
            ->from(Module::class, 'm')
            ->where('m.user = :id')
            ->setParameter('id', $userId)
            ->getQuery()
            ->getResult()
            ;
    }
}
