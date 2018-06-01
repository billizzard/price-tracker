<?php
namespace App\Tests\phpunit;

use App\Entity\Base;
use App\Entity\Error;
use App\Entity\Host;
use App\Entity\Message;
use App\Entity\Product;
use App\Entity\User;
use App\Entity\Watcher;
use App\Kernel;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;


class BaseTestCase extends WebTestCase
{
    /** @var EntityManager entityManager */
    protected static $entityManager;
    private static $uniq = 1;

    public static function getKernelClass()
    {
        return Kernel::class;
    }

    public static function setUpBeforeClass(): void
    {
        self::$kernel = self::bootKernel();
        self::$entityManager = self::$kernel->getContainer()->get('doctrine')->getManager();
        self::clearTables();
        static::loadFixtures();
        parent::setUpBeforeClass();
    }

    protected static function loadFixtures()
    {
    }

    public static function tearDownAfterClass(): void
    {
        self::clearTables();
        parent::tearDownAfterClass();
    }

    protected static function save(Base $entity): void
    {
        self::$entityManager->persist($entity);
        self::$entityManager->flush();
    }

    protected static function createTestUser(): User
    {
        $user = new User();
        $user->setEmail('test@test.com');
        $user->setRoles('ROLE_USER');
        $user->setIsConfirmed(true);
        $user->setPassword('$2y$12$A1comgHtNSnZwjz09PIWhuD2DOt2iV8rwG74pZ9t9apQzkwdpYByC');
        $user->setNickName('User_Test');
        self::$entityManager->persist($user);
        self::$entityManager->flush();
        return $user;
    }

    protected static function createTestUser2(): User
    {
        $user = new User();
        $user->setEmail('test2@test.com');
        $user->setRoles('ROLE_USER');
        $user->setPassword('$2y$12$A1comgHtNSnZwjz09PIWhuD2DOt2iV8rwG74pZ9t9apQzkwdpYByC');
        $user->setNickName('User_Test2');
        $user->setIsConfirmed(true);
        self::$entityManager->persist($user);
        self::$entityManager->flush();
        return $user;
    }

    protected static function createAdminUser(): User
    {
        $user = new User();
        $user->setEmail('admin@admin.com');
        $user->setRoles('ROLE_ADMIN');
        $user->setPassword('$2y$12$A1comgHtNSnZwjz09PIWhuD2DOt2iV8rwG74pZ9t9apQzkwdpYByC');
        $user->setNickName('User_Admin');
        $user->setIsConfirmed(true);
        self::$entityManager->persist($user);
        self::$entityManager->flush();
        return $user;
    }

    protected static function createHost(): Host
    {
        $host = new Host();
        $host->setHost('http://test' . self::getUniq() . '.te');
        self::$entityManager->persist($host);
        self::$entityManager->flush();
        return $host;
    }

    protected static function createError(): Error
    {
        $entity = new Error();
        $entity->setMessage('error message');
        $entity->setAddData([]);
        self::$entityManager->persist($entity);
        self::$entityManager->flush();
        return $entity;
    }

    protected static function createProduct(Host $host): Product
    {
        $product = new Product();
        $product->setHost($host);
        $product->setUrl('http://test' . self::getUniq() . '.te');
        $product->setCurrentPrice(100);
        self::$entityManager->persist($product);
        self::$entityManager->flush();
        return $product;
    }

    protected static function createMessage(User $user): Message
    {
        $message = new Message();
        $message->setMessage('message');
        $message->setTitle('title' . self::getUniq());
        $message->setUser($user);
        self::$entityManager->persist($message);
        self::$entityManager->flush();
        return $message;
    }

    protected static function createWatcher(Product $product, User $user): Watcher
    {
        $watcher = new Watcher();
        $watcher->setUser($user);
        $watcher->setProduct($product);
        $watcher->setTitle('title' . self::getUniq());
        $watcher->setStartPrice(100);
        $watcher->setPercent(10);
        self::$entityManager->persist($watcher);
        self::$entityManager->flush();
        return $watcher;
    }

    private static function clearTables(): void
    {
        $sql = 'DELETE FROM watcher; DELETE FROM message; DELETE FROM price_tracker; DELETE FROM product; 
DELETE FROM `host`; DELETE FROM `user`; DELETE FROM `error`; DELETE FROM `file`;';
        $stmt = static::$entityManager->getConnection()->prepare($sql);
        $stmt->execute();
    }

    private static function getUniq(): int
    {
        self::$uniq++;
        return self::$uniq;
    }

    public function assertError(Crawler $crawler)
    {
        $this->assertEquals(1, $crawler->filter('.alert-error')->count());
    }

}