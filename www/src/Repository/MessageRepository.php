<?php

namespace App\Repository;

use App\Entity\Message;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\Request;

class MessageRepository extends ServiceEntityRepository
{
    private $qb;

    use TraitRepository;

    public function getAlias(): string
    {
        return 'm';
    }

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Message::class);
    }

    public function clearQb()
    {
        $this->qb = null;
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

        $queryBuilder = $this->createQueryBuilder('m')->where('m.status != ' . Message::STATUS_DELETED);
        $this->andWhereUserOwner($queryBuilder, $user);
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
        $qb->update()->set('m.status', Message::STATUS_DELETED)->where($qb->expr()->in('m  .id', ':ids'))->setParameter("ids", $ids);
        $this->andWhereUserOwner($qb, $user);
        $updated = $qb->getQuery()->execute();

        return $updated;
    }

    public function findPrev(Message $message)
    {
        $qb = $this->createQueryBuilder('m');
        $qb->where('m.id < :id AND m.status != :status')->setParameters(['id' => $message->getId(), 'status' => Message::STATUS_DELETED]);
        $this->andWhereUserOwner($qb, $message->getUser());
        $qb->orderBy('m.id', 'DESC')->setMaxResults(1)->getQuery()->getOneOrNullResult();
    }

    public function findNext(Message $message)
    {
        $qb = $this->createQueryBuilder('m');
        $qb->where('m.id > :id AND m.status != :status')->setParameters(['id' => $message->getId(), 'status' => Message::STATUS_DELETED]);
        $this->andWhereUserOwner($qb, $message->getUser());
        $qb->orderBy('m.id', 'ASC')->setMaxResults(1)->getQuery()->getOneOrNullResult();
    }

    public function findById($id, User $user)
    {
        $qb = $this->createQueryBuilder('m');
        $qb->where('m.id = :id AND m.status != :status')->setParameters(['id' => $id, 'status' => Message::STATUS_DELETED]);
        $this->andWhereUserOwner($qb, $user);
        return $qb->getQuery()->getOneOrNullResult();
    }

}
