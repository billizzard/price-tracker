<?php
namespace App\Tests\Repository;

use App\Entity\Watcher;
use App\Repository\WatcherRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;


class WatcherRepositoryTest123 extends KernelTestCase
{
    private static $entityManager;

    public static function setUpBeforeClass()
    {
        $kernel = self::bootKernel();
        self::$entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        //self::$entityManager
        $watcher = new Watcher();
        $watcher->setStatus(Watcher::STATUS_NEW);
        $watcher->setPercent(10);
        $watcher->setStartPrice(200);

        $watcher2 = new Watcher();
        $watcher2->setStatus(Watcher::STATUS_SUCCESS);
        $watcher2->setPercent(15);
        $watcher2->setStartPrice(200);

        $watcher2 = new Watcher();
        $watcher2->setStatus(Watcher::STATUS_PRICE_CONFIRMED);
        $watcher2->setPercent(15);
        $watcher2->setStartPrice(200);

        self::$entityManager->persist($watcher);
        self::$entityManager->persist($watcher2);
        self::$entityManager->flush($watcher);
    }

    protected function setUp()
    {
//        $watcher = new Watcher();
//        $watcher->setStatus(Watcher::STATUS_NEW);
//        $watcher->setPercent(10);
//        $watcher->setStartPrice(200);
//
//        $this->stack = [];
    }

    public function testFindActiveCount()
    {
        $products = self::$entityManager->getRepository(Watcher::class)->findActive();

        $this->assertEquals(2, count($products));
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        /** @var WatcherRepository $products */
        $products = self::$entityManager->getRepository(Watcher::class);
        $products->clear();

        self::$entityManager->close();
        self::$entityManager = null; // avoid memory leaks
    }
}