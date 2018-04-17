<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Annotations\HVFGrid;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProductRepository")
 */
class Product
{
    const STATUS_TRACKED = 2; // отслеживается, есть парсер, есть dfnxths
    const STATUS_NOT_TRACKED = 1; // не отслеживается, т.е. у продукта нету ватчеров
    const STATUS_NEW = 4; // новый продукт, непонятно есть ли у него парсер
    const STATUS_ERROR_TRACKED = 3; // не смог найти продукта либо его цену

    public function __construct()
    {
        $this->watchers = new ArrayCollection();
    }

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=500, unique=true)
     * @Assert\Url(message="v.url.invalid")
     * @Assert\NotBlank()
     * @HVFGrid(sort=true)
     */
    private $url = '';

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Watcher", mappedBy="product")
     */
    private $watchers;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PriceTracker", mappedBy="product")
     */
    private $priceTrackers;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Host", inversedBy="products")
     * @ORM\JoinColumn(nullable=true)
     */
    private $host;

    /**
     * @ORM\Column(type="decimal", scale=2)
     * @Assert\NotBlank()
     */
    private $currentPrice = 0;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     */
    private $lastTrackedDate = 0;

    /**
     * @ORM\Column(type="smallint")
     */
    private $status = self::STATUS_NEW;

    private $changedPrice = false;

    public function getHost(): Host
    {
        return $this->host;
    }

    public function setHost(Host $host)
    {
        $this->host = $host;
    }

    public function setCurrentPrice(float $price)
    {
        $this->currentPrice = $price;
    }

    public function getCurrentPrice(): float
    {
        return $this->currentPrice;
    }

    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url): void
    {
        $this->url = $url;
    }

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
     * @return Collection|Watcher[]
     */
    public function getWatchers()
    {
        return $this->watchers;
    }

    /**
     * @return Collection|PriceTracker[]
     */
    public function getPriceTrackers()
    {
        return $this->priceTrackers;
    }

    /**
     * @return int
     */
    public function getLastTrackedDate(): int
    {
        return $this->lastTrackedDate;
    }

    /**
     * @param int $lastTrackedDate
     */
    public function setLastTrackedDate($lastTrackedDate): void
    {
        $this->lastTrackedDate = $lastTrackedDate;
    }

    public function setChangedPrice(bool $changedPrice)
    {
        $this->changedPrice = $changedPrice;
    }

    public function getChangedPrice(): bool
    {
        return $this->changedPrice;
    }


}
