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



use App\Entity\Error;
use App\Entity\Message;
use App\Repository\ErrorRepository;
use App\Repository\MessageRepository;
use Billizzard\GridView\GridView;
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
class ErrorController extends MainController
{
    public function listAction(Request $request, ErrorRepository $er)
    {
//        $em = $this->getDoctrine()->getManager();
//        $message = new Message();
//        $message->setMessage('m.changed_price');
//        $message->setAddData(['watcher_id' => 1, 'watcher_title' => 'werwerwe']);
//        $message->setUser($this->getUser());
//        $message->setTitle('m.changed_price_short');
//        $message->setType(Message::TYPE_CHANGE_PRICE);
//        $em->persist($message);
//        $em->flush();
//        die('ddd');

        $qb = $er->findByRequestQueryBuilder($request, $this->getUser());
        $grid = new GridView($request, $qb, ['perPage' => 10, 'template' => '2']);
        $grid->addColumn('message', [])
            ->addColumn('type', [])
            ->addColumn('createdAt', $this->getCreatedAdOption())
            ->addActionColumn('Actions', [
                'buttons' => ['view'],
                'colOptions' => [
                    'style' => 'width: 48px'
                ]
            ]);

        $messages = $grid->getGridData();

        return $this->render('errors/list.html.twig', [
            'messages' => $messages,
            'activeMenu' => 'errors-list',
        ]);
    }

    public function deleteAction(Request $request, ErrorRepository $er)
    {
        $ids = explode(',', $request->get('id'));
        if (!$this->isCsrfTokenValid('delete_message', $request->get('_token'))) {
            throw new NotFoundHttpException();
        }
        $redirect_route = ['name' => 'error_list', 'params' => []];

        $er->deleteById($ids);

        $this->addFlash('success', $this->translator->trans('m.data_deleted'));
        return $this->redirectToRoute($redirect_route['name'], $redirect_route['params']);
    }


    public function viewAction(Request $request, ErrorRepository $repository)
    {
        /** @var Message $message */
        $message = $repository->findOneBy(['id' => $request->get('id')]);
        if ($message) {
            return $this->render('errors/view.html.twig', [
                'message' => $message,
                'activeMenu' => 'errors-list',
                'translatedMessage' => $message->getMessage()
            ]);
        }
        throw new NotFoundHttpException();
    }

    private function getCreatedAdOption()
    {
        return [
            'raw' => true,
            'callback' => function($model) {
                return date('d-m-Y', $model['createdAt']);
            },
            'colOptions' => [
                'style' => 'width: 100px'
            ]
        ];
    }
}
