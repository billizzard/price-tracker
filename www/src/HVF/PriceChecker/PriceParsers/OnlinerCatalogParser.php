<?php
namespace App\HVF\PriceChecker\PriceParsers;

use App\HVF\PriceChecker\SiteParsers\SiteParser;
use Symfony\Component\DomCrawler\Crawler;

class OnlinerCatalogParser implements PriceParser
{
    private $parser;

    public function __construct(SiteParser $parser)
    {
        $this->parser = $parser;
    }

//    public function getPriceByUrl(string $url)
//    {
//        $doc = new \DOMDocument();
//
//        libxml_use_internal_errors(true);
//
//        if ($content = $this->parser->getContent($url)) {
//            $doc->loadHtml($content);
//            libxml_use_internal_errors(false);
//            $tags = $doc->getElementById('product-prices-container')->getElementsByTagName('a');
//            foreach ($tags as $tag) {
//                if ($tag->hasAttribute('class')) {
//                    if ($tag->getAttribute('class') == 'offers-description__link offers-description__link_subsidiary offers-description__link_nodecor') {
//                        $prices = explode('–', trim($tag->textContent));
//                        $price = (float)str_replace(',', '.', $prices[0]);
//                        if ($price) {
//                            return $price;
//                        }
//                    }
//                }
//            }
//        }
//
//        return 0;
//    }

    public function getPriceByUrl(string $url)
    {
        if ($content = $this->parser->getContent($url)) {
            $crawler = new Crawler($content);
            $text = $crawler->filter('#product-prices-container .offers-description__link')->text();

            if ($text) {
                $prices = explode('–', trim($text));
                $price = (float)str_replace(',', '.', $prices[0]);
                if ($price) {
                    return $price;
                }
            }
        }

        return 0;
    }

}