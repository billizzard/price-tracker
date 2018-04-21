<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MessageRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Message
{
    const STATUS_NOT_READ = 1;
    const STATUS_DELETED = 2;
    const STATUS_READ = 3;

    const TYPE_SUCCESS = 10;
    const TYPE_SALE_SUCCESS = 11;
    const TYPE_INFO = 20;
    const TYPE_CHANGE_PRICE = 21;
    const TYPE_WARNING = 30;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=500)
     */
    private $message;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $title = '';

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $fromUser = '';

    /**
     * @ORM\Column(columnDefinition="TINYINT DEFAULT 1 NOT NULL")
     */
    private $status = self::STATUS_NOT_READ;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="messages")
     * @ORM\JoinColumn(nullable=true)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=500)
     */
    private $addData;

    /**
     * @ORM\Column(type="smallint")
     */
    private $type = self::TYPE_INFO;

    /**
     * @ORM\Column(type="integer")
     */
    private $createdAt;

    /**
     * @return mixed
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title): void
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param mixed $fromUser
     */
    public function setFromUser($fromUser): void
    {
        $this->fromUser = $fromUser;
    }

    /**
     * @return mixed
     */
    public function getFromUser(): string
    {
        return $this->fromUser ? $this->fromUser : 'price-tracker.by';
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message): void
    {
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getAddData()
    {
        return (array)json_decode($this->addData);
    }

    /**
     * @param mixed $addData
     */
    public function setAddData($addData): void
    {
        $this->addData = json_encode($addData);
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus($status): void
    {
        $this->status = $status;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType($type): void
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Triggered on insert
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->createdAt = time();
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getTranslatedMessage(TranslatorInterface $translator)
    {
        if ($this->getType() == self::TYPE_CHANGE_PRICE) {
            $addData = $this->getAddData();
            return $translator->trans($this->getMessage(), ['%watcher%' => '"<a target="_blank" href="/ru/profile/trackers/' . $addData['watcher_id'] . '/view/">' . $this->getAddData()['watcher_title'] . '</a>"']);
        }
        return '';
    }

    public function getTranslatedTitle(TranslatorInterface $translator)
    {
        if ($this->getType() == self::TYPE_CHANGE_PRICE) {
            return $translator->trans($this->getTitle());
        }
    }
}
