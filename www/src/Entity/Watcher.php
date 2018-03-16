<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\WatcherRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Watcher
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="watchers")
     * @ORM\JoinColumn(nullable=true)
     */
    private $user;

    /**
     * @ORM\Column(type="decimal", scale=2)
     * @Assert\NotBlank()
     */
    private $startPrice;

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
    private $percent;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     */
    private $createdAt;

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

    /**
     * @return int
     */
    public function getUser()
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
}
