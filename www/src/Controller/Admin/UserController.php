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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\Request;
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
class UserController extends AbstractController
{
    public function indexAction(Request $request)
    {
        $user = $this->getUser();

        $form = $this->createEditUserForm($user);

        if($request->isXmlHttpRequest()) {
            $form->handleRequest($request);

            $response = ['errors' => [], 'success' => []];
            if ($form->isSubmitted() && $form->isValid()) {
                $this->editUser($user, $form->getData());
                $response['success'][]['value'] = 'v.Данные успешно обновлены';
            } else {
                foreach ($form as $fieldName => $formField) {
                    foreach ($formField->getErrors() as $error) {
                        $message = $error->getMessage();
                        $response['errors'][] = ['key' => $fieldName, 'value' => $message];
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

    private function editUser(User $user, $data)
    {
        $user->setEmail($data['email']);
        $user->setNickName($data['nickName']);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();
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
            ->add('oldPassword', PasswordType::class, [
                'mapped' => false,
            ])
            ->add('newPassword', PasswordType::class, [
                'mapped' => false,
            ])
            ->add('repeatPassword', PasswordType::class, [
                'mapped' => false,
            ])->getForm();
    }
}
