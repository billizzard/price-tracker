<?php

namespace App\Repository;

use App\Entity\Message;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\Request;

class MessageRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Message::class);
    }

    public function getUnreadMessagesByUser(User $user, $count)
    {
        return $this->createQueryBuilder('m')
            ->where('m.user = :user AND m.status = :status')
            ->setParameter('user', $user->getId())
            ->setParameter('status', Message::STATUS_NOT_READ)
            ->setMaxResults($count)
            ->getQuery()
            ->getResult();
    }

    public function findByRequestQueryBuilder(Request $request, User $user)
    {
        $sortColumn = $request->get('sort', 'id');
        $sortDirection = 'DESC';

        $queryBuilder = $this->createQueryBuilder('m')
        ->where('m.status != ' . Message::STATUS_DELETED . ' AND m.user = ' . $user->getId());

        $queryBuilder->addSelect('m.id as id');
        $queryBuilder->addSelect('m.message as message');
        $queryBuilder->addSelect('m.type as type');
        $queryBuilder->addSelect('m.addData as addData');
        $queryBuilder->addSelect('m.createdAt as createdAt');

        $queryBuilder->addOrderBy($sortColumn, $sortDirection);

        return $queryBuilder;
    }

    public function deleteById($ids, User $user)
    {
        $qb = $this->createQueryBuilder("m");
        $updated = $qb->update()
            ->set('m.status', Message::STATUS_DELETED)
            ->where($qb->expr()->in('m  .id', ':ids'))->setParameter("ids", explode(',', $ids))
            ->andWhere('m.user = :userId')->setParameter("userId", $user->getId())
            ->getQuery()->execute();

        return $updated;
    }

}
