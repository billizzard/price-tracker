<?php
namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;
use Symfony\Component\Translation\TranslatorInterface;

class TestFailureHandler extends DefaultAuthenticationFailureHandler
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(array(
                'authenticated' => false,
                'error' => $this->translator->trans('e.login_invalid'),
            ));
        }

        return parent::onAuthenticationFailure($request, $exception);
    }
}