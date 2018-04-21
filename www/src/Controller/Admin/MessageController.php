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
use App\Form\AddProductType;
use App\Form\AddWatcherType;
use App\Form\EditWatcherType;
use App\Repository\MessageRepository;
use App\Repository\PriceTrackerRepository;
use App\Repository\ProductRepository;
use App\Repository\WatcherRepository;

use Billizzard\GridView\BillizzardGridViewBundle;
use Billizzard\GridView\GridView;
use Doctrine\ORM\EntityManager;
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
class MessageController extends MainController
{
    public function listAction(Request $request, MessageRepository $mr)
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

        $qb = $mr->findByRequestQueryBuilder($request, $this->getUser());
        $grid = new GridView($request, $qb, ['perPage' => 10, 'template' => '2']);
        $grid->addColumn('message', $this->getMessageOption())
            ->addColumn('type', $this->getTypeOption())
            ->addColumn('createdAt', $this->getCreatedAdOption())
            ->addActionColumn('Actions', [
                'buttons' => ['view'],
                'colOptions' => [
                    'style' => 'width: 48px'
                ]
            ]);
        $messages = $grid->getGridData();

        return $this->render('messages/list.html.twig', [
            'messages' => $messages,
            'activeMenu' => 'messages-list'
        ]);
    }

    public function deleteAction(Request $request, MessageRepository $mr)
    {
        $ids = explode(',', $request->get('id'));
        if (!$this->isCsrfTokenValid('delete_message', $request->get('_token'))) {
            throw new NotFoundHttpException();
        }
        $redirect_route = ['name' => 'message_list', 'params' => []];
        if ($place = $request->get('place')) {
            if ($place == 'one_message') {
                /** @var Message $message */
                $message = $mr->findById($request->get('id'), $this->getUser());
                if ($prevMessage = $mr->findPrev($message)) {
                    $redirect_route = ['name' => 'message_view', 'params' => ['id' => $prevMessage->getId()]];
                } else if ($nextMessage = $mr->findNext($message)) {
                    $redirect_route = ['name' => 'message_view', 'params' => ['id' => $nextMessage->getId()]];
                }
            }
        }
        $updated = $mr->deleteById($ids, $this->getUser());
        $this->addFlash('success', $this->translator->trans('m.data_deleted'));
        return $this->redirectToRoute($redirect_route['name'], $redirect_route['params']);
    }


    public function viewAction(Request $request, MessageRepository $messageRepository)
    {
        /** @var Message $message */
        $message = $messageRepository->findById($request->get('id'), $this->getUser());
        if ($message) {
            if ($message->getStatus() == Message::STATUS_NOT_READ) {
                $message->setStatus(Message::STATUS_READ);
                $this->getDoctrine()->getManager()->flush();
            }

            $prevMessage = $messageRepository->findPrev($message);
            $nextMessage = $messageRepository->findNext($message);

            return $this->render('messages/view.html.twig', [
                'message' => $message,
                'translatedMessage' => $message->getTranslatedMessage($this->translator),
                'prevMessageId' => $prevMessage ? $prevMessage->getId() : false,
                'nextMessageId' => $nextMessage ? $nextMessage->getId() : false,
            ]);
        }
        throw new NotFoundHttpException();
    }

    private function getMessageOption()
    {
        return [
            'raw' => true,
            'callback' => function($model) {
                $result = $model[0]->getTranslatedMessage($this->translator);
                return $result;
            }
        ];
    }

    private function getTypeOption()
    {
        return [
            'raw' => true,
            'callback' => function($model) {
                $result = '';
                if ($model['type'] == Message::TYPE_INFO || $model['type'] == Message::TYPE_CHANGE_PRICE) {
                    $result = '<i class="fa fa-warning text-yellow"></i>';
                } else if ($model['status'] == Message::TYPE_SALE_SUCCESS) {
                    $result = '<i class="fa fa-shopping-cart text-green"></i>';
                }
                return $result;
            }
        ];
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
