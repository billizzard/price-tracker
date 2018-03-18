<?php
namespace App\HVF\PriceChecker\Parsers;

class OnlinerCatalogParser implements PriceParser
{

    public function getPrice(string $pageContent)
    {
        return '30.2';
    }
}