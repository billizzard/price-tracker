<?php

namespace App\Command;


use App\Entity\Error;
use App\Entity\Host;
use App\Entity\Message;
use App\Entity\PriceTracker;
use App\Entity\Product;
use App\Entity\Watcher;
use App\HVF\Helper\ProfilerLogger;
use App\HVF\PriceChecker\PriceParsers\PriceParser;
use App\Repository\ErrorRepository;
use App\Repository\HostRepository;
use App\Repository\ProductRepository;
use App\Repository\WatcherRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Translation\TranslatorInterface;

class CronCheckerPriceCommand extends ContainerAwareCommand
{
    const VALID_ENCODING = ['UTF-8'];

    /** @var EntityManagerInterface  */
    private $em;
    /** @var LoggerInterface */
    private $logger;
    private $loggerProfiling;
    /** @var TranslatorInterface */
    private $translator;
    /** @var HostRepository */
    private $hostRepository;

    private $doctrine;

    private $profiler;


    public function __construct(EntityManagerInterface $em, LoggerInterface $logger, TranslatorInterface $translator, HostRepository $hr, $name = null)
    {
        parent::__construct($name);
        $this->logger = $logger;
        $this->translator = $translator;
        $this->hostRepository = $hr;
        $this->em = $em;
    }

    protected function configure()
    {
        $this
            ->setName('cron:checker:price')
            ->setDescription('Check product price on sites')
            ->addOption(
                'price',
                null,
                InputOption::VALUE_OPTIONAL,
                'Specify a price (for testing)'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->profiler = new ProfilerLogger($this->getContainer()->get('monolog.logger.profiling'));

        $this->doctrine = $this->getContainer()->get('doctrine');
        $hosts = $this->hostRepository->findAll();
        if ($hosts) {
            /** @var Host $host */
            foreach ($hosts as $host) {
                /** @var PriceParser $parser */
                if ($parser = $host->getParser()) {
                    /** @var ProductRepository $repository */
                    $repository = $this->doctrine->getRepository(Product::class);

                    $this->profiler->log('Start foreach for host: ' . $host->getId() . '; ---------------------');
                    /** @var Product $product */
                    foreach ($repository->findTrackedByHost($host) as $product) {
                        if (!($price = $this->getPrice($input))) {
                            $this->profiler->log('Start parse price for product: ' . $product->getId());
                            $price = $parser->getPriceByUrl($product->getUrl());
                            $this->profiler->log('Stop parse price for product: ' . $product->getId());
                        }

                        if ($price) {
                            $this->changeCurrentPrice($product, $price);
                            $this->changeWatcherStatus($product);
                        } else {
                            $product->setStatus(Product::STATUS_ERROR_TRACKED);
                            $this->em->persist($product);
                            $this->em->flush();
                            $this->addError('Cannot find price for product (' . $product->getId() . ') url: <a href="' . $product->getUrl() . '" >link</a>');
                            $this->logger->critical('Cannot find price', ['product_id' => $product->getId()]);
                        }
                    }
                    $this->profiler->log('Stop foreach for host: ' . $host->getId());
                } else {
                    $this->addError('Not found parser for host (' . $host->getId() . '): ' . $host->getHost());
                    $this->logger->critical('Not found parser for host', ['host_id' => $host->getId()]);
                }
            }
        }
    }

    /**
     * Изменяет текущую цену если нужно и добавляет price_tracker если нужно
     * @param Product $product
     * @param $price
     */
    private function changeCurrentPrice(Product $product, $price)
    {
        $product->setLastTrackedDate(time());
        // Если текущая цена отличается от новой цены
        if (!$product->getCurrentPrice() || $product->getCurrentPrice() != $price) {
            $priceTracker = new PriceTracker();
            $priceTracker->setPrice($price);
            $priceTracker->setProduct($product);
            $this->em->persist($priceTracker);
            $product->setCurrentPrice($price);
            $product->setStatus(Product::STATUS_TRACKED);
            $product->setChangedPrice(true);
        }

        $this->em->persist($product);
        $this->em->flush();
    }

    /**
     * Изменяет статус нблюдателя и оповещает, если нужно о приемлемой цене для пользователя
     * @param Product $product
     */
    private function changeWatcherStatus(Product $product)
    {
        /** @var WatcherRepository $repository */
        $repository = $this->doctrine->getRepository(Watcher::class);
        $watchers = $repository->findTrackedByProductId($product->getId());

        if ($watchers) {
            $this->profiler->log('Start foreach for product: ' . $product->getId());
            /** @var Watcher $watcher */
            foreach ($watchers as $watcher) {
                $user = $watcher->getUser();

                if ($product->getChangedPrice()) {
                    // Если это цена, которую ждал пользователь
                    if ($watcher->getEndPrice() >= $product->getCurrentPrice()) {
                        // Устанавливаем статус успешно, чтобы больше этого наблюдателя не трогать
                        $watcher->setStatus(Watcher::STATUS_SUCCESS);
                        $watcher->setSuccessDate(time());
                        $messageSuccess = new Message();
                        $messageSuccess->setMessage('m.success_price');
                        $messageSuccess->setAddData(['watcher_id' => $watcher->getId(), 'watcher_title' => $watcher->getTitle()]);
                        $messageSuccess->setUser($user);
                        $messageSuccess->setTitle('m.success_price_short');
                        $messageSuccess->setType(Message::TYPE_SALE_SUCCESS);
                        $this->em->persist($messageSuccess);
                        //$this->mailer->sendSaleMail($user->getEmail());
                    } else {
                        $message = new Message();
                        $message->setMessage('m.changed_price');
                        $message->setAddData(['watcher_id' => $watcher->getId(), 'watcher_title' => $watcher->getTitle()]);
                        $message->setUser($user);
                        $message->setTitle('m.changed_price_short');
                        $message->setType(Message::TYPE_CHANGE_PRICE);
                        $this->em->persist($message);
                    }
                }
                // Если это новый ватчер
                if ($watcher->getStatus() == Watcher::STATUS_NEW) {
                    $watcher->setStatus(Watcher::STATUS_PRICE_CONFIRMED);
//                    // И цена продукта отличается то посылаем ему письмо
//                    if ($product->getCurrentPrice() != $watcher->getStartPrice()) {
//                        $message = new Message();
//                        $message->setMessage('m.watcherPrice.wrong');
//                        $message->setUser($user);
//                        $this->entityManager->persist($message);
//                    }
                }



            }
            $this->profiler->log('Stop foreach for product: ' . $product->getId());
        } else { // значит за товаром уже никто не наблюдает
            $this->logger->info('Product not tracker more', ['product_id' => $product->getId()]);
            $product->setStatus(Product::STATUS_NOT_TRACKED);
            $this->em->persist($product);
        }
        $this->em->flush();
    }

    /**
     * Get price from console
     * @param InputInterface $input
     * @return mixed
     */
    private function getPrice(InputInterface $input)
    {
        $price = $input->getOption('price') ? $input->getOption('price') : 0;
        return $price;
    }

    private function addError($message, $addData = [])
    {
        $error = new Error();
        $error->setMessage($message);
        $error->setAddData($addData);
        $error->setCreatedAt(time());
        $this->em->persist($error);
        $this->em->flush();
    }
}