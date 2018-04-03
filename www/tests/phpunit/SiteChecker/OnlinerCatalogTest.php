<?php
namespace App\Tests\phpunit\SiteChecker;

use App\Entity\Host;
use App\Entity\Product;
use App\Entity\User;
use App\Entity\Watcher;
use App\HVF\PriceChecker\PriceParsers\OnlinerCatalogParser;
use App\HVF\PriceChecker\PriceParsers\PriceParser;
use App\Kernel;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class OnlinerCatalogTest extends WebTestCase
{
    private $url = 'https://catalog.onliner.by/memcards/samsung/mbmc64ga';

    public function testCreateHost()
    {
        $host = new Host();
        $host->setHost('catalog.onliner.by');
        $parser = $host->getParser();
        $this->assertNotEmpty($parser);
        return $parser;
    }

    /**
     * Должна изменить текущая цена товара
     * @depends testCreateHost
     */
    public function testCheckChangeProduct50(OnlinerCatalogParser $parser)
    {
        $price = $parser->getPriceByUrl($this->url);
        $this->assertGreaterThan(0, $price);
    }
}