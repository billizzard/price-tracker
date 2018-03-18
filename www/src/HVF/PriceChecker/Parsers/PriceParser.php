<?php
namespace App\HVF\PriceChecker\Parsers;

interface PriceParser
{
    public function getPrice(string $pageContent);
}