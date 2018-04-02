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

class CronController extends Controller
{
    private $entityManager;
    private $logger;
    private $mailer;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
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
                    // Исправить88: вместо всех продуктов вынимать только со статусом нужным уже
                    foreach ($host->getProducts() as $product) {
                        if ($product->getStatus() == Product::STATUS_TRACKED) {

                            if ($env == 'test') {
                                $price = $request->get('price');
                            } else {
                                $price = $parser->getPriceByUrl($product->getUrl());
                            }
                            file_put_contents('aaaaaaaaaaaa.txt', $price);
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
        // Если текущая цена отличается от новой цены
        if (!$product->getCurrentPrice() || $product->getCurrentPrice() != $price) {
            $priceTracker = new PriceTracker();
            $priceTracker->setPrice($price);
            $priceTracker->setProduct($product);
            $this->entityManager->persist($priceTracker);
            $product->setCurrentPrice($price);
            $this->entityManager->persist($product);
            $this->entityManager->flush();
        }
    }

    /**
     * Изменяет стату нблюдателя и оповещает, если нужно о приемлемой цене для пользователя
     * @param Product $product
     */
    private function changeWatcherStatus(Product $product)
    {
        /** @var WatcherRepository $repository */
        $repository = $this->getDoctrine()->getRepository(Watcher::class);
        //$watchers = $repository->findAll(['product_id' => $product->getId(), 'status' => [Watcher::STATUS_NEW]]);
        $watchers = $repository->findActive();

        if ($watchers) {
            /** @var Watcher $watcher */
            foreach ($watchers as $watcher) {
                $user = $watcher->getUser();
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

                // Если это цена, которую ждал пользователь
                if ($watcher->getEndPrice() >= $product->getCurrentPrice()) {
                    // Устанавливаем статус успешно, чтобы больше этого наблюдателя не трогать
                    $watcher->setStatus(Watcher::STATUS_SUCCESS);
                    $message = new Message();
                    $message->setMessage('qqqqqqwwwweeee');
                    $message->setUser($user);
                    $message->setType(Message::TYPE_SUCCESS);
                    $this->entityManager->persist($message);
                    //$this->mailer->sendSaleMail($user->getEmail());
                }
                $this->entityManager->flush();
            }
        }
    }

}
