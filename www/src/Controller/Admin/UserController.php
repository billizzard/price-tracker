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


use App\Entity\User;
use App\Repository\UserRepository;
use stringEncode\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;

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
class UserController extends MainController
{
    public function indexAction(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $user = $this->getUser();

        $form = $this->createEditUserForm($user);

        if($request->isXmlHttpRequest()) {
            $form->handleRequest($request);

            $response = $this->getJsonSuccessResponse(['message' => 'v.Данные успешно обновлены']);
            if ($form->isSubmitted() && $form->isValid()) {
                $error = $this->editUser($user, $form->getData(), $encoder);
                if ($error) {
                    $response = $this->getJsonErrorResponse($error);
                }
            } else {
                foreach ($form as $fieldName => $formField) {
                    foreach ($formField->getErrors() as $error) {
                        $response = $this->getJsonErrorResponse(['field' => $fieldName, 'message' => $error->getMessage()]);
                        break 2;
                    }
                }
            }
            return $this->json($response);
        }

        return $this->render('user/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function avatarsAction(Request $request)
    {
        $user = $this->getUser();
        if($request->isXmlHttpRequest()) {
            if ($basename = basename($request->get('src'))) {
                $user->setAvatar($basename);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();
                return $this->json($this->getJsonSuccessResponse(['message' => 'v.Данные успешно обновлены']));
            }
        }

        /** @var UserRepository $repository */
        $repository = $this->getDoctrine()->getRepository(User::class);
        $avatars = $repository->getAllAvatars();

        $perPage = 24;
        $total = count($avatars);
        $curPage = (int)$request->get('page') ? abs((int)$request->get('page')) : 1;
        $totalPages = ceil($total/$perPage);
        if ($curPage > $totalPages) {
            throw new NotFoundHttpException();
        }

        $avatars = array_slice($avatars, ($curPage - 1) * $perPage, $perPage);

        return $this->render('user/avatars.html.twig', [
            'avatars' => $avatars,
            'currentAvatar' => $user->getAvatar(),
            'paginationData' => ['total' => $totalPages]
        ]);
    }

    private function editUser(User $user, $data, UserPasswordEncoderInterface $encoder)
    {
        $result = [];
        /** @var UserRepository $repository */
        $repository = $this->getDoctrine()->getRepository(User::class);
        $userAlready = $repository->findOtherByEmailOrNick($data['email'], $data['nickName'], $user->getId());

        if ($userAlready) {
            if ($userAlready->getEmail() == $data['email']) {
                $result = ['field' => 'email', 'message' => 'v.email alredy'];
            } else {
                $result = ['field' => 'nickName', 'message' => 'v.nickName alredy'];
            }
        } else {
            try {
                $user->changeByData($data, $encoder);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();
            } catch (\Exception $e) {
                $result = ['field' => 'newPassword', 'message' => $e->getMessage()];
            }
        }

        return $result;
    }

    private function createEditUserForm(User $user)
    {
        return $this->createFormBuilder()
            ->add('email', EmailType::class, [
                'data' => $user->getEmail(),
                'constraints' => array(
                    new Email(),
                ),
            ])
            ->add('nickName', TextType::class, [
                'data' => $user->getNickName(),
                'constraints' => array(
                    new Length([
                        'min' => 3,
                        'max' => 30
                    ]),
                ),
            ])
            ->add('oldPassword', PasswordType::class)
            ->add('newPassword', PasswordType::class, [
                'constraints' => array(
                    new Length([
                        'min' => 6,
                        'max' => 50
                    ]),
                )
            ])
            ->add('repeatPassword', PasswordType::class)
            ->getForm();
    }
}
