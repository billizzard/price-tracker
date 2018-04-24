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
        if ($request->isXmlHttpRequest()) {
            if ($error = $helper->getLastAuthenticationError()) {
                $response = $this->getJsonErrorResponse(['message' => $this->translator->trans('e.login_invalid')]);
            } else {
                $response = $this->getJsonSuccessResponse(['url' => 'http://price-tracker.local/ru/profile/trackers']);
            }
            return $this->json($response);
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
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);

        // 2) handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // 3) Encode the password (you could also do this via Doctrine listener)
            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);

            // 4) save the User!
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('security_login');
        }

        foreach ($form->getErrors(true, true) as $error) {
            $this->addFlash('error', $error->getMessage());
            break;
        }

        return $this->render(
            'security/registration.html.twig',
            array('form' => $form->createView())
        );
    }

}
