<?php

namespace App\Repository;

use App\Entity\Base;
use App\Entity\File;
use App\Entity\Uploadable;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method File|null find($id, $lockMode = null, $lockVersion = null)
 * @method File|null findOneBy(array $criteria, array $orderBy = null)
 * @method File[]    findAll()
 * @method File[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FileRepository extends ServiceEntityRepository
{
    use TraitRepository;

    public function getAlias()
    {
        return 'f';
    }

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, File::class);
    }

    public function getFilesByEntity(Uploadable $entity, User $user)
    {
        $qb = $this->createQueryBuilder($this->getAlias());
        $this->getFileByEntityAndUser($qb, $entity, $user)->andWhereUserOwner($qb, $user)->getNotDeleted($qb);

        return $qb->getQuery()->getResult();
    }

    public function getFileByEntity(Uploadable $entity, User $user)
    {
        $qb = $this->createQueryBuilder($this->getAlias());
        $this->getFileByEntityAndUser($qb, $entity, $user)->andWhereUserOwner($qb, $user)->getNotDeleted($qb);
        $qb->addOrderBy('f.id', 'DESC');
        $qb->setMaxResults(1);
        return $qb->getQuery()->getOneOrNullResult();
    }

    private function getFileByEntityAndUser(QueryBuilder &$qb, Uploadable $entity, User $user)
    {
        $qb->andWhere('f.type = :type AND f.entityId = :entity')
            ->setParameters([':type' => $entity->getEntityType(), ':entity' => $entity->getId()]);

        return $this;
    }
}
