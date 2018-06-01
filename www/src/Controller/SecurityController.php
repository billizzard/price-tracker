<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangeType;
use App\Form\ForgotType;
use App\Form\RegistrationType;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class SecurityController extends FrontendController
{
    public function loginAction(Request $request, AuthenticationUtils $helper): Response
    {
        /** @var User $user */
        if ($user = $this->getUser()) {
            $user->setLastLogin(time());
            $this->save($user);
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

    public function confirmAction(Request $request, UserRepository $repository)
    {
        $user = $repository->findByConfirmCode($request->get('code'));
        if ($user) {
            $user->setIsConfirmed(true);
            $this->save($user);
            $this->addFlash('success', 'l.reg_success_confirm');
            return $this->redirectToRoute('security_login');
        }

        throw new NotFoundHttpException();
    }

    public function forgotAction(Request $request, UserRepository $repository)
    {
        $form = $this->createForm(ForgotType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $user = $repository->findByEmail($email);
            if ($user) {
                $user->setIsConfirmed(false)->setConfirmCode($user->getConfirmCode())->setLastConfirmCode(time());
                $this->save($user);

                $this->sendForgotEmail($email, ['link' => $this->generateUrl('security_change', ['code' => $user->getConfirmCode()], UrlGeneratorInterface::ABSOLUTE_URL)], $request->getLocale());
                $this->addFlash('success', $this->translator->trans('s.forgot'));
            } else {
                $this->addFlash('error', $this->translator->trans('e.user_not_found'));
            }
        }

        $this->setFlashFormError($form);

        return $this->render('security/forgot.html.twig', [
            'form' => $form->createView()
        ]);
    }

    public function changeAction(Request $request, UserRepository $repository, UserPasswordEncoderInterface $passwordEncoder)
    {
        $user = $repository->findByConfirmCode($request->get('code'));

        if ($user) {
            $form = $this->createForm(ChangeType::class);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
                $user->setPassword($password)->setIsConfirmed(true);
                $this->save($user);

                $this->addFlash('success', $this->translator->trans('s.password_changed'));

                return $this->redirectToRoute('security_login');
            }

            $this->setFlashFormError($form);

            return $this->render('security/change.html.twig', [
                'form' => $form->createView()
            ]);
        }

        throw new NotFoundHttpException();
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
        if ($form->isSubmitted() && $form->isValid() && $request->isXmlHttpRequest()) {
            // 3) Encode the password (you could also do this via Doctrine listener)
            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password)->setConfirmCode($user->generateConfirmCode());
            $this->save($user);

            $this->sendRegistrationEmail($user->getEmail(), ['link' => $this->generateUrl('security_confirm', ['code' => $user->getConfirmCode()], UrlGeneratorInterface::ABSOLUTE_URL)], $request->getLocale());
            $response = $this->getJsonSuccessResponse(['url' => $this->generateUrl('security_login')]);

            return $this->json($response);
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
