<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Admin;

use App\Entity\PriceTracker;
use App\Entity\Product;
use App\Entity\Watcher;
use App\Form\AddProductType;
use App\Repository\PriceTrackerRepository;
use App\Repository\ProductRepository;
use App\Repository\WatcherRepository;
use App\Service\HVFGridView;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller used to manage blog contents in the backend.
 *
 * Please note that the application backend is developed manually for learning
 * purposes. However, in your real Symfony application you should use any of the
 * existing bundles that let you generate ready-to-use backends without effort.
 *
 * See http://knpbundles.com/keyword/admin
 * *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class TrackerController extends AbstractController
{

    public function listAction(Request $request, ProductRepository $pr)
    {
        $qb = $pr->findByRequestQueryBuilder($request);
        $grid = new HVFGridView($request, $qb, ['perPage' => 5]);
        $products = $grid->getGridData();


        return $this->render('trackers/list.html.twig', [
            'products' => $products,
        ]);
    }

    public function addAction(Request $request)
    {
        $model = new Product();
        $form = $this->createForm(AddProductType::class, $model);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            /** @var ProductRepository $repository */
            $repository = $this->getDoctrine()->getRepository(Product::class);
            $entityManager = $this->getDoctrine()->getManager();
            $product = $repository->findOneBy(['url' => $model->getUrl()]);

            if (!$product) {
                $entityManager->persist($model);
                $entityManager->flush();
                $product = $model;
            }

            /** @var PriceTrackerRepository $repository */
            $repository = $this->getDoctrine()->getRepository(PriceTracker::class);
            $priceTracker = $repository->findOneBy(['productId' => $product->getId()]);

            if (!$priceTracker) {
                $priceTracker = new PriceTracker();
                $priceTracker->setProductId($product->getId());
                $priceTracker->setPrice($form['price']->getData());
                $priceTracker->setDate(time());
                $entityManager->persist($priceTracker);
                $entityManager->flush();
            }

            /** @var WatcherRepository $repository */
            $repository = $this->getDoctrine()->getRepository(Watcher::class);
            $watcher = $repository->findOneBy(['productId' => $product->getId(), 'userId' => $this->getUser()->getId()]);

            if (!$watcher) {
                $watcher = new Watcher();
                $watcher->setTitle($form['title']->getData());
                $watcher->setProductId($product->getId());
                $watcher->setUserId($this->getUser()->getId());
                $watcher->setStartPrice($form['price']->getData());
                $watcher->setPercent($form['percent']->getData());
                $entityManager->persist($watcher);
                $entityManager->flush();
                $this->addFlash('success', 'v.success.added');
            } else {
                $this->addFlash('info', 'v.priceTracker.taken');
            }

            return $this->redirectToRoute('tracker_list');
        }

        foreach ($form->getErrors(true, true) as $error) {
            $this->addFlash('error', $error->getMessage());
            break;
        }

        return $this->render('trackers/add.html.twig', ['form' => $form->createView()]);
    }



}
