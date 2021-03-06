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
class User extends Base implements UserInterface
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
     * @ORM\Column(type="smallint")
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

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $confirmCode = '';

    /**
     * @ORM\Column(type="boolean")
     */
    private $isConfirmed = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isDeleted = false;

    /**
     * @ORM\Column(type="integer", options={"unsigned":true})
     */
    private $lastLogin = 0;

    /**
     * @ORM\Column(type="integer", options={"unsigned":true})
     */
    private $lastConfirmCode = 0;

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

    public function setPassword($password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getConfirmCode(): string
    {
        return $this->confirmCode;
    }

    public function setConfirmCode(string $confirmCode): self
    {
        $this->confirmCode = $confirmCode;
        return $this;
    }

    public function getIsConfirmed(): bool
    {
        return $this->isConfirmed;
    }

    public function setIsConfirmed(bool $isConfirmed): self
    {
        $this->isConfirmed = $isConfirmed;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLastLogin(): int
    {
        return $this->lastLogin;
    }

    /**
     * @param mixed $lastLogin
     */
    public function setLastLogin($lastLogin): void
    {
        $this->lastLogin = $lastLogin;
    }

    /**
     * @return mixed
     */
    public function getLastConfirmCode(): int
    {
        return $this->lastConfirmCode;
    }

    /**
     * @param mixed $lastConfirmCode
     */
    public function setLastConfirmCode($lastConfirmCode): self
    {
        $this->lastConfirmCode = $lastConfirmCode;
        return $this;
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

    public function getCreatedAt()
    {
        return $this->createdAt;
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

    public function generateConfirmCode(): string
    {
        return md5(uniqid(rand(), true));
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

    public function changeByData(array $data)
    {
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

    public function isAdmin(): bool
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

    public function delete(): void
    {
        $this->isDeleted = true;
    }

    public function canChangeConfirmCode(): bool
    {
        if ((time() - $this->getLastConfirmCode()) > (60 * 5)) {
            return true;
        }

        return false;
    }
}