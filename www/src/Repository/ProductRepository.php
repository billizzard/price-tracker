<?php

namespace App\Repository;

use App\Entity\Host;
use App\Entity\Product;
use App\Entity\User;
use App\Service\HVFGridView;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\Request;

class ProductRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function findByRequestQueryBuilder(Request $request, User $user)
    {
        $sortColumn = $request->get('sort', 'id');
        $sortDirection = $sortColumn[0] === '-' ? 'DESC' : 'ASC';

        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder->leftJoin('p.watchers', 'w', 'WITH', 'w.product = p.id');
        $queryBuilder->where("w.user = " . $user->getId());

        $queryBuilder->addOrderBy('p.' . $sortColumn, $sortDirection);

        return $queryBuilder;

        return (array) $this->createPaginator($queryBuilder,$page)->getCurrentPageResults();
    }

    public function findTrackedByHost(Host $host): array
    {
        return $this->findBy(['host' => $host, 'status' => [Product::STATUS_TRACKED, Product::STATUS_NEW, Product::STATUS_ERROR_TRACKED]]);
    }

}
