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

use App\Entity\Domain;
use App\Entity\Host;
use App\Entity\PriceTracker;
use App\Entity\Product;
use App\Entity\Watcher;
use App\Form\AddProductType;
use App\Form\AddWatcherType;
use App\Form\EditWatcherType;
use App\Repository\PriceTrackerRepository;
use App\Repository\ProductRepository;
use App\Repository\WatcherRepository;
use App\Service\HVFGridView;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Translation\TranslatorInterface;

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
class TrackerController extends MainController
{
    private $translator;
    private $logger;

    public function __construct(TranslatorInterface $translator, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->translator = $translator;
    }

    public function listAction(Request $request, WatcherRepository $wr)
    {
        //$logger->error('Cannot find price', ['product_id' => 1]);
        $qb = $wr->findByRequestQueryBuilder($request, $this->getUser());
        $grid = new HVFGridView($request, $qb, ['perPage' => 1]);

        $grid->addColumn('id', [
            'sort' => false,
        ])->addColumn('title', [
            'sort' => true,
            'label' => 'Tiittllee'
        ])->addColumn('status', [
            'label' => 'Статус',
            'sort' => true,
            'raw' => true,
            'callback' => function($model) {
                $result = '';
                if ($model['status'] == Product::STATUS_NOT_TRACKED) {
                    $result = 'Not tracked';
                } else if ($model['status'] == Product::STATUS_TRACKED) {
                    $result = "<a href='google.by'>Tracked</a>";
                } else if ($model['status'] == Product::STATUS_ERROR_TRACKED) {
                    $result = 'Error';
                }
                return $result;
            }
        ])->addActionColumn('Actions', [
            'view', 'edit', 'delete'
        ]);

        $products = $grid->getGridData();

        return $this->render('trackers/list.html.twig', [
            'products' => $products,
            'activeMenu' => 'trackers-list'
        ]);
    }

    public function addAction(Request $request)
    {
        $watcher = new Watcher();
        $form = $this->createForm(AddWatcherType::class, $watcher, ['attr' => ['novalidate' => 'novalidate']]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            /** @var PriceTrackerRepository $repository */
            $repository = $this->getDoctrine()->getRepository(Host::class);
            $url = parse_url($form['url']->getData());
            $host = $repository->findOneBy(['host' => $url['host']]);

            if (!$host) {
                $host = new Host();
                $host->setHost($url['host']);
                $entityManager->persist($host);
                $entityManager->flush();
            }

            /** @var ProductRepository $repository */
            $repository = $this->getDoctrine()->getRepository(Product::class);
            $product = $repository->findOneBy(['url' => $form['url']->getData()]);

            if (!$product) {
                $product = new Product();
                $product->setHost($host);
                $product->setUrl($form['url']->getData());
            } else { // такой товар есть
                if ($product->getCurrentPrice() && $product->getCurrentPrice() != $watcher->getStartPrice()) {
                    // logging:  пользователь с таким то ид указал неверную цену, т.е. либо он ошибся, либо скрипт неверно распознает цену
                }
            }

            if ($host->getParser()) {
                $product->setStatus(Product::STATUS_TRACKED);
            }
            $entityManager->persist($product);
            $entityManager->flush();

            /** @var WatcherRepository $repository */
            $repository = $this->getDoctrine()->getRepository(Watcher::class);
            $watcherExisted = $repository->findOneBy(['product' => $product->getId(), 'user' => $this->getUser()]);

            if ($watcherExisted) {
                $this->addFlash('info', 'v.priceTracker.taken');
            } else {
                $watcher->setProduct($product);
                $watcher->setUser($this->getUser());
                $entityManager->persist($watcher);
                $entityManager->flush();
                $this->addFlash('success', 'v.success.added');
            }

            return $this->redirectToRoute('tracker_list');
        }

        foreach ($form->getErrors(true, true) as $error) {
            $this->addFlash('error', $error->getMessage());
            break;
        }

        return $this->render('trackers/add.html.twig', [
            'form' => $form->createView(),
            'activeMenu' => 'trackers-add',
        ]);
    }

    public function editAction(Request $request, WatcherRepository $watcherRepository)
    {
        $watcher = $watcherRepository->findOneBy(['product' => $request->get('id'), 'user' => $this->getUser()->getId()]);
        if ($watcher) {
            $this->denyAccessUnlessGranted('edit', $watcher, 'Access denied.');
            $form = $this->createForm(EditWatcherType::class, $watcher, ['attr' => ['novalidate' => 'novalidate']]);
            $form->get('url')->setData($watcher->getProduct()->getUrl());
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->getDoctrine()->getManager()->flush();
                $this->addFlash('success', 'v.success.edited');
                return $this->redirectToRoute('tracker_list');
            }

            $form->get('url')->setData($watcher->getProduct()->getUrl());

            return $this->render('trackers/edit.html.twig', [
                'form' => $form->createView(),
                'activeMenu' => 'trackers-add',
            ]);

        }
        throw new NotFoundHttpException();
    }

    public function viewAction(Request $request, WatcherRepository $watcherRepository)
    {
        $watcher = $watcherRepository->findOneBy(['product' => $request->get('id'), 'user' => $this->getUser()->getId()]);
        if ($watcher) {
            $product = $watcher->getProduct();
            $this->denyAccessUnlessGranted('view', $watcher, 'Access denied.');
            $jsonPrice = [];
            $addData = [];

            // optimization: вынимает с товаром, хотя тут это не нужно
            foreach ($product->getPriceTrackers() as $key => $priceTracker) {
                $jsonPrice['data'][] = [$key, $priceTracker->getPrice()];
                $jsonPrice['labels'][] = [$key, date('d.m', $priceTracker->getDate())];
            }

            if ($watcher->getStatus()) {
                $addData['status'] = [
                    'class' => 'green',
                    'label' => $this->translator->trans('l.completed')
                ];
            } else {
                $addData['status'] = [
                    'class' => 'yellow',
                    'label' => $this->translator->trans('l.tracked')
                ];
            }

            return $this->render('trackers/view.html.twig', [
                'product' => $product,
                'watcher' => $watcher,
                'jsonPrice' => json_encode($jsonPrice),
                'addData' => $addData
            ]);

        }
        throw new NotFoundHttpException();
    }





}
