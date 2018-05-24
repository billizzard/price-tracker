<?php
namespace App\Tests\phpunit\Content\Trackers;

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


class TrackerFilterTest extends BaseTestCase
{
    private $url = '/en/profile/trackers/';

    private static $user1;
    private static $user2;
    private static $client1;
    private static $clientAdmin;
    /** @var Watcher $watcher */
    private static $watcher;

    /**
     * Создаю пользователя и админа.
     */
    protected static function loadFixtures()
    {
        self::$user1 = self::createTestUser();
        self::$user2 = self::createTestUser2();
        self::createAdminUser();

        $host = self::createHost();
        $product = self::createProduct($host);
        $product2 = self::createProduct($host);
        self::$watcher = self::createWatcher($product, self::$user1);
        self::createWatcher($product, self::$user2);
        self::createWatcher($product2, self::$user2);

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
     * --------------------------   FIND BY USER
     */

    /**
     * Без фильтра видим все 3 ватчера
     */
    public function testUsersCountWatchers()
    {
        /** @var Crawler $crawler */
        $crawler = self::$clientAdmin->request('GET', $this->url);
        $count = $crawler->filter('.HVFTable tbody tr')->count();

        $this->assertEquals(3, $count);
    }

    /**
     * У первого пользователя только одит ватчер
     */
    public function testUser1CountWatchers()
    {
        /** @var Crawler $crawler */
        $crawler = self::$clientAdmin->request('GET', $this->url. '?user=' . self::$user1->getId());
        $count = $crawler->filter('.HVFTable tbody tr')->count();

        $this->assertEquals(1, $count);
    }

    /**
     * У второго пользователя только 2 ватчера
     */
    public function testUser2CountWatchers()
    {
        /** @var Crawler $crawler */
        $crawler = self::$clientAdmin->request('GET', $this->url. '?user=' . self::$user2->getId());
        $count = $crawler->filter('.HVFTable tbody tr')->count();

        $this->assertEquals(2, $count);
    }

    /**
     * У двух пользователей 3
     */
    public function testUser12CountWatchers()
    {
        /** @var Crawler $crawler */
        $crawler = self::$clientAdmin->request('GET', $this->url. '?user=' . self::$user1->getId() . ',' . self::$user2->getId());
        $count = $crawler->filter('.HVFTable tbody tr')->count();

        $this->assertEquals(3, $count);
    }

    /*
     * --------------------------   FIND BY TITLE
     */

    /**
     * Без фильтра видим все 3 ватчера
     */
    public function testFindByTitleWatchers()
    {
        /** @var Crawler $crawler */
        $crawler = self::$clientAdmin->request('GET', $this->url . '?title=' . self::$watcher->getTitle());
        $count = $crawler->filter('.HVFTable tbody tr')->count();

        $this->assertEquals(1, $count);
    }

    /*
     * --------------------------   FIND BY STATUS
     */

    /**
     * Без фильтра видим все 3 ватчера
     */
    public function testFindByStatusConfirmedWatchers()
    {
        /** @var Crawler $crawler */
        $crawler = self::$clientAdmin->request('GET', $this->url . '?status=' . Watcher::STATUS_PRICE_CONFIRMED);
        $count = $crawler->filter('.HVFTable tbody tr')->count();

        $this->assertEquals(3, $count);
    }

    /**
     * Без фильтра видим все 3 ватчера
     */
    public function testFindByStatusSuccessWatchers()
    {
        /** @var Crawler $crawler */
        $crawler = self::$clientAdmin->request('GET', $this->url . '?status=' . Watcher::STATUS_SUCCESS);
        $count = $crawler->filter('.HVFTable tbody tr')->count();

        $this->assertEquals(0, $count);
    }


}