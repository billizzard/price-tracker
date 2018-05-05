<?php

namespace App\Repository;

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


//    public function getTableData(HVFGridView $gridView, QueryBuilder $queryBuilder): array
//    {
//        $result = $gridView->getGridData($queryBuilder, $page, $onPage);
//        return $result;
//    }
//
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

    public function findTracked(): array
    {
        return $this->findBy(['status' => [Product::STATUS_TRACKED, Product::STATUS_NEW, Product::STATUS_ERROR_TRACKED]]);
    }


//
//    private function createPaginator(QueryBuilder $query, int $page): Pagerfanta
//    {
//        $paginator = new Pagerfanta(new DoctrineORMAdapter($query));
//        $paginator->setMaxPerPage(Product::DEFAULT_LIST_ITEMS);
//        $paginator->setCurrentPage($page);
//
//        return $paginator;
//    }

    /*
    public function findBySomething($value)
    {
        return $this->createQueryBuilder('t')
            ->where('t.something = :value')->setParameter('value', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */
}
