<?php
// src/Acme/TestBundle/AcmeTestBundle.php
namespace App\HVF\PriceChecker;

use App\HVF\PriceChecker\Parsers\PriceParser;

class HVFPriceChecker
{
    private $priceParser;

    public function __construct(PriceParser $priceParser)
    {
        $this->priceParser = $priceParser;
    }

    public function getPriceByUrl(string $url)
    {
        $result = null;
        $pageContent = $this->getPageContent($url);
        if ($pageContent) {
            $price = $this->priceParser->getPrice($pageContent);
            if (!($result = (float)$price)) {
                $a = 'Ошибка';
            }
        }
        return $result;
    }

    private function getPageContent($url)
    {
        return 'dfdf';
    }
}