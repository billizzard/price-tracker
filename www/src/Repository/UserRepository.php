<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * This custom Doctrine repository is empty because so far we don't need any custom
 * method to query for application user information. But it's always a good practice
 * to define a custom repository that will be used when the application grows.
 *
 * See https://symfony.com/doc/current/doctrine/repository.html
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findOtherByEmailOrNick(string $email, string $nickName, int $id): ?User
    {
        return $this->createQueryBuilder('u')->select('u')->where('(u.email = :email OR u.nickName = :nickName) AND u.id != :id')
            ->setParameter('email', $email)
            ->setParameter('nickName', $nickName)
            ->setParameter('id', $id)
            ->getQuery()->getOneOrNullResult();
    }

    public function getAllAvatars(): array
    {
        $avatars = scandir(DOCUMENT_ROOT . '/build/images/avatars/');
        if ($avatars) {
            array_shift($avatars);
            array_shift($avatars);
        }
        return $avatars;
    }
}
