<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\ORM\QueryBuilder;

trait TraitRepository
{
    public function andWhereUserOwner(QueryBuilder $qb, User $user)
    {
        if (!$user->isAdmin()) {
            $qb->andWhere($this->getAlias() . ".user = " . $user->getId());
        }
        return $this;
    }

    protected function getNotDeleted(QueryBuilder &$qb)
    {
        $qb->andWhere($this->getAlias() . '.isDeleted = false');
        return $this;
    }

    /**
     * Есть ли не удаленные записи
     * @return bool
     */
    public function isHasEntity()
    {
        $qb = $this->createQueryBuilder($this->getAlias());
        $this->getNotDeleted($qb);
        return (bool)$qb->setMaxResults(1)->getQuery()->getOneOrNullResult();
    }

    abstract function getAlias(): string;
}
