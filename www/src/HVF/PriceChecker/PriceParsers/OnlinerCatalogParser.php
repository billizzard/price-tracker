<?php
namespace App\HVF\PriceChecker\PriceParsers;

class OnlinerCatalogParser implements PriceParser
{
    public function getPrice(string $pageContent)
    {
        $doc = new \DOMDocument();

        libxml_use_internal_errors(true);
        $doc->loadHtml($pageContent);
        libxml_use_internal_errors(false);
        $tags = $doc->getElementById('product-prices-container')->getElementsByTagName('a');
        echo "<pre>";
        var_dump(count($tags));
        echo "</pre>";
        die();
        die('fdf');
        

        return '30.2';
    }
}