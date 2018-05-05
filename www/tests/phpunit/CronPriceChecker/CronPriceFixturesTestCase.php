<?php
namespace App\Tests\phpunit\CronPriceChecker;

use App\Entity\Host;
use App\Entity\Product;
use App\Entity\User;
use App\Entity\Watcher;
use App\Kernel;
use App\Tests\phpunit\BaseTestCase;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class CronPriceFixturesTestCase extends BaseTestCase
{
    public static $entities = ['user', 'product', 'host', 'watcher'];


    protected static function loadFixtures()
    {
        $user = self::createTestUser();

        $host = new Host();
        $host->setHost('catalog.onliner.by');
        self::$entityManager->persist($host);
        self::$entityManager->flush();

        $product = new Product();
        $product->setHost($host);
        $product->setUrl('https://catalog.onliner.by/memcards/samsung/mbmc64ga');
        self::$entityManager->persist($product);
        self::$entityManager->flush();

        $watcher = new Watcher();
        $watcher->setUser($user);
        $watcher->setProduct($product);
        $watcher->setTitle('Onliner');
        $watcher->setStartPrice(55);
        $watcher->setPercent(10);
        $watcher->setStatus(Watcher::STATUS_NEW);

        self::$entities = [
            'user' => $user,
            'product' => $product,
            'host' => $host,
            'watcher' => $watcher,
        ];

        self::$entityManager->persist($watcher);
        self::$entityManager->flush();

    }
}