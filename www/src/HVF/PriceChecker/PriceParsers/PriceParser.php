<?php
namespace App\HVF\PriceChecker\PriceParsers;

interface PriceParser
{
    public function getPriceByUrl(string $pageContent);
}