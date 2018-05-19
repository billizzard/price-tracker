<?php

namespace App\Repository;

use App\Entity\PriceTracker;
use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class PriceTrackerRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PriceTracker::class);
    }

    public function getGraphDataForProduct(Product $product, $dateStart = 0, $dateStop = 0)
    {
        $jsonPrice = [];
        $dateStop = $dateStop && $dateStop <= $product->getLastTrackedDate() ? $dateStop : $product->getLastTrackedDate();
        $dateStart = $dateStart ? $dateStart : $dateStop - (60*60*24*30);
        $trackers = $this->getTrackersBetweenDate($product->getId(), $dateStart, $dateStop);
        $jsonPrice['startDate'] = date('d.m.Y', $dateStart);
        $jsonPrice['stopDate'] = date('d.m.Y', $dateStop);
        $beforeTracker = $this->getTrackerBeforeDate($product->getId(), $dateStart);
        $beforePrice = 0;
        if ($beforeTracker) {
            $beforePrice = $beforeTracker->getPrice();
        }

        if ($trackers) {
            $i = 0;
            foreach ($trackers as $key => $tracker) {
                $trackerDay = date('d.m', $tracker->getDate());
                while ($dateStart <= $dateStop) {
                    $i++;
                    $dayStart = date('d.m', $dateStart);
                    if ($trackerDay != $dayStart) {
                        if ($beforePrice) {
                            $jsonPrice['data'][] = [$i, $beforePrice];
                            $jsonPrice['labels'][] = [$i, date('d.m', $dateStart)];
                        }
                        $dateStart = $dateStart + 60*60*24;
                    } else {
                        $beforePrice = $tracker->getPrice();
                        $jsonPrice['data'][] = [$i, $tracker->getPrice()];
                        $jsonPrice['labels'][] = [$i, date('d.m', $tracker->getDate())];
                        $dateStart = $dateStart + 60*60*24;
                        break;
                    }
                }
            }

            while ($dateStart <= $dateStop) {
                $i++;
                $jsonPrice['data'][] = [$i, $beforePrice];
                $jsonPrice['labels'][] = [$i, date('d.m', $dateStart)];
                $dateStart = $dateStart + 60 * 60 * 24;
            }
        }
        
        return $jsonPrice;
    }

    public function getTrackersBetweenDate(int $productId, $dateStart, $dateStop)
    {
        return $this->createQueryBuilder('pt')->where('pt.product = :product AND pt.date >= :dateStart AND pt.date <= :dateStop')
            ->orderBy('pt.date', 'ASC')
            ->setParameter('product', $productId)
            ->setParameter('dateStart', $dateStart)
            ->setParameter('dateStop', $dateStop)
            ->getQuery()
            ->getResult();
    }

    public function getTrackerBeforeDate(int $productId, $date)
    {
        return $this->createQueryBuilder('pt')->where('pt.product = :product AND pt.date <= :date')
            ->setParameter('product', $productId)
            ->setParameter('date', $date)
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }
}
