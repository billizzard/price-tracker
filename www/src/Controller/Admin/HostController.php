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
use App\Entity\Message;
use App\Entity\PriceTracker;
use App\Entity\Product;
use App\Entity\Watcher;
use App\Form\AddHostType;
use App\Form\AddProductType;
use App\Form\AddWatcherType;
use App\Form\EditHostType;
use App\Form\EditWatcherType;
use App\Repository\HostRepository;
use App\Repository\MessageRepository;
use App\Repository\PriceTrackerRepository;
use App\Repository\ProductRepository;
use App\Repository\WatcherRepository;

use App\Service\FileUploader;
use Billizzard\GridView\BillizzardGridViewBundle;
use Billizzard\GridView\GridView;
use Doctrine\ORM\EntityManager;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
class HostController extends MainController
{
    public function listAction(Request $request, HostRepository $repository)
    {
        $qb = $repository->findByRequestQueryBuilder($request, $this->getUser());
        $grid = new GridView($request, $qb, ['perPage' => 10]);

        $this->addToGridView($grid);

        $models = $grid->getGridData();

        return $this->render('hosts/list.html.twig', [
            'products' => $models,
            'activeMenu' => 'host-list'
        ]);
    }

    public function addAction(Request $request, FileUploader $fileUploader)
    {
        $model = new Host();
        $form = $this->createForm(AddHostType::class, $model, ['attr' => ['novalidate' => 'novalidate']]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $model->getLogo();
            $fileName = $fileUploader->upload($file);
            $model->setLogo($fileName);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($model);
            $entityManager->flush();

            $this->addFlash('success', 's.data_added');

            return $this->redirectToRoute('host_list');
        }

        foreach ($form->getErrors(true, true) as $error) {
            $this->addFlash('error', $error->getMessage());
            break;
        }

        return $this->render('hosts/add.html.twig', [
            'form' => $form->createView(),
            'activeMenu' => 'host-add',
        ]);
    }

    public function editAction(Request $request, HostRepository $repository, FileUploader $fileUploader)
    {
        /** @var Host $model */
        $model = $repository->getOneById($request->get('id'));
        if ($model) {
            $model->setLogoFile(
                new File($model->getSaveDir() . $model->getLogo())
            );


            $form = $this->createForm(EditHostType::class, $model, ['attr' => ['novalidate' => 'novalidate']]);
            //$form->get('url')->setData($watcher->getProduct()->getUrl());
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                /** @var UploadedFile $file */
                $file = $model->getLogoFile();
                if ($file) {
                    $fileName = $fileUploader->upload($file);
                    $model->setLogo($fileName);
                }

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($model);
                $entityManager->flush();

                $this->addFlash('success', 's.data_updated');
                return $this->redirectToRoute('host_list');
            }

            foreach ($form->getErrors(true, true) as $error) {
                $this->addFlash('error', $error->getMessage());
                break;
            }

            return $this->render('hosts/edit.html.twig', [
                'form' => $form->createView(),
                'model' => $model,
                'activeMenu' => 'trackers-add',
            ]);
        }
        
        throw new NotFoundHttpException();
    }

    public function viewAction(Request $request, WatcherRepository $watcherRepository, PriceTrackerRepository $priceTrackerRepository)
    {
        $watcher = $watcherRepository->getOneVisibleByIdAndUser($request->get('id'), $this->getUser());
        $dateStop = 0;
        if ($graphTo = $request->get('graphTo')) {
            $dateStop = strtotime($graphTo) ? strtotime($graphTo) + 86300 : 0;
        }

        if ($watcher) {
            $product = $watcher->getProduct();
            $this->denyAccessUnlessGranted('view', $watcher, 'Access denied.');
            $addData = [];

            $jsonPrice = $priceTrackerRepository->getGraphDataForProduct($product, 0, $dateStop);
            $addData['graph'] = [
                'startDate' => $jsonPrice['startDate'],
                'dateMinus30' => date('d.m.Y', strtotime($jsonPrice['stopDate']) - 2592000),
                'datePlus30' => date('d.m.Y', strtotime($jsonPrice['stopDate']) + 2592000),
                'stopDate' => $jsonPrice['stopDate']
            ];
            if ($watcher->getStatus() == Watcher::STATUS_SUCCESS) {
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

    public function deleteAction(Request $request, WatcherRepository $watcherRepository)
    {
        /** @var Watcher $watcher */
        $watcher = $watcherRepository->getOneVisibleByIdAndUser($request->get('id'), $this->getUser());

        if ($watcher) {
            $watcher->delete();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($watcher);
            $entityManager->flush();
            return $this->redirectToRoute('tracker_list');
        }

        throw new NotFoundHttpException();
    }

    private function addToGridView(GridView $grid)
    {
        $grid->addColumn('id', [
            'sort' => false,
            'colOptions' => [
                'style' => 'width:30px;'
            ]
        ])->addColumn('host', [
            'sort' => true,
            'label' => $this->translator->trans('l.host')
        ])->addActionColumn('Actions', [
            'buttons' => ['view', 'edit', 'delete'],
            'label' => $this->translator->trans('l.actions'),
            'colOptions' => [
                'style' => 'width:100px; text-align:center;'
            ]
        ]);

        $this->addFilter($grid);
    }

    private function addFilter(GridView $grid)
    {
        if ($this->getUser()->isAdmin()) {
            $grid->addFilter([
                'fields' => [
                    ['type' => 'text', 'name' => 'host', 'placeholder' => $this->translator->trans('l.host')],
                ]
            ]);
        }
    }
}
