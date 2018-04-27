<?php
namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Security\Http\HttpUtils;

class AuthenticationSuccessHandler
    extends DefaultAuthenticationSuccessHandler
    implements AuthenticationSuccessHandlerInterface
{

    public function __construct(HttpUtils $httpUtils, array $options = array())
    {
        parent::__construct($httpUtils, $options);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(array(
                'authenticated' => true,
                'url' => $this->httpUtils->createRedirectResponse($request, $this->determineTargetUrl($request))->getTargetUrl(),
            ));
        }
        return parent::onAuthenticationSuccess($request, $token);
    }
}