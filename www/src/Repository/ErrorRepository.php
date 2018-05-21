<?php

namespace App\Repository;

use App\Entity\Error;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method Error|null find($id, $lockMode = null, $lockVersion = null)
 * @method Error|null findOneBy(array $criteria, array $orderBy = null)
 * @method Error[]    findAll()
 * @method Error[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ErrorRepository extends ServiceEntityRepository
{
    use TraitRepository;

    public function getAlias(): string
    {
        return 'e';
    }

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Error::class);
    }

    public function findByRequestQueryBuilder(Request $request, User $user)
    {
        $sortColumn = $request->get('sort', 'id');
        $sortDirection = 'DESC';

        $queryBuilder = $this->createQueryBuilder('e')->where('e.isDeleted = false');
        $queryBuilder->addSelect('e.id as id');
        $queryBuilder->addSelect('e.message as message');
        $queryBuilder->addSelect('e.type as type');
        $queryBuilder->addSelect('e.addData as addData');
        $queryBuilder->addSelect('e.createdAt as createdAt');
        $queryBuilder->addOrderBy($sortColumn, $sortDirection);

        return $queryBuilder;
    }

    public function deleteById($ids)
    {
        $qb = $this->createQueryBuilder("e");
        $qb->update()->set('e.isDeleted', true)->where($qb->expr()->in('e.id', ':ids'))->setParameter("ids", $ids);
        $updated = $qb->getQuery()->execute();

        return $updated;
    }
}
