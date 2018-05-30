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
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Doctrine\ORM\QueryBuilder;

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
class UserRepository extends ServiceEntityRepository implements UserLoaderInterface
{
    use TraitRepository;

    public function getAlias(): string
    {
        return 'u';
    }

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findByEmail($email): ?User
    {
        $qb = $this->createQueryBuilder($this->getAlias());
        $qb->andWhere('u.email = :email AND u.isConfirmed = :isConfirmed')
        ->setParameters([':email' => $email, ':isConfirmed' => true]);
        $this->getNotDeleted($qb);
        return $qb->getQuery()->getOneOrNullResult();
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

    public function findByConfirmCode(string $code): ?User
    {
        $qb = $this->createQueryBuilder($this->getAlias());
        $qb->andWhere('u.confirmCode = :code AND u.isConfirmed = :isConfirmed')
            ->setParameters([':code' => $code, ':isConfirmed' => false]);
        $this->getNotDeleted($qb);
        return $qb->setMaxResults(1)->getQuery()->getOneOrNullResult();
    }

    /**
     * Loads the user for the given username.
     *
     * This method must throw UsernameNotFoundException if the user is not
     * found.
     *
     * @param string $username The username
     *
     * @return UserInterface
     *
     * @throws UsernameNotFoundException if the user is not found
     */
    public function loadUserByUsername($username)
    {
        $user = $this->findByEmail($username);

        if (!$user) {
            throw new UsernameNotFoundException('No user found for username ' . $username);
        }

        return $user;
    }

    /**
     * Refreshes the user.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     *
     * @return UserInterface
     *
     * @throws UnsupportedUserException if the user is not supported
     */
    public function refreshUser(UserInterface $user)
    {
        $class = get_class($user);
        if (!$this->supportsClass($class)) {
            throw new UnsupportedUserException(sprintf(
                'Instances of "%s" are not supported.',
                $class
            ));
        }

        if (!$refreshedUser = $this->find($user->getId())) {
            throw new UsernameNotFoundException(sprintf('User with id %s not found', json_encode($user->getId())));
        }

        return $refreshedUser;
    }

    /**
     * Whether this provider supports the given user class.
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        return $this->getEntityName() === $class || is_subclass_of($class, $this->getEntityName());
    }

    protected function getConfirmed(QueryBuilder &$qb)
    {
        $qb->andWhere($this->getAlias() . '.isConfirmed = false');
        return $this;
    }
}
