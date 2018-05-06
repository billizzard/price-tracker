<?php
namespace App\Tests\phpunit\PageAccess;

use App\Entity\Host;
use App\Entity\Product;
use App\Entity\User;
use App\Entity\Watcher;
use App\HVF\PriceChecker\PriceParsers\OnlinerCatalogParser;
use App\HVF\PriceChecker\PriceParsers\PriceParser;
use App\Kernel;
use App\Tests\phpunit\BaseTestCase;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class PageMessagesTest extends BaseTestCase
{
    private $url = '/en/profile/messages/';

    /**
     * Доступ для залогиненого пользователя
     */
    public function testUserMessagesList()
    {
        self::createTestUser();
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'test@test.com',
            'PHP_AUTH_PW'   => 'qq',
        ));

        $client->request('GET', $this->url);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * Доступ для незалогиненого пользователя
     */
    public function testNoUserMessagesList()
    {
        $client = static::createClient();
        $client->request('GET', $this->url);
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    /**
     * Доступ для админа пользователя
     */
    public function testAdminMessagesList()
    {
        self::createAdminUser();
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'admin@admin.com',
            'PHP_AUTH_PW'   => 'qq',
        ));

        $client->request('GET', $this->url);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}