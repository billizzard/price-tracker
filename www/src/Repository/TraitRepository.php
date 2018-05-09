<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\ORM\QueryBuilder;

trait TraitRepository
{
    public function andWhereUserOwner(QueryBuilder &$qb, User $user, string $alias)
    {
        if (!$user->isAdmin()) {
            $qb->andWhere($alias . ".user = " . $user->getId());
        }
    }
}
