<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Watcher;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\Request;

class WatcherRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Watcher::class);
    }

    public function findByRequestQueryBuilder(Request $request, User $user)
    {
        $sortColumn = $request->get('sort', 'id');
        $sortDirection = 'ASC';

        if ($sortColumn[0] === '-') {
            $sortDirection = 'DESC';
            $sortColumn = mb_substr($sortColumn, 1);
        }

        $queryBuilder = $this->createQueryBuilder('w');
        $queryBuilder->addSelect('w.id as id');
        $queryBuilder->addSelect('p.status as status');
        $queryBuilder->addSelect('w.title as title');
        $queryBuilder->leftJoin('w.product', 'p', 'WITH', 'w.product = p.id');
        $queryBuilder->where("w.user = " . $user->getId());
        $queryBuilder->andWhere($this->getActiveQuery());


        $queryBuilder->addOrderBy($sortColumn, $sortDirection);

        return $queryBuilder;
    }

    public function findActive()
    {
        $qb = $this->createQueryBuilder('w')->where($this->getActiveQuery());
        return $qb->getQuery()->getResult();
    }

    public function findActiveByProductId($productId)
    {
        $qb = $this->createQueryBuilder('w')->where($this->getActiveQuery())
            ->andWhere('w.product = ' . (int)$productId);
        return $qb->getQuery()->getResult();
    }

    public function getOneByIdAndUser(int $id, User $user)
    {
        $qb = $this->createQueryBuilder('w')->where($this->getActiveQuery())
            ->andWhere('w.id = ' . (int)$id)
            ->andWhere('w.user = ' . $user->getId());
        return $qb->getQuery()->getOneOrNullResult();
    }

    private function getActiveQuery(): string
    {
        return 'w.status != ' . Watcher::STATUS_SUCCESS . ' AND w.status != ' . Watcher::STATUS_DELETED;
    }

}
