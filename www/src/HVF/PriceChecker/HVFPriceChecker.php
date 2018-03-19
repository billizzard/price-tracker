<?php
// src/Acme/TestBundle/AcmeTestBundle.php
namespace App\HVF\PriceChecker;

use App\HVF\PriceChecker\PriceParsers\PriceParser;
use App\HVF\PriceChecker\SiteParsers\FileGetContentParser;
use App\HVF\PriceChecker\SiteParsers\SiteParser;
use function Sodium\add;

class HVFPriceChecker
{
    private $priceParser;
    private $siteParser;

    public function __construct(PriceParser $priceParser, SiteParser $siteParser = null)
    {
        $this->priceParser = $priceParser;
        $this->siteParser = new FileGetContentParser();

        if ($siteParser) {
            $this->siteParser = $siteParser;
        }

    }

    public function getPriceByUrl(string $url)
    {
        $result = null;
        //$pageContent = $this->getPageContent($url);
        //file_put_contents('page.txt', $pageContent);
        $pageContent = file_get_contents('page.txt');
        if ($pageContent) {
            $price = $this->priceParser->getPrice($pageContent);
            if (!($result = (float)$price)) {
                // logging: не нашел цену на странице
            }
        }
        return $result;
    }

    private function getPageContent($url)
    {
        return $this->siteParser->getContent($url);
    }
}