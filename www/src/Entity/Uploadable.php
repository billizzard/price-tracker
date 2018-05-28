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

interface Uploadable
{
    const HOST_TYPE = 1;

    public function getEntityType();
}