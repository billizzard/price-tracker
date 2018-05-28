<?php
namespace App\Tests\phpunit\Content;

use App\Entity\Host;
use App\Entity\Message;
use App\Entity\Product;
use App\Entity\User;
use App\Entity\Watcher;
use App\HVF\PriceChecker\PriceParsers\OnlinerCatalogParser;
use App\HVF\PriceChecker\PriceParsers\PriceParser;
use App\Kernel;
use App\Tests\phpunit\BaseTestCase;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;


class HostListTest extends BaseTestCase
{
    private $url = '/en/profile/hosts/';

    private static $clientAdmin;
    private static $hostId;

    /**
     * Создаю 2 простых пользователей и админа.
     * У первого пользователя одно сообщение у второго 3 (одно из них удаленное).
     */
    protected static function loadFixtures()
    {
        self::createAdminUser();

        self::createHost();
        $hostForDelete = self::createHost();
        self::$hostId = $hostForDelete->getId();

        $deletedHost = self::createHost();
        $deletedHost->delete();
        self::$entityManager->persist($deletedHost);
        self::$entityManager->flush();

        self::$clientAdmin = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'admin@admin.com',
            'PHP_AUTH_PW'   => 'qq',
        ));
    }

    /**
     * Должен видеть два хоста
     */
    public function testCount2Host()
    {
        $crawler = self::$clientAdmin->request('GET', $this->url);
        $count = $crawler->filter('.host-list table tbody tr')->count();

        $this->assertEquals(2, $count);
    }

    /**
     * После удаления должен видеть один хост
     * @depends testCount2Host
     */
    public function testDeleteHost()
    {
        self::$clientAdmin->request('GET', $this->url . self::$hostId . '/delete/');
        $this->assertContains('/hosts/', self::$clientAdmin->getResponse()->getTargetUrl());
        $this->assertEquals(302, self::$clientAdmin->getResponse()->getStatusCode());
    }

    /**
     * После удаления должен видеть один хост
     * @depends testDeleteHost
     */
    public function testCount1Host()
    {
        self::$clientAdmin->request('GET', $this->url . self::$hostId . '/delete/');
        $crawler = self::$clientAdmin->request('GET', $this->url);
        $count = $crawler->filter('.host-list table tbody tr')->count();

        $this->assertEquals(1, $count);
    }

}