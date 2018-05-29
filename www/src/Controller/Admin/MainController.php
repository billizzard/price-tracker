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


use App\Controller\BaseController;
use App\Entity\Error;
use App\Entity\Message;
use App\Entity\User;
use App\Repository\ErrorRepository;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use Psr\Log\LoggerInterface;
use Psr\Log\Test\LoggerInterfaceTest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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
class MainController extends BaseController
{
    public function getMessages()
    {
        /** @var MessageRepository $repository */
        $repository = $this->getDoctrine()->getRepository(Message::class);
        $messages = $repository->getUnreadMessagesByUser($this->getUser(), 5);
        $cookieMessages = [];
        $class = 'fa-warning text-yellow';
        /** @var Message $message */
        foreach ($messages as $message) {
            if ($message->getType() == Message::TYPE_SALE_SUCCESS) {
                $class = 'fa-shopping-cart text-green';
            }

            $cookieMessages[] = ['id' => $message->getId(), 'class' => $class, 'message' => $message->getTranslatedTitle($this->translator)];
        }
        return $cookieMessages;
    }

    public function isHasError()
    {
        /** @var ErrorRepository $repository */
        $repository = $this->getDoctrine()->getRepository(Error::class);
        return $repository->isHasEntity();
    }

    protected function getJsonSuccessResponse($data)
    {
        return [
            'success' => true,
            'data' => $data
        ];
    }

    protected function getJsonErrorResponse($data)
    {
        return [
            'success' => false,
            'data' => $data
        ];
    }
    
    public function render(string $view, array $parameters = array(), Response $response = null): Response
    {
        $parameters['short_messages'] = $this->getMessages();
        $parameters['has_error'] = false;
        $user = $this->getUser();
        if ($user->isAdmin()) {
            $parameters['has_error'] = $this->isHasError();
        }
        return parent::render($view, $parameters, $response);
    }
}
