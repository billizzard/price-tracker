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

class SecurityController extends Controller
{
    public function loginAction(AuthenticationUtils $helper): Response
    {
        return $this->render('security/login.html.twig', [
            // last username entered by the user (if any)
            //'last_username' => $helper->getLastUsername(),
            // last authentication error (if any)
            //'error' => $helper->getLastAuthenticationError(),
        ]);
    }

    public function registrationAction(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $dbh = new \PDO('mysql:host=db:3306;dbname=symfony_price_tracker', 'root', 'root');
        foreach($dbh->query('SELECT 12') as $row) {
            print_r($row);
        }
        $dbh = null;
        phpinfo();
        die();
        // 1) build the form
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

            // ... do any other work - like sending them an email, etc
            // maybe set a "flash" success message for the user

            return $this->redirectToRoute('replace_with_some_route');
        }

        return $this->render(
            'security/registration.html.twig',
            array('form' => $form->createView())
        );
    }

}
