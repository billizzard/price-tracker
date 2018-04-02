<?php
namespace App\Tests\phpunit\Price;

use App\Entity\Message;
use App\Entity\PriceTracker;
use App\Entity\Product;
use App\Entity\Watcher;
use App\Repository\MessageRepository;
use App\Repository\PriceTrackerRepository;
use App\Repository\ProductRepository;
use App\Repository\WatcherRepository;

class PriceCheckerTest extends DataFixturesTestCase
{
    public function testRunCron50()
    {
        $client = static::createClient();
        $client->request('GET', '/cron/price-checker/?price=50');
        $response = $client->getResponse()->getContent();

        file_put_contents('aaaaaaaaaaaa.txt', $response);
        $this->assertEquals(false, false);
    }

    /**
     * @depends testRunCron50
     */
    public function testCheckChangeProduct50()
    {
        /** @var ProductRepository $repository */
        $repository = self::$entityManager->getRepository(Product::class);
        /** @var Product $product */
        $product = $repository->find(self::$entities['product']->getId());
        self::$entityManager->persist($product);
        $this->assertEquals(50, $product->getCurrentPrice());
    }

    /**
     * @depends testRunCron50
     */
    public function testCheckAddPriceTracker50()
    {
        /** @var PriceTrackerRepository $repository */
        $repository = self::$entityManager->getRepository(PriceTracker::class);
        /** @var PriceTracker $priceTracker */
        $priceTracker = $repository->findOneBy(['product' => self::$entities['product']->getId()]);
        $this->assertEquals(50, $priceTracker->getPrice());
    }

    /**
     * @depends testRunCron50
     */
    public function testCheckNotChangeWatcher50()
    {
        /** @var WatcherRepository $repository */
        $repository = self::$entityManager->getRepository(Watcher::class);
        /** @var Watcher $watcher */
        $watcher = $repository->findOneBy(['product' => self::$entities['product']->getId(), 'user' => self::$entities['user']->getId()]);
        $this->assertEquals(Watcher::STATUS_PRICE_CONFIRMED, $watcher->getStatus());
    }

    /**
     * @depends testRunCron50
     */
    public function testCheckNotMessage50()
    {
        /** @var MessageRepository $repository */
        $repository = self::$entityManager->getRepository(Message::class);
        /** @var Message $message */
        $message = $repository->findOneBy(['user' => self::$entities['user']->getId()]);

        $this->assertEquals(false, $message);
    }

    /**
     * @depends testRunCron50
     */
    public function testRunCron45()
    {
        $client = static::createClient();
        $client->request('GET', '/cron/price-checker/?price=45');
        $this->assertEquals(false, false);
    }

    /**
     * @depends testRunCron45
     */
    public function testCheckChangeProduct45()
    {
//        echo "<pre>";
//        var_dump(self::$entityManager);
//        echo "</pre>";
//        die();

        /** @var ProductRepository $repository */
        $repository = self::$entityManager->getRepository(Product::class);
        /** @var Product $product */
        $product = $repository->find(self::$entities['product']->getId());
//        self::$entityManager->flush();
//        self::$entityManager->clear();
        //self::$entityManager->detach($product);
        //self::$entityManager->clear();
        self::$entityManager->flush();
//        self::$entityManager->detach($product);
//        $product = $repository->find(self::$entities['product']->getId());
        //$product = $repository->find(self::$entities['product']->getId());
        echo $product->getId() . ':::' . $product->getCurrentPrice() . PHP_EOL;

        $kernel = self::bootKernel();
        $entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        $repository = $entityManager->getRepository(Product::class);
        $product = $repository->find(self::$entities['product']->getId());
        echo $product->getId() . ':::' . $product->getCurrentPrice();

        $this->assertEquals(45, $product->getCurrentPrice());
    }

    /**
     * @depends testRunCron45
     */
    public function testCheckAddPriceTracker45()
    {
        /** @var PriceTrackerRepository $repository */
        $repository = self::$entityManager->getRepository(PriceTracker::class);
        /** @var PriceTracker $priceTracker */
        $priceTracker = $repository->findOneBy(['product' => self::$entities['product']->getId(), 'price' => 45]);
        $this->assertEquals(45, $priceTracker->getPrice());
    }

    /**
     * @depends testRunCron45
     */
    public function testCheckChangeWatcher45()
    {
        /** @var WatcherRepository $repository */
        $repository = self::$entityManager->getRepository(Watcher::class);
        /** @var Watcher $watcher */
        $watcher = $repository->findOneBy(['product' => self::$entities['product']->getId(), 'user' => self::$entities['user']->getId()]);
        $this->assertEquals(Watcher::STATUS_SUCCESS, $watcher->getStatus());
    }

    /**
     * @depends testRunCron45
     */
    public function testCheckNotMessage45()
    {
        /** @var MessageRepository $repository */
        $repository = self::$entityManager->getRepository(Message::class);
        /** @var Message $message */
        $message = $repository->findOneBy(['user' => self::$entities['user']->getId()]);

        $this->assertEquals(Message::TYPE_SUCCESS, $message->getType());
    }

}