<?php
namespace App\Tests\phpunit\CronPriceChecker;

use App\Entity\Message;
use App\Entity\PriceTracker;
use App\Entity\Product;
use App\Entity\Watcher;
use App\Repository\MessageRepository;
use App\Repository\PriceTrackerRepository;
use App\Repository\ProductRepository;
use App\Repository\WatcherRepository;

class PriceCheckerTest extends CronPriceFixturesTestCase
{
    /* ------------------------ Запускаем крон с новым товаром  -------------------------------------*/

    public function testRunCron50()
    {
        $this->runCommandWithPrice(50);
        $this->assertEquals(false, false);
    }

    /**
     * Должна изменится текущая цена товара, так как изначально она равна 0, пока товар хоть один таз не протрекался
     * @depends testRunCron50
     */
    public function testCheckChangeProduct50()
    {
        /** @var ProductRepository $repository */
        $repository = self::$entityManager->getRepository(Product::class);
        /** @var Product $product */
        $product = $repository->find(static::$entities['product']->getId());
        $this->assertEquals(50, $product->getCurrentPrice());
    }

    /**
     * Должна быть запись о цене товара в price tracker, так как цена изменилась, а при ее изменении запись должна быть
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
     * Статус ватчера должен измениться из нового на подтвержденного (что товар есть и отслеживается)
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
     * Сообщений (с типом успешно) не должно быть, так как это не цена, которую ожидает пользователь
     * @depends testRunCron50
     */
    public function testCheckNotSuccessMessage50()
    {
        /** @var MessageRepository $repository */
        $repository = self::$entityManager->getRepository(Message::class);
        /** @var Message $message */
        $message = $repository->findOneBy(['user' => self::$entities['user']->getId(), 'type' => Message::TYPE_SUCCESS]);
        $this->assertEquals(false, $message);
    }

    /**
     * Должно быть сообщение о изменении цены товара, так как цена на товар изменилась
     * @depends testRunCron50
     */
    public function testCheckChangePriceMessage50()
    {
        /** @var MessageRepository $repository */
        $repository = self::$entityManager->getRepository(Message::class);
        /** @var Message $message */
        $message = $repository->findOneBy(['user' => self::$entities['user']->getId(), 'type' => Message::TYPE_CHANGE_PRICE]);
        $this->assertEquals(Message::TYPE_CHANGE_PRICE, $message->getType());
        $this->assertNotEmpty($message->getTitle());
        $this->assertNotEmpty($message->getMessage());
    }

    /* ------------------------ Увеличение цены  -------------------------------------*/

    /**
     * @depends testRunCron50
     */
    public function testRunCron60()
    {
        $this->runCommandWithPrice(60);
        $this->assertEquals(false, false);
    }

    /**
     * Должна измениться текущая цена товара
     * @depends testRunCron60
     */
    public function testCheckChangeProduct60()
    {
        self::$kernel->boot();
        self::$entityManager = self::$kernel->getContainer()->get('doctrine')->getManager();
        /** @var ProductRepository $repository */
        $repository = self::$entityManager->getRepository(Product::class);
        /** @var Product $product */
        $product = $repository->find(self::$entities['product']->getId());
        $this->assertEquals(60, $product->getCurrentPrice());
    }

    /**
     * Должна быть запись о новой цене товара в price tracker
     * @depends testRunCron60
     */
    public function testCheckAddPriceTracker60()
    {
        /** @var PriceTrackerRepository $repository */
        $repository = self::$entityManager->getRepository(PriceTracker::class);
        /** @var PriceTracker $priceTracker */
        $priceTracker = $repository->findOneBy(['product' => self::$entities['product']->getId(), 'price' => 60]);
        $this->assertEquals(60, $priceTracker->getPrice());
    }

    /**
     * Статус ватчера не должен измениться
     * @depends testRunCron60
     */
    public function testCheckNotChangeWatcher60()
    {
        /** @var WatcherRepository $repository */
        $repository = self::$entityManager->getRepository(Watcher::class);
        /** @var Watcher $watcher */
        $watcher = $repository->findOneBy(['product' => self::$entities['product']->getId(), 'user' => self::$entities['user']->getId()]);
        $this->assertEquals(Watcher::STATUS_PRICE_CONFIRMED, $watcher->getStatus());
    }

    /**
     * Сообщений не должно быть, так как это не цена, которую ожидает пользователь
     * @depends testRunCron60
     */
    public function testCheckNotSuccessMessage60()
    {
        /** @var MessageRepository $repository */
        $repository = self::$entityManager->getRepository(Message::class);
        /** @var Message $message */
        $message = $repository->findOneBy(['user' => self::$entities['user']->getId(), 'type' => Message::TYPE_SUCCESS]);

        $this->assertEquals(false, $message);
    }

    /**
     * Сообщений должно быть 2 так как цена изменялась 2 раза
     * @depends testRunCron60
     */
    public function testCheckCountMessages60()
    {
        /** @var MessageRepository $repository */
        $repository = self::$entityManager->getRepository(Message::class);
        /** @var Message $message */
        $message = $repository->findBy(['user' => self::$entities['user']->getId(), 'type' => Message::TYPE_CHANGE_PRICE]);

        $this->assertEquals(2, count($message));
    }

    /* ------------------------ Уменьшение цены, подходящей для пользователя  -------------------------------------*/

