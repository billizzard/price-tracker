<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\WatcherRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Watcher extends Base
{
    const STATUS_NEW = 1; // Новый ватчер
    const STATUS_PRICE_CONFIRMED = 3; // После нового статуса присваивается этот, значит вотчер отслеживается
    const STATUS_SUCCESS = 2; // Успешно отслежен, скидка получена, больше не отслеживается
    const STATUS_DELETED = 4; // Удален, больше не отслеживается

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private $title = '';

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="watchers")
     * @ORM\JoinColumn(nullable=true)
     */
    private $user;

    /**
     * @ORM\Column(type="decimal", scale=2)
     * @Assert\NotBlank()
     */
    private $startPrice = 0;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     * @Assert\Range(
     *      min = 1,
     *      max = 99,
     *      minMessage = "v.percent.range",
     *      maxMessage = "v.percent.range"
     * )
     */
    private $percent = 0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isDeleted = false;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     */
    private $createdAt = 0;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     */
    private $successDate = 0;

    /**
     * @ORM\Column(type="smallint")
     */
    private $status = self::STATUS_NEW;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Product", inversedBy="watchers")
     * @ORM\JoinColumn(nullable=true)
     */
    private $product;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title): void
    {
        $this->title = $title;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return float
     */
    public function getStartPrice(): float
    {
        return $this->startPrice;
    }

    public function getEndPrice(): float
    {
        return round($this->startPrice - ($this->startPrice * $this->percent / 100), 2);
    }

    /**
     * @param float $startPrice
     */
    public function setStartPrice($startPrice): void
    {
        $this->startPrice = $startPrice;
    }

    /**
     * @return int
     */
    public function getPercent(): int
    {
        return $this->percent;
    }

    /**
     * @param integer $percent
     */
    public function setPercent($percent): void
    {
        $this->percent = $percent;
    }

    /**
     * @return integer
     */
    public function getCreatedAt(): int
    {
        return $this->createdAt;
    }

    /**
     * @param integer $createdAt
     */
    public function setCreatedAt($createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return integer
     */
    public function getSuccessDate(): int
    {
        return $this->successDate;
    }

    /**
     * @param integer $successDate
     */
    public function setSuccessDate($successDate): void
    {
        $this->successDate = $successDate;
    }

    /**
     * Triggered on insert
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->createdAt = time();
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function setProduct(Product $product)
    {
        $this->product = $product;
    }

    public function delete(): void
    {
        $this->isDeleted = true;
    }
}
