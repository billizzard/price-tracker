<?php

namespace App\Repository;

use App\Entity\Host;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\Request;

class HostRepository extends ServiceEntityRepository
{
    use TraitRepository;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Host::class);
    }

    public function getAlias()
    {
        return 'h';
    }

    public function getAll()
    {
        $qb = $this->createQueryBuilder($this->getAlias());
        $this->getNotDeleted($qb);
        return $qb->getQuery()->getResult();
    }

    public function findByRequestQueryBuilder(Request $request, User $user)
    {
        $qb = $this->createQueryBuilder($this->getAlias());
        $qb->addSelect('h.id as id')
            ->addSelect('h.host as host');

        $this->getFiltered($qb, $request)->getNotDeleted($qb);
        $this->getOrder($qb, $request);

        return $qb;
    }

    private function getFiltered(QueryBuilder &$qb, $request)
    {
        if ($request->get('host')) {
            $qb->andWhere("h.host LIKE :host")->setParameter(':host', $request->get('host') . '%');
        }

        return $this;
    }

    private function getOrder(QueryBuilder &$qb, $request)
    {
        $sortColumn = $request->get('sort', 'id');
        $sortDirection = 'ASC';

        if ($sortColumn[0] === '-') {
            $sortDirection = 'DESC';
            $sortColumn = mb_substr($sortColumn, 1);
        }

        $qb->addOrderBy($sortColumn, $sortDirection);
    }

    public function getOneById(int $id)
    {
        $qb = $this->createQueryBuilder($this->getAlias());
        $qb->andWhere("h.id = :id")->setParameter(':id', $id);
        $this->getNotDeleted($qb);
        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findOneBy(array $criteria, array $orderBy = null)
    {

        return parent::findOneBy($criteria, $orderBy); // TODO: Change the autogenerated stub
    }
}
