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

use App\Entity\Host;
use App\Entity\Watcher;
use App\Form\AddHostType;
use App\Form\EditHostType;
use App\Repository\FileRepository;
use App\Repository\HostRepository;
use App\Repository\PriceTrackerRepository;
use App\Repository\WatcherRepository;

use App\Service\FileUploader;
use Billizzard\GridView\GridView;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
            $entityManager = $this->getDoctrine()->getManager();

            /** @var UploadedFile $logoFile */
            $logoFile = $model->getLogoFile();
            $entityManager->persist($model);
            $entityManager->flush();

            if ($logoFile) {
                $file = $fileUploader->upload($model, $logoFile, $this->getUser());

                $entityManager->persist($file);
                $entityManager->flush();
            }

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

    public function editAction(Request $request, HostRepository $repository, FileRepository $fileRepository, FileUploader $fileUploader)
    {
        /** @var Host $model */
        $model = $repository->getOneById($request->get('id'));

        if ($model) {
            /** @var \App\Entity\File $oldFile */
            $oldFile = $fileRepository->getFileByEntity($model, $this->getUser());
            $oldFile = $oldFile && file_exists($oldFile->getFullSrc()) ? $oldFile : null;

            if ($oldFile) {
                $model->setLogoFile(new File($oldFile->getFullSrc()));
            }

            $form = $this->createForm(EditHostType::class, $model, ['attr' => ['novalidate' => 'novalidate']]);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                /** @var UploadedFile $logoFile */
                $logoFile = $model->getLogoFile();

                $entityManager = $this->getDoctrine()->getManager();

                if ($logoFile) {

                    if ($oldFile) {
                        $oldFile->delete();
                        $entityManager->persist($oldFile);
                    }

                    $newFile = $fileUploader->upload($model, $logoFile, $this->getUser());
                    $entityManager->persist($newFile);
                } else {
                    if ($deleted = $form->get('deleted')->getData()) {
                        $deleted = explode(',', $deleted);
                        foreach ($deleted as $id) {
                            if ($id == $oldFile->getId()) {
                                $oldFile->delete();
                                $entityManager->persist($oldFile);
                            }
                        }
                    }
                }

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
                'files' => [$oldFile],
                'activeMenu' => 'host-add',
            ]);
        }
        
        throw new NotFoundHttpException();
    }

    public function deleteAction(Request $request, HostRepository $repository, FileRepository $fileRepository)
    {
        /** @var Host $model */
        $model = $repository->getOneById($request->get('id'));

        if ($model) {
            $entityManager = $this->getDoctrine()->getManager();
            $model->delete();
            /** @var \App\Entity\File $oldFile */
            $oldFile = $fileRepository->getFileByEntity($model, $this->getUser());
            if ($oldFile) {
                $oldFile->delete();
                $entityManager->persist($oldFile);
            }
            $entityManager->persist($model);
            $entityManager->flush();
            return $this->redirectToRoute('host_list');
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
            'buttons' => ['edit', 'delete'],
            'label' => $this->translator->trans('l.actions'),
            'colOptions' => [
                'style' => 'width:70px; text-align:center;'
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
