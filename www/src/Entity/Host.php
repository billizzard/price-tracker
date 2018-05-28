<?php

namespace App\Entity;

use App\HVF\PriceChecker\PriceParsers\OnlinerBaraholkaParser;
use App\HVF\PriceChecker\PriceParsers\OnlinerCatalogParser;
use App\HVF\PriceChecker\PriceParsers\PriceParser;
use App\HVF\PriceChecker\SiteParsers\FileGetContentParser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @ORM\Entity(repositoryClass="App\Repository\HostRepository")
 */
class Host extends Base implements Uploadable
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank()
     */
    private $host;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Product", mappedBy="host")
     */
    private $products;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isDeleted = false;

    /**
     * @var string $image
     * @Assert\Image(
     *     minWidth = 200,
     *     maxWidth = 400,
     *     minHeight = 200,
     *     maxHeight = 400,
     *     allowLandscape = false,
     *     allowPortrait = false,
     *     maxSize = "2M"
     *     )
     */
    private $logoFile;

    public function getEntityType()
    {
        return self::HOST_TYPE;
    }

    /**
     * @return Collection|Product[]
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param mixed $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    public function setLogoFile($logoFile)
    {
        $this->logoFile = $logoFile;
    }

    public function getLogoFile()
    {
        return $this->logoFile;
    }

    public function delete()
    {
        $this->isDeleted = true;
    }

    public function getParser(): ?PriceParser
    {
        switch($this->getHost()) {
            case 'catalog.onliner.by': return new OnlinerCatalogParser(new FileGetContentParser());
            case 'baraholka.onliner.by': return new OnlinerBaraholkaParser(new FileGetContentParser());
        }

        return null;
    }


}
