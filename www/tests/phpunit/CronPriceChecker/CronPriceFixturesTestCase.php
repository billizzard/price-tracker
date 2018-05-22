<?php
namespace App\Tests\phpunit\CronPriceChecker;

use App\Command\CronCheckerPriceCommand;
use App\Entity\Host;
use App\Entity\Product;
use App\Entity\User;
use App\Entity\Watcher;
use App\Kernel;
use App\Tests\phpunit\BaseTestCase;
use Doctrine\ORM\EntityManager;
use PharIo\Manifest\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\StringInput;


class CronPriceFixturesTestCase extends BaseTestCase
{
    public static $entities = ['user', 'product', 'host', 'watcher'];

    protected static $rootDir;
    
    protected static function loadFixtures()
    {
        $user = self::createTestUser();

//        $application = new Application();
//        $application->add(new CronCheckerPriceCommand());
//
//        $command = $application->find('demo:greet');
//        $commandTester = new CommandTester($command);
//        $commandTester->execute(array('command' => $command->getName()));



//        new Application(self::$kernel);
//        $application = new \AppKernel(self::$kernel);
//        $application->add(new YourCommand());
//
//        $command = $application->find('your:command:name');
//        $commandTester = new CommandTester($command);
//        $commandTester->execute(array('command' => $command->getName()));


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

        self::$rootDir = self::$kernel->getRootDir();

        self::$entityManager->persist($watcher);
        self::$entityManager->flush();

    }

    protected function runCommandWithPrice($price)
    {
        passthru("php " . self::$rootDir ."/../bin/console cron:checker:price --price=" . $price);
    }
}