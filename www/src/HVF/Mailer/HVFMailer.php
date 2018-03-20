<?php
// src/Acme/TestBundle/AcmeTestBundle.php
namespace App\HVF\Mailer;

use App\HVF\PriceChecker\PriceParsers\PriceParser;
use App\HVF\PriceChecker\SiteParsers\FileGetContentParser;
use App\HVF\PriceChecker\SiteParsers\SiteParser;
use function Sodium\add;

class HVFMailer
{
    private $twig;
    private $mailer;

    public function sendSaleMail($email, \Swift_Mailer $mailer, \Twig_Environment $twig)
    {
        $message = (new \Swift_Message('Hello Email'))
            ->setFrom('price-tracker@mail.ru')
            ->setTo($email)
            ->setBody(
                $twig->render(
                // templates/emails/registration.html.twig
                    'templates/emails/registration.html.twig',
                    array('name' => 'Vasja', 'productName' => 'productName', 'productPrice' => '100')
                ),
                'text/html'
            )
            /*
             * If you also want to include a plaintext version of the message
            ->addPart(
                $this->renderView(
                    'emails/registration.txt.twig',
                    array('name' => $name)
                ),
                'text/plain'
            )
            */
        ;

        if ($mailer->send($message)) {
            return true;
        }

        return false;
    }
}