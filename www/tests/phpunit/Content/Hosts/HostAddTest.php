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
use Symfony\Component\HttpFoundation\File\UploadedFile;


class HostAddTest extends BaseTestCase
{
    private $url = '/en/profile/hosts/add/';

    private static $clientAdmin;

    /**
     * Создаю админа
     */
    protected static function loadFixtures()
    {
        self::createAdminUser();

        self::$clientAdmin = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'admin@admin.com',
            'PHP_AUTH_PW'   => 'qq',
        ));
    }

    /**
     * Загружаю хост с фоткой
     */
    public function testHostAddSuccess()
    {
        /** @var Crawler $crawler */
        $crawler = self::$clientAdmin->request('GET', $this->url);

        $buttonCrawlerNode = $crawler->selectButton('submit');
        $form = $buttonCrawlerNode->form();
        $form['add_host[host]'] = 'test1.com';
        $form['add_host[logoFile]']->upload(__DIR__ . '/files/success.jpeg');

        self::$clientAdmin->submit($form);

        $this->assertContains('/hosts/', self::$clientAdmin->getResponse()->getTargetUrl());
        $this->assertEquals(302, self::$clientAdmin->getResponse()->getStatusCode());
    }

    /**
     * Загружаю хост с файлом php
     */
    public function testHostAddFailExtension()
    {
        /** @var Crawler $crawler */
        $crawler = self::$clientAdmin->request('GET', $this->url);

        $buttonCrawlerNode = $crawler->selectButton('submit');
        $form = $buttonCrawlerNode->form();
        $form['add_host[host]'] = 'test2.com';
        $form['add_host[logoFile]']->upload(__DIR__ . '/files/failExtension.php');

        $crawler = self::$clientAdmin->submit($form);
        $count = $crawler->filter('.alert-error')->count();

        $this->assertEquals(1, $count);
    }

}