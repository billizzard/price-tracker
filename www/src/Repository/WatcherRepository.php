<?php

namespace App\Repository;

use App\Entity\Message;
use App\Entity\User;
use App\Entity\Watcher;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\Request;

class WatcherRepository extends ServiceEntityRepository
{
    use TraitRepository;

    /** @var  QueryBuilder */

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Watcher::class);
    }

    public function getAlias()
    {
        return 'w';
    }

    public function findByRequestQueryBuilder(Request $request, User $user)
    {
        $sortColumn = $request->get('sort', 'id');
        $sortDirection = 'ASC';

        if ($sortColumn[0] === '-') {
            $sortDirection = 'DESC';
            $sortColumn = mb_substr($sortColumn, 1);
        }

        $qb = $this->createQueryBuilder('w');
        $qb->addSelect('w.id as id')
            ->addSelect('w.status as status')
            ->addSelect('w.title as title')
            ->addSelect('IDENTITY(w.user) as user')
            ->leftJoin('w.product', 'p', 'WITH', 'w.product = p.id');
        


        $this->getFiltered($qb, $request)->getNotDeleted($qb)->andWhereUserOwner($qb, $user);
        $qb->addOrderBy($sortColumn, $sortDirection);
        
        //$query = $qb->getQuery();

        return $qb;
    }

    private function getFiltered(QueryBuilder &$qb, $request)
    {
        if ($request->get('title')) {
            $qb->andWhere("w.title LIKE :title")->setParameter(':title', $request->get('title') . '%');

        }

        if ($status = $request->get('status')) {
            if ($status == Watcher::STATUS_PRICE_CONFIRMED) {
                $qb->andWhere('w.status IN (' . Watcher::STATUS_PRICE_CONFIRMED . ',' . Watcher::STATUS_NEW . ')');
            } else {
                $qb->andWhere('w.status = ' . (int)$status);
            }
        }

        if ($user = $request->get('user')) {
            $user = explode(',', $user);

            $user = array_map(function($val) {
                return (int)$val;
            }, $user);

            $user = array_unique($user);

            $qb->andWhere('w.user IN (' . implode(',', $user) . ')');
        }
        return $this;
    }

    /*
     * Tracked - значит вынимаются все, которые не должны кроном отслеживаться, т.е. удаленные или успешно законченные
     */

    public function findTracked()
    {
        $qb = $this->createQueryBuilder('w');
        $this->getTracked($qb);
        return $qb->getQuery()->getResult();
    }

    public function findTrackedByProductId($productId)
    {
        $qb = $this->createQueryBuilder('w')->andWhere('w.product = ' . (int)$productId);
        $this->getTracked($qb);
        return $qb->getQuery()->getResult();
    }

    /*
     * Visible - те, которые пользователь может просматривать. Т.е. не удаленные.
     */

    public function getOneVisibleByIdAndUser(int $id, User $user)
    {
        $qb = $this->createQueryBuilder('w')->andWhere('w.id = ' . (int)$id);
        $this->getNotDeleted($qb)->andWhereUserOwner($qb, $user);
        return $qb->getQuery()->getOneOrNullResult();
    }

    private function getTracked(QueryBuilder &$qb)
    {
        $this->getNotDeleted($qb);
        $qb->andWhere('w.status != ' . Watcher::STATUS_SUCCESS);
        return $this;
    }

}
