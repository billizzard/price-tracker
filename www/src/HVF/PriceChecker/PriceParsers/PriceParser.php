<?php
namespace App\HVF\PriceChecker\PriceParsers;

interface PriceParser
{
    public function getPrice(string $pageContent);
}