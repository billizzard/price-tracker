<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields="email", message="e.email_taken")
 * @ORM\HasLifecycleCallbacks()
 */
class User implements UserInterface
{
    const USER_STATUS_DEFAULT = 1;

    const USER_ROLE_DEFAULT = 'ROLE_USER';
    const USER_ROLE_ADMIN = 'ROLE_ADMIN';

    public function __construct()
    {
        $this->watchers = new ArrayCollection();
        if (empty($this->nickName)) {
            $this->nickName = 'User ' . uniqid();
        }
    }

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=60, unique=true)
     * @Assert\Email()
     * @Assert\NotBlank()
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=30, unique=true)
     */
    private $nickName = '';

    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=50)
     */
    private $plainPassword;

    /**
     * @ORM\Column(columnDefinition="TINYINT DEFAULT 1 NOT NULL")
     */
    private $status = self::USER_STATUS_DEFAULT;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Watcher", mappedBy="user")
     */
    private $watchers;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $avatar = '';

    /**
     * @var array
     *
     * @ORM\Column(type="json")
     */
    private $roles = ['ROLE_USER'];

    /**
     * The below length depends on the "algorithm" you use for encoding
     * the password, but this works well with bcrypt.
     *
     * @ORM\Column(type="string", length=64)
     */
    private $password;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Message", mappedBy="user")
     */
    private $messages;

    public function getId(): int
    {
        return $this->id;
    }

    public function getNickName(): string
    {
        return $this->nickName;
    }

    public function setNickName(string $nickName)
    {
        $this->nickName = $nickName;
    }

    public function getAvatar(): string
    {
        if (!$this->avatar) {
            $this->avatar = '1.jpg';
        }
        return $this->avatar;
    }

    public function setAvatar(string $avatar): void
    {
        $this->avatar = $avatar;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getUsername()
    {
        return $this->email;
    }

    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    public function setPlainPassword($password)
    {
        $this->plainPassword = $password;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Returns the roles or permissions granted to the user for security.
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        if (empty($roles)) {
            $roles[] = self::USER_ROLE_DEFAULT;
        }

        return array_unique($roles);
    }

    public function setRoles($roles): void
    {
        $this->roles[] = $roles;
    }

    /**
     * @return Collection|Watcher[]
     */
    public function getWatchers()
    {
        return $this->watchers;
    }

    /**
     * @return Collection|Message[]
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * {@inheritdoc}
     */
    public function getSalt(): ?string
    {
        // See "Do you need to use a Salt?" at https://symfony.com/doc/current/cookbook/security/entity_provider.html
        // we're using bcrypt in security.yml to encode the password, so
        // the salt value is built-in and you don't have to generate one

        return null;
    }

    /**
     * Removes sensitive data from the user.
     *
     * {@inheritdoc}
     */
    public function eraseCredentials(): void
    {
        // if you had a plainPassword property, you'd nullify it here
        // $this->plainPassword = null;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): string
    {
        // add $this->salt too if you don't use Bcrypt or Argon2i
        return serialize([$this->id, $this->nickName, $this->password]);
    }

    public function changeByData(array $data, UserPasswordEncoderInterface $encoder)
    {
        if ($data['newPassword'] || $data['oldPassword'] || $data['repeatPassword']) {
            if ($data['newPassword'] && $data['oldPassword'] && $data['repeatPassword']) {
                if ($data['newPassword'] == $data['repeatPassword']) {
                    if ($encoder->isPasswordValid($this, $data['oldPassword'])) {
                        $this->setPassword($encoder->encodePassword($this, $data['newPassword']));
                    } else {
                        throw new \Exception('e.change_password');
                    }
                } else {
                    throw new \Exception('e.change_password');
                }
            } else {
                throw new \Exception('e.change_password');
            }
        }

        $this->setEmail($data['email']);
        $this->setNickName($data['nickName']);
    }

    public function getAvatarFull()
    {
        return '/build/images/avatars/' . $this->getAvatar();
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized): void
    {
        // add $this->salt too if you don't use Bcrypt or Argon2i
        [$this->id, $this->nickName, $this->password] = unserialize($serialized, ['allowed_classes' => false]);
    }

    public function isAdmin()
    {
        if (in_array(self::USER_ROLE_ADMIN, $this->getRoles())) {
            return true;
        }
        return false;
    }

    /**
     * Triggered on insert
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->createdAt = new \DateTime("now");
    }

    /**
     * Triggered on update
     * @ORM\PreUpdate
     */
    public function onPreUpdate()
    {
        //$this->updatedAt = new \DateTime("now");
    }

    /**
     * Triggered on update
     * @ORM\PreRemove
     */
    public function onPreRemove()
    {
        //$this->deletedAt = new \DateTime("now");
    }
}