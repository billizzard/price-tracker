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


class PageTrackersTest extends BaseTestCase
{
    private $url = '/en/profile/trackers/';
    private static $data = [];

    private static $client1 = null;
    private static $client2 = null;
    private static $clientAdmin = null;

    public static function loadFixtures()
    {
        $user1 = self::createTestUser();
        self::createTestUser2();
        self::createAdminUser();

        $host = self::createHost();
        $product = self::createProduct($host);
        $deletedWatcher = self::createWatcher($product, $user1);
        $deletedWatcher->delete();
        self::$entityManager->persist($deletedWatcher);
        self::$entityManager->flush();
        self::$data['user1Watcher'] = self::createWatcher($product, $user1);
        self::$data['user1DeletedWatcher'] = $deletedWatcher;
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
     *  --------------------------  LIST
     */

    /**
     * Доступ для залогиненого пользователя
     */
    public function testUserTrackersList()
    {
        self::$client1->request('GET', $this->url);

        $this->assertEquals(200, self::$client1->getResponse()->getStatusCode());
    }

    /**
     * Доступ для незалогиненого пользователя
     */
    public function testNoUserTrackersList()
    {
        $client = static::createClient();
        $client->request('GET', $this->url);
        $this->assertContains('/login/', $client->getResponse()->getTargetUrl());
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    /**
     * Доступ для админа пользователя
     */
    public function testAdminTrackersList()
    {
        self::$clientAdmin->request('GET', $this->url);
        $this->assertEquals(200, self::$clientAdmin->getResponse()->getStatusCode());
    }

    /*
     *  --------------------------  VIEW
     */
    /**
     * Доступ для залогиненого пользователя, к его товару
     */
    public function testUserOwnerTrackerView()
    {
        self::$client1->request('GET', $this->url . self::$data['user1Watcher']->getId() . '/view/');
        $this->assertEquals(200, self::$client1->getResponse()->getStatusCode());
    }

    /**
     * Доступ для залогиненого пользователя, к не его товару
     */
    public function testUserNotOwnerTrackerView()
    {
        self::$client2->request('GET', $this->url . self::$data['user1Watcher']->getId() . '/view/');
        $this->assertEquals(404, self::$client2->getResponse()->getStatusCode());
    }

    /**
     * Доступ для залогиненого пользователя, к его удаленному товару
     */
    public function testUserOwnerDeletedTrackerView()
    {
        self::$client1->request('GET', $this->url . self::$data['user1DeletedWatcher']->getId() . '/view/');
        $this->assertEquals(404, self::$client1->getResponse()->getStatusCode());
    }

    /**
     * Просмотр для незалогиненого пользователя
     */
    public function testNoUserTrackersView()
    {
        $client = static::createClient();
        $client->request('GET', $this->url . self::$data['user1Watcher']->getId() . '/view/' );
        $this->assertContains('/login/', $client->getResponse()->getTargetUrl());
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    /*
     *  --------------------------  EDIT
     */

    /**
     * Редактирование для незалогиненого пользователя
     */
    public function testNoUserTrackersEdit()
    {
        $client = static::createClient();
        $client->request('GET', $this->url . self::$data['user1Watcher']->getId() . '/edit/' );
        $this->assertContains('/login/', $client->getResponse()->getTargetUrl());
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    /**
     * Редактирование для залогиненого пользователя, к его товару
     */
    public function testUserOwnerTrackerEdit()
    {
        self::$client1->request('GET', $this->url . self::$data['user1Watcher']->getId() . '/edit/');
        $this->assertEquals(200, self::$client1->getResponse()->getStatusCode());
    }

    /**
     * Редактирование для залогиненого пользователя, к не его товару
     */
    public function testUserNotOwnerTrackerEdit()
    {
        self::$client2->request('GET', $this->url . self::$data['user1Watcher']->getId() . '/edit/');
        $this->assertEquals(404, self::$client2->getResponse()->getStatusCode());
    }

    /**
     * Редактирование для залогиненого пользователя, к его удаленному товару
     */
    public function testUserOwnerDeletedTrackerEdit()
    {
        self::$client1->request('GET', $this->url . self::$data['user1DeletedWatcher']->getId() . '/edit/');
        $this->assertEquals(404, self::$client1->getResponse()->getStatusCode());
    }

    /*
     *  --------------------------  ADD
     */

    /**
     * Добавление для незалогиненого пользователя
     */
    public function testNoUserTrackersAdd()
    {
        $client = static::createClient();
        $client->request('GET', $this->url . 'add/' );
        $this->assertContains('/login/', $client->getResponse()->getTargetUrl());
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    /**
     * Добавление для залогиненого пользователя
     */
    public function testUserTrackerAdd()
    {
        self::$client1->request('GET', $this->url . 'add/');
        $this->assertEquals(200, self::$client1->getResponse()->getStatusCode());
    }

    /*
     *  --------------------------  DELETE
     */

    /**
     * Доступ для удаления не своего ватчера
     */
    public function testUserNotOwnerTrackerDelete()
    {
        self::$client2->request('GET', $this->url . self::$data['user1Watcher']->getId() . '/delete/');
        $this->assertEquals(404, self::$client2->getResponse()->getStatusCode());
    }

    /**
     * Доступ для удаления не своего ватчера
     */
    public function testNoUserTrackerDelete()
    {
        $client = static::createClient();
        $client->request('GET', $this->url . self::$data['user1Watcher']->getId() . '/delete/');
        $this->assertContains('/login/', $client->getResponse()->getTargetUrl());
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    /**
     * Доступ для удаления своего ватчера
     */
    public function testUserOwnerTrackerDelete()
    {
        self::$client1->request('GET', $this->url . self::$data['user1Watcher']->getId() . '/delete/');
        $this->assertContains('/trackers/', self::$client1->getResponse()->getTargetUrl());
        $this->assertEquals(302, self::$client1->getResponse()->getStatusCode());
    }

}