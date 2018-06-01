<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ErrorRepository")
 */
class Error extends Base
{
    const TYPE_CRON = 1;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=1000)
     */
    private $message;

    /**
     * @ORM\Column(type="smallint")
     */
    private $type = self::TYPE_CRON;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isDeleted = false;

    /**
     * @ORM\Column(type="string", length=1000)
     */
    private $addData;

    /**
     * @ORM\Column(type="integer")
     */
    private $createdAt;

    public function __construct()
    {
        $this->createdAt = time();
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
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

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    public function fill($message, $addData)
    {
        $this->setMessage($message);
        $this->setAddData($addData);
        $this->setCreatedAt(time());
    }

    public function delete()
    {
        $this->isDeleted = true;
    }


}