    /**
     * @depends testRunCron60
     */
    public function testRunCron45()
    {
        $this->runCommandWithPrice(45);
        $this->assertEquals(false, false);
    }

    /**
     * Изменяет текущую цену товара
     * @depends testRunCron45
     */
    public function testCheckChangeProduct45()
    {
        self::$kernel->boot();
        self::$entityManager = self::$kernel->getContainer()->get('doctrine')->getManager();
        /** @var ProductRepository $repository */
        $repository = self::$entityManager->getRepository(Product::class);
        /** @var Product $product */
        $product = $repository->find(self::$entities['product']->getId());
        $this->assertEquals(45, $product->getCurrentPrice());
    }

    /**
     * Добавляет запись в Price Tracker
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
     * Изменяет статус Ватчера на SUCCESS
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
     * Добавляет сообщение пользователю
     * @depends testRunCron45
     */
    public function testCheckMessage45()
    {
        /** @var MessageRepository $repository */
        $repository = self::$entityManager->getRepository(Message::class);
        /** @var Message $message */
        $message = $repository->findOneBy(['user' => self::$entities['user']->getId(), 'type' => Message::TYPE_SALE_SUCCESS]);

        $this->assertEquals(Message::TYPE_SALE_SUCCESS, $message->getType());
        $this->assertNotEmpty($message->getTitle());
        $this->assertNotEmpty($message->getMessage());
    }

    /* ------------------------ Повторное уменьшение цены  -------------------------------------*/

    /**
     * @depends testRunCron45
     */
    public function testRunCron35()
    {
        $this->runCommandWithPrice(35);
        $this->assertEquals(false, false);
    }

    /**
     * Должно изменить текущую цену продукта
     * @depends testRunCron45
     */
    public function testCheckChangeProduct35()
    {
        self::$kernel->boot();
        self::$entityManager = self::$kernel->getContainer()->get('doctrine')->getManager();
        /** @var ProductRepository $repository */
        $repository = self::$entityManager->getRepository(Product::class);
        /** @var Product $product */
        $product = $repository->find(self::$entities['product']->getId());
        $this->assertEquals(35, $product->getCurrentPrice());
    }


    /**
     * Должно добавить запись в Price Tracker
     * @depends testRunCron35
     */
    public function testCheckAddPriceTracker35()
    {
        /** @var PriceTrackerRepository $repository */
        $repository = self::$entityManager->getRepository(PriceTracker::class);
        /** @var PriceTracker $priceTracker */
        $priceTracker = $repository->findOneBy(['product' => self::$entities['product']->getId(), 'price' => 35]);
        $this->assertEquals(35, $priceTracker->getPrice());
    }

    /**
     * Не должно изменить Ватчер
     * @depends testRunCron35
     */
    public function testCheckChangeWatcher35()
    {
        /** @var WatcherRepository $repository */
        $repository = self::$entityManager->getRepository(Watcher::class);
        /** @var Watcher $watcher */
        $watcher = $repository->findOneBy(['product' => self::$entities['product']->getId(), 'user' => self::$entities['user']->getId()]);
        $this->assertEquals(Watcher::STATUS_SUCCESS, $watcher->getStatus());
    }

    /**
     * Не должно прибавить еще одно сообщение о успехе
     * @depends testRunCron35
     */
    public function testCheckMessage35()
    {
        /** @var MessageRepository $repository */
        $repository = self::$entityManager->getRepository(Message::class);
        /** @var Message $message */
        $message = $repository->findBy(['user' => self::$entities['user']->getId(), 'type' => Message::TYPE_SALE_SUCCESS]);

        $this->assertEquals(1, count($message));
    }

    /* ------------------------ Цена не изменилась  -------------------------------------*/

    /**
     * @depends testRunCron35
     */
    public function testRunCron35_2()
    {
        $this->runCommandWithPrice(35);
        $this->assertEquals(false, false);
    }

    /**
     * Не должно изменить текущую цену продукта
     * @depends testRunCron35_2
     */
    public function testCheckChangeProduct35_2()
    {
        self::$kernel->boot();
        self::$entityManager = self::$kernel->getContainer()->get('doctrine')->getManager();
        /** @var ProductRepository $repository */
        $repository = self::$entityManager->getRepository(Product::class);
        /** @var Product $product */
        $product = $repository->find(self::$entities['product']->getId());
        $this->assertEquals(35, $product->getCurrentPrice());
    }


    /**
     * Должно добавить запись в Price Tracker
     * @depends testRunCron35_2
     */
    public function testCheckAddPriceTracker35_2()
    {
        /** @var PriceTrackerRepository $repository */
        $repository = self::$entityManager->getRepository(PriceTracker::class);
        /** @var PriceTracker $priceTracker */
        $priceTracker = $repository->findBy(['product' => self::$entities['product']->getId()]);
        $this->assertEquals(4, count($priceTracker));
    }

    /**
     * Не должно прибавить еще одно сообщение о успехе
     * @depends testRunCron35_2
     */
    public function testCheckMessage35_2()
    {
        /** @var MessageRepository $repository */
        $repository = self::$entityManager->getRepository(Message::class);
        /** @var Message $message */
        $message = $repository->findBy(['user' => self::$entities['user']->getId(), 'type' => Message::TYPE_SALE_SUCCESS]);

        $this->assertEquals(1, count($message));
    }
}