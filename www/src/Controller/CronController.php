<?php

namespace App\Controller;

use App\Entity\Host;
use App\Entity\Message;
use App\Entity\PriceTracker;
use App\Entity\Product;
use App\Entity\Watcher;
use App\HVF\PriceChecker\HVFPriceChecker;
use App\Repository\HostRepository;
use App\Repository\PriceTrackerRepository;
use App\Repository\ProductRepository;
use App\Repository\WatcherRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class CronController extends Controller
{
    private $entityManager;

    public function priceCheckerAction(HostRepository $hr)
    {
        $this->entityManager = $this->getDoctrine()->getManager();
        $hosts = $hr->findAll();
        if ($hosts) {
            /** @var Host $host */
            foreach ($hosts as $host) {
                if ($parser = $host->getParser()) {
                    $priceChecker = new HVFPriceChecker($parser);
                    foreach ($host->getProducts() as $product) {
                        if ($product->getStatus() == Product::STATUS_TRACKED) {
                            $price = $priceChecker->getPriceByUrl($product->getUrl());
                            if ($price) {
                                $this->changeCurrentPrice($product, $price);
                                $this->changeWatcherStatus($product);
                            }
                        }
                    }
                }
            }
        }
        die('end');
    }

    private function changeCurrentPrice(Product $product, $price)
    {
        if (!$product->getCurrentPrice() || $product->getCurrentPrice() != $price) {
            $priceTracker = new PriceTracker();
            $priceTracker->setPrice($price);
            $priceTracker->setProduct($product);
            $this->entityManager->persist($priceTracker);
            $this->entityManager->flush();

            $product->setCurrentPrice($price);
            $this->entityManager->persist($product);
            $this->entityManager->flush();
        }
    }

    private function changeWatcherStatus(Product $product)
    {
        /** @var WatcherRepository $repository */
        $repository = $this->getDoctrine()->getRepository(Watcher::class);
        $watchers = $repository->findAll(['product_id' => $product->getId(), 'status' => Watcher::STATUS_NEW]);
        if ($watchers) {
            /** @var Watcher $watcher */
            foreach ($watchers as $watcher) {
                $user = $watcher->getUser();
                $watcher->setStatus(Watcher::STATUS_PRICE_CONFIRMED);
                if ($product->getCurrentPrice() != $watcher->getStartPrice()) {
                    $message = new Message();
                    $message->setMessage('m.watcherPrice.wrong');
                    $message->setUser($user);
                    $this->entityManager->persist($message);
                    $this->entityManager->flush();
                }

                if ($watcher->getEndPrice() >= $product->getCurrentPrice()) {
                    $message = new Message();
                    $message->setMessage('');
                    $message->setUser($user);
                    //$message->setType(Message::TYPE_SUCCESS);
                    $this->entityManager->persist($message);
                    $this->entityManager->flush();
                    die('sendMessage');
                }
            }
        }
    }

}