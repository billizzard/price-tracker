<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\TranslatorInterface;

class SecurityController extends FrontendController
{
    public function loginAction(Request $request, AuthenticationUtils $helper): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('tracker_list');
        }

        if ($error = $helper->getLastAuthenticationError()) {
            $this->addFlash('error', 'e.login_invalid');
        }
        return $this->render('security/login.html.twig', [
            // last username entered by the user (if any)
            'last_username' => $helper->getLastUsername(),
            // last authentication error (if any)
            'error' => $helper->getLastAuthenticationError(),
        ]);
    }

    public function logoutAction(): void
    {
        // До сюда никогда не должно дойти
        throw new \Exception('Logout');
    }

    public function registrationAction(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('tracker_list');
        }

        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);

        // 2) handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ( $form->isValid()) {
                if ($request->isXmlHttpRequest()) {
                    // 3) Encode the password (you could also do this via Doctrine listener)
                    $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
                    $user->setPassword($password);

                    // 4) save the User!
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($user);
                    $entityManager->flush();
                    $response = $this->getJsonSuccessResponse(['url' => $this->generateUrl('security_login')]);

                    return $this->json($response);
                }
            }
        }

        foreach ($form->getErrors(true, true) as $error) {
            if ($request->isXmlHttpRequest()) {
                $response = $this->getJsonErrorResponse(['message' => $this->translator->trans($error->getMessage())]);
                return $this->json($response);
            }
        }

        return $this->render(
            'security/registration.html.twig',
            array('form' => $form->createView())
        );
    }

}
