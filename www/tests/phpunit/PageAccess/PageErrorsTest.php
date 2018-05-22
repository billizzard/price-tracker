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


class PageErrorsTest extends BaseTestCase
{
    private $url = '/en/profile/errors/';
    private static $client1;
    private static $client2;
    private static $clientAdmin;

    public static function loadFixtures()
    {
        self::createTestUser();
        self::createTestUser2();
        self::createAdminUser();

        self::$client1 = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'test@test.com',
            'PHP_AUTH_PW'   => 'qq',
        ));
        self::$client2 = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'test2@test.com',
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
    public function testUserMessagesList()
    {
        self::$client1->request('GET', $this->url);
        $this->assertEquals(403, self::$client1->getResponse()->getStatusCode());
    }

    /**
     * Доступ для незалогиненого пользователя
     */
    public function testNoUserMessagesList()
    {
        $client = static::createClient();
        $client->request('GET', $this->url);
        $this->assertContains('/login/', $client->getResponse()->getTargetUrl());
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    /**
     * Доступ для админа пользователя
     */
    public function testAdminMessagesList()
    {
        self::$clientAdmin->request('GET', $this->url);
        $this->assertEquals(200, self::$clientAdmin->getResponse()->getStatusCode());

    }



}