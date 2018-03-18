<?php

namespace App\Controller;

use App\Entity\Host;
use App\Entity\Product;
use App\HVF\PriceChecker\HVFPriceChecker;
use App\Repository\HostRepository;
use App\Repository\ProductRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class CronController extends Controller
{
    public function priceCheckerAction(HostRepository $hr)
    {
        $hosts = $hr->findAll();
        if ($hosts) {
            /** @var Host $host */
            foreach ($hosts as $host) {
                if ($parser = $host->getParser()) {
                    $priceChecker = new HVFPriceChecker($parser);
                    foreach ($host->getProducts() as $product) {
                        if ($product->getStatus() == Product::STATUS_TRACKED) {
                            $price = $priceChecker->getPriceByUrl($product->getUrl());
                            echo "<pre>";
                            var_dump($price);
                            die();
                        }
                    }
                }
            }
        }
        die('end');
    }
}
