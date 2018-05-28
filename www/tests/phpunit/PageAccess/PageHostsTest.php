<?php
namespace App\Tests\phpunit\PageAccess;

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


class PageHostsTest extends BaseTestCase
{
    private $url = '/en/profile/hosts/';
    private $urlAdd = '/en/profile/hosts/add/';
    private static $client1;
    private static $clientAdmin;
    private static $hostId;

    public static function loadFixtures()
    {
        self::createTestUser();
        self::createTestUser2();
        self::createAdminUser();
        $deletedHost = self::createHost();
        self::$hostId = $deletedHost->getId();

        self::$client1 = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'test@test.com',
            'PHP_AUTH_PW'   => 'qq',
        ));

        self::$clientAdmin = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'admin@admin.com',
            'PHP_AUTH_PW'   => 'qq',
        ));
    }

    /*
     * ---------------- LIST
     */

    /**
     * Доступ для залогиненого пользователя
     */
    public function testUserHostsList()
    {
        self::$client1->request('GET', $this->url);
        $this->assertEquals(403, self::$client1->getResponse()->getStatusCode());
    }

    /**
     * Доступ для незалогиненого пользователя
     */
    public function testNoUserHostList()
    {
        $client = static::createClient();
        $client->request('GET', $this->url);
        $this->assertContains('/login/', $client->getResponse()->getTargetUrl());
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    /**
     * Доступ для админа пользователя
     */
    public function testAdminHostList()
    {
        self::$clientAdmin->request('GET', $this->url);
        $this->assertEquals(200, self::$clientAdmin->getResponse()->getStatusCode());
    }

    /*
     * ---------------- ADD
     */

    /**
     * Доступ для залогиненого пользователя
     */
    public function testUserHostsAdd()
    {
        self::$client1->request('GET', $this->urlAdd);
        $this->assertEquals(403, self::$client1->getResponse()->getStatusCode());
    }

    /**
     * Доступ для незалогиненого пользователя
     */
    public function testNoUserHostAdd()
    {
        $client = static::createClient();
        $client->request('GET', $this->urlAdd);
        $this->assertContains('/login/', $client->getResponse()->getTargetUrl());
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    /**
     * Доступ для админа пользователя
     */
    public function testAdminHostAdd()
    {
        self::$clientAdmin->request('GET', $this->urlAdd);
        $this->assertEquals(200, self::$clientAdmin->getResponse()->getStatusCode());
    }

    /*
     * ---------------- DELETE
     */

    /**
     * Доступ для залогиненого пользователя
     */
    public function testUserHostsDelete()
    {
        self::$client1->request('GET', $this->url . self::$hostId . '/delete/');
        $this->assertEquals(403, self::$client1->getResponse()->getStatusCode());
    }

    /**
     * Доступ для незалогиненого пользователя
     */
    public function testNoUserHostDelete()
    {
        $client = static::createClient();
        $client->request('GET', $this->url . self::$hostId . '/delete/');
        $this->assertContains('/login/', $client->getResponse()->getTargetUrl());
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    /**
     * Доступ для админа пользователя
     */
    public function testAdminHostDelete()
    {
        self::$clientAdmin->request('GET', $this->url . self::$hostId . '/delete/');
        $this->assertContains('/hosts/', self::$clientAdmin->getResponse()->getTargetUrl());
        $this->assertEquals(302, self::$clientAdmin->getResponse()->getStatusCode());
    }



}