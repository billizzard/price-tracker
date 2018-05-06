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


class MessageListTest extends BaseTestCase
{
    private $url = '/en/profile/messages/';

    /**
     * Создаю 2 простых пользователей и админа.
     * У первого пользователя одно сообщение у второго 3 (одно из них удаленное).
     */
    protected static function loadFixtures()
    {
        $user1 = self::createTestUser();
        $user2 = self::createTestUser2();
        $userAdmin = self::createAdminUser();

        self::createMessage($user1);
        self::createMessage($user2);
        self::createMessage($user2);
        $deletedMessage = self::createMessage($user2);
        $deletedMessage->setStatus(Message::STATUS_DELETED);
        self::$entityManager->persist($deletedMessage);
        self::$entityManager->flush();
    }

    /**
     * Первый пользователь должен видеть только одно сообщение
     */
    public function testUser1CountMessages()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'test@test.com',
            'PHP_AUTH_PW'   => 'qq',
        ));

        $crawler = $client->request('GET', $this->url);
        $count = $crawler->filter('.mailbox-messages tr')->count();

        $this->assertEquals(1, $count);
    }

    /**
     * Первый пользователь должен видеть только два сообщения
     */
    public function testUser2CountMessages()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'test2@test.com',
            'PHP_AUTH_PW'   => 'qq',
        ));

        $crawler = $client->request('GET', $this->url);
        $count = $crawler->filter('.mailbox-messages tr')->count();

        $this->assertEquals(2, $count);
    }
}