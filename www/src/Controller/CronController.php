<?php

namespace App\Controller;

use App\Entity\Host;
use App\Entity\Message;
use App\Entity\PriceTracker;
use App\Entity\Product;
use App\Entity\Watcher;
use App\HVF\Mailer\HVFMailer;
use App\HVF\PriceChecker\PriceParsers\PriceParser;
use App\Repository\HostRepository;
use App\Repository\ProductRepository;
use App\Repository\WatcherRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

class CronController extends Controller
{
    private $entityManager;
    private $logger;
    private $mailer;
    private $translator;

    public function __construct(LoggerInterface $logger, TranslatorInterface $translator)
    {
        $this->logger = $logger;
        $this->translator = $translator;
        $this->mailer = new HVFMailer();
    }

    public function priceCheckerAction(HostRepository $hr, Request $request)
    {
        $env = $this->container->get( 'kernel' )->getEnvironment();
        $this->entityManager = $this->getDoctrine()->getManager();
        $hosts = $hr->findAll();
        if ($hosts) {
            /** @var Host $host */
            foreach ($hosts as $host) {
                /** @var PriceParser $parser */
                if ($parser = $host->getParser()) {
                    /** @var ProductRepository $repository */
                    $repository = $this->getDoctrine()->getRepository(Product::class);
                    foreach ($repository->findTracked() as $product) {
                        if ($env == 'test' && $request->get('price')) {
                            $price = $request->get('price');
                        } else {
                            $price = $parser->getPriceByUrl($product->getUrl());
                        }

                        if ($price) {
                            $this->changeCurrentPrice($product, $price);
                            $this->changeWatcherStatus($product);
                        } else {
                            $product->setStatus(Product::STATUS_ERROR_TRACKED);
                            $this->entityManager->persist($product);
                            $this->entityManager->flush();
                            $this->logger->error('Cannot find price', ['product_id' => $product->getId()]);
                        }
                    }
                } else {
                    $this->logger->error('Not found parser for host', ['host_id' => $host->getId()]);
                }
            }
        }

        return new Response(
            '<html><body>Cron end</body></html>'
        );
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
            $this->entityManager->persist($priceTracker);
            $product->setCurrentPrice($price);
            $product->setStatus(Product::STATUS_TRACKED);
            $product->setChangedPrice(true);

        }
        $this->entityManager->persist($product);
        $this->entityManager->flush();
    }

    /**
     * Изменяет статус нблюдателя и оповещает, если нужно о приемлемой цене для пользователя
     * @param Product $product
     */
    private function changeWatcherStatus(Product $product)
    {
        /** @var WatcherRepository $repository */
        $repository = $this->getDoctrine()->getRepository(Watcher::class);
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
                        $this->entityManager->persist($messageSuccess);
                        //$this->mailer->sendSaleMail($user->getEmail());
                    } else {
                        $message = new Message();
                        $message->setMessage('m.changed_price');
                        $message->setAddData(['watcher_id' => $watcher->getId(), 'watcher_title' => $watcher->getTitle()]);
                        $message->setUser($user);
                        $message->setTitle('m.changed_price_short');
                        $message->setType(Message::TYPE_CHANGE_PRICE);
                        $this->entityManager->persist($message);
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
            $this->entityManager->persist($product);
        }
        $this->entityManager->flush();
    }

}
