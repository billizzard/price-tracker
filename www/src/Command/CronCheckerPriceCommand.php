<?php

namespace App\Command;


use App\Entity\Host;
use App\Entity\Message;
use App\Entity\PriceTracker;
use App\Entity\Product;
use App\Entity\Watcher;
use App\HVF\PriceChecker\PriceParsers\PriceParser;
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
    /** @var TranslatorInterface */
    private $translator;
    /** @var HostRepository */
    private $hostRepository;

    private $doctrine;


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
        $this->doctrine = $this->getContainer()->get('doctrine');
        $hosts = $this->hostRepository->findAll();
        if ($hosts) {
            /** @var Host $host */
            foreach ($hosts as $host) {
                /** @var PriceParser $parser */
                if ($parser = $host->getParser()) {
                    /** @var ProductRepository $repository */
                    $repository = $this->doctrine->getRepository(Product::class);

                    foreach ($repository->findTracked() as $product) {
                        if (!($price = $this->getPrice($input))) {
                            $price = $parser->getPriceByUrl($product->getUrl());
                        }

                        if ($price) {
                            $this->changeCurrentPrice($product, $price);
                            $this->changeWatcherStatus($product);
                        } else {
                            $product->setStatus(Product::STATUS_ERROR_TRACKED);
                            $this->em->persist($product);
                            $this->em->flush();
                            $this->logger->error('Cannot find price', ['product_id' => $product->getId()]);
                        }
                    }
                } else {
                    $this->logger->error('Not found parser for host', ['host_id' => $host->getId()]);
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
        } else { // значит за товаром уже никто не наблюдает
            $this->logger->info('Product not tracker more', ['product_id' => $product->getId()]);
            $product->setStatus(Product::STATUS_NOT_TRACKED);
            $this->em->persist($product);
        }
        $this->em->flush();
    }


    /**
     * If product exists - changes it. If not exist - creates it
     * @param $data - array with product data
     */
    private function changeProductData($data)
    {
        $repository = $this->em->getRepository(ProductData::class);
        $product = $repository->findOneBy(array('code' => $data[0]));

        if (!$product) {
            $product = new ProductData();
            $product->setCode($data[0]);
        }

        $product->setName($data[1]);
        $product->setDesc($data[2]);
        $product->setStockLevel((float)$data[3]);
        $product->setPrice((float)$data[4]);
        if (isset($data[5]) && $data[5] == 'yes') {
            $dt = new \DateTime();
            $dt->format('Y-m-d H:i:s');
            $product->setDiscontinued($dt);
        }
        $this->em->persist($product);
    }

    /**
     * Get price from console
     * @param InputInterface $input
     * @return mixed
     */
    private function getPrice(InputInterface $input)
    {
        $price = $input->getOption('price') ? $input->getOptions('price') : 0;
        return $price;
    }
}