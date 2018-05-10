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

    abstract function getAlias(): string;
}
