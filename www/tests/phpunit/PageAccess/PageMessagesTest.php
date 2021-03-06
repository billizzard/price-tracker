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


class PageMessagesTest extends BaseTestCase
{
    private $url = '/en/profile/messages/';
    private static $client1;
    private static $client2;
    private static $clientAdmin;
    private static $data = [];


    public static function loadFixtures()
    {
        $user1 = self::createTestUser();
        self::createTestUser2();
        self::createAdminUser();

        $deletedMessage = self::createMessage($user1);
        $deletedMessage->delete();
        self::$entityManager->persist($deletedMessage);
        self::$entityManager->flush();
        self::$data['user1Message'] = self::createMessage($user1);
        self::$data['user1DeletedMessage'] = $deletedMessage;

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
        $this->assertEquals(200, self::$client1->getResponse()->getStatusCode());
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

    /*
     * ---------------------- VIEW
     */

    /**
     * Доступ пользователя, к его сообщению
     */
    public function testUserOwnerMessageView()
    {
        self::$client1->request('GET', $this->url . self::$data['user1Message']->getId() . '/view/');
        $this->assertEquals(200, self::$client1->getResponse()->getStatusCode());
    }

    /**
     * Доступ пользователя, к его удаленному сообщению
     */
    public function testUserOwnerDeletedMessageView()
    {
        self::$client1->request('GET', $this->url . self::$data['user1DeletedMessage']->getId() . '/view/');
        $this->assertEquals(404, self::$client1->getResponse()->getStatusCode());
    }

    /**
     * Доступ пользователя, к не его сообщению
     */
    public function testUserNoOwnerMessageView()
    {
        self::$client2->request('GET', $this->url . self::$data['user1Message']->getId() . '/view/');
        $this->assertEquals(404, self::$client1->getResponse()->getStatusCode());
    }
}