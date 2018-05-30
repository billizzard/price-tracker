<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Translation\TranslatorInterface;


class BaseController extends Controller
{
    protected $logger;
    protected $translator;

    public function __construct(TranslatorInterface $translator, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->translator = $translator;
    }

    protected function sendEmail($subject, $to, $template, $params = [])
    {
        $mailer = $this->container->get('mailer');
        $message = (new \Swift_Message($subject))
            ->setFrom('88billizzard88@gmail.com')
            ->setTo($to)
            ->setBody(
                $this->renderView(
                    // templates/emails/registration.html.twig
                    'emails/' . $template,
                    //array('name' => 'Vladimir')
                    $params
                ),
                'text/html'
            )
        ;
        $mailer->send($message);
    }

    protected function sendRegistrationEmail($to, $params = [], $locale = 'en')
    {
        $this->sendEmail($this->translator->trans('l.registration_confirm'), $to, $locale . '_registration.html.twig', $params);
    }

    protected function sendForgotEmail($to, $params = [], $locale = 'en')
    {
        $this->sendEmail($this->translator->trans('l.change_password'), $to, $locale . '_forgot.html.twig', $params);
    }
}
