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
        $sortDirection = 'ASC';

        if ($sortColumn[0] === '-') {
            $sortDirection = 'DESC';
            $sortColumn = mb_substr($sortColumn, 1);
        }

        $queryBuilder = $this->createQueryBuilder('m');
        $queryBuilder->addSelect('m.id as id');
        $queryBuilder->addSelect('m.message as message');
        $queryBuilder->addSelect('m.status as status');
        $queryBuilder->addSelect('m.createdAt as createdAt');

        $queryBuilder->addOrderBy($sortColumn, $sortDirection);

        return $queryBuilder;
    }

}
