<?php
namespace App\Tests\phpunit\Security;

use App\Tests\phpunit\BaseTestCase;
use Symfony\Component\DomCrawler\Crawler;


class ChangeTest extends BaseTestCase
{
    private $url = '/en/change/';

    private static $client;
    private static $code;

    /**
     * Создаю админа
     */
    protected static function loadFixtures()
    {
        $user = self::createTestUser();
        self::$code = $user->generateConfirmCode();
        $user->setConfirmCode(self::$code);
        $user->setIsConfirmed(false);
        self::save($user);

        self::$client = static::createClient();
    }

    /**
     * Ошибка если поля пустые
     */
    public function testChangeEmpty()
    {
        /** @var Crawler $crawler */
        $crawler = self::$client->request('GET', $this->url . self::$code . '/');

        $buttonCrawlerNode = $crawler->selectButton('submit');
        $form = $buttonCrawlerNode->form();

        $crawler = self::$client->submit($form);
        $this->assertError($crawler);
    }

    /**
     * Ошибка если поля отличаются
     */
    public function testChangeDifferent()
    {
        /** @var Crawler $crawler */
        $crawler = self::$client->request('GET', $this->url . self::$code . '/');

        $buttonCrawlerNode = $crawler->selectButton('submit');
        $form = $buttonCrawlerNode->form();
        $form['change[plainPassword][first]'] = '123456';
        $form['change[plainPassword][second]'] = '654321';

        $crawler = self::$client->submit($form);
        $this->assertError($crawler);
    }

    /**
     * Успешная смена пароля
     */
    public function testChangeSuccess()
    {
        /** @var Crawler $crawler */
        $crawler = self::$client->request('GET', $this->url . self::$code . '/');

        $buttonCrawlerNode = $crawler->selectButton('submit');
        $form = $buttonCrawlerNode->form();
        $form['change[plainPassword][first]'] = '123456';
        $form['change[plainPassword][second]'] = '123456';

        self::$client->submit($form);
        $this->assertContains('/login/', self::$client->getResponse()->getTargetUrl());
        $this->assertEquals(302, self::$client->getResponse()->getStatusCode());
    }

}