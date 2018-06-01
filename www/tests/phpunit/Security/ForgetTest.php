<?php
namespace App\Tests\phpunit\Security;

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
use Symfony\Component\DomCrawler\Crawler;


class ForgetTest extends BaseTestCase
{
    private $url = '/en/forgot/';

    private static $user1;

    /**
     * Создаю админа
     */
    protected static function loadFixtures()
    {
        self::createTestUser();
        self::createTestUser2();

        self::$user1 = static::createClient();
    }

    /**
     * Загружаю хост с файлом php
     */
    public function testForgetWrongData()
    {
        /** @var Crawler $crawler */
        $crawler = self::$user1->request('GET', $this->url);

        $buttonCrawlerNode = $crawler->selectButton('submit');
        $form = $buttonCrawlerNode->form();
        $form['forgot[email]'] = 'test';

        $crawler = self::$user1->submit($form);
        $count = $crawler->filter('.alert-error')->count();

        $this->assertEquals(1, $count);
    }

    /**
     * Загружаю хост с файлом php
     */
    public function testForgetEmail()
    {
        self::$user1 = static::createClient();

        /** @var Crawler $crawler */
        $crawler = self::$user1->request('GET', $this->url);

        $buttonCrawlerNode = $crawler->selectButton('submit');
        $form = $buttonCrawlerNode->form();
        $form['forgot[email]'] = 'test@test.com';

        self::$user1->enableProfiler();
        self::$user1->submit($form);

        $mailCollector = self::$user1->getProfile()->getCollector('swiftmailer');

        $message = $mailCollector->getMessages()[0];

        $this->assertSame(1, $mailCollector->getMessageCount());
        $this->assertSame('Change password', $message->getSubject());
    }

    /**
     * Загружаю хост с файлом php
     * @depends testForgetEmail
     */
    public function testOftenChange()
    {
        /** @var Crawler $crawler */
        $crawler = self::$user1->request('GET', $this->url);

        $buttonCrawlerNode = $crawler->selectButton('submit');
        $form = $buttonCrawlerNode->form();
        $form['forgot[email]'] = 'test@test.com';

        self::$user1->submit($form);

        $crawler = self::$user1->submit($form);
        $count = $crawler->filter('.alert-error')->count();

        $this->assertEquals(1, $count);
    }
}