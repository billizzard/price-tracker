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
        foreach ($tags as $tag) {
            if ($tag->hasAttribute('class')) {
                if ($tag->getAttribute('class') == 'offers-description__link offers-description__link_subsidiary offers-description__link_nodecor') {
                    $prices = explode('–', trim($tag->textContent));
                    $price = (float)str_replace(',', '.', $prices[0]);
                    if ($price) {
                        return $price;
                    }
                }
            }
        }
        // logging: не смог распарсить цену

        return 0;
    }
}