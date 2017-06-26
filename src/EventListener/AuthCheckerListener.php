<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AuthCheckerListener
{
    /**
     * Handles security related exceptions.
     * @param GetResponseForExceptionEvent $event
     */
    public function onCoreException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        $request = $event->getRequest();

        if ($request->isXmlHttpRequest()) {
            // Not logged in (from Security firewall)
            if ($exception instanceof AuthenticationException) {
                $event->setResponse(new JsonResponse(['need_to_login' => true], 403));
            }
            // Not enough privileges (from controllers)
            if ($exception instanceof AccessDeniedException) {
                $event->setResponse(new JsonResponse(['access_denied' => true], 403));
            }
        }
    }
}
