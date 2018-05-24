<?php
namespace App\HVF\PriceChecker\PriceParsers;

use App\HVF\PriceChecker\SiteParsers\SiteParser;
use Symfony\Component\DomCrawler\Crawler;

class OnlinerBaraholkaParser implements PriceParser
{
    private $parser;

    public function __construct(SiteParser $parser)
    {
        $this->parser = $parser;
    }

    public function getPriceByUrl(string $url)
    {
        if ($content = $this->parser->getContent($url)) {
            $crawler = new Crawler($content);
            $text = $crawler->filter('.price-primary')->text();

            if ($text) {
                $prices = explode(' ', trim($text));
                $price = (float)str_replace(',', '.', $prices[0]);
                if ($price) {
                    return $price;
                }
            }
        }

        return 0;
    }

}