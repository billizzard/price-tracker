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


class AdminErrorMessagesTest extends BaseTestCase
{
    private $url = '/en/profile/messages/';

    private static $client1;
    private static $clientAdmin;

    /**
     * Создаю 2 простых пользователей и админа.
     * У первого пользователя одно сообщение у второго 3 (одно из них удаленное).
     */
    protected static function loadFixtures()
    {
        self::createTestUser();
        self::createAdminUser();
        self::createError();
//        $errorDeleted = self::createError();
//        $errorDeleted->delete();
//        self::$entityManager->persist($errorDeleted);
//        self::$entityManager->flush();

        self::$client1 = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'test@test.com',
            'PHP_AUTH_PW'   => 'qq',
        ));

        self::$clientAdmin = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'admin@admin.com',
            'PHP_AUTH_PW'   => 'qq',
        ));
    }

    /**
     * Админ должен видеть в левом меню "Сообщение" итем ошибки
     */
    public function testAdminLeftErrorItem()
    {
        $crawler = self::$clientAdmin->request('GET', $this->url);
        $countLeft = $crawler->filter('#errors-list')->count();
        $countTop = $crawler->filter('.error-notification')->count();

        $this->assertEquals(1, $countLeft);
        $this->assertEquals(1, $countTop);
    }

    /**
     * Простой пользователь не должен видеть итем "Ошибки"
     */
    public function testUserLeftErrorItem()
    {
        $crawler = self::$client1->request('GET', $this->url);
        $countLeft = $crawler->filter('#errors-list')->count();
        $countTop = $crawler->filter('.error-notification')->count();

        $this->assertEquals(0, $countLeft);
        $this->assertEquals(0, $countTop);
    }


}