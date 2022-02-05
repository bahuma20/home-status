<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class TokenSubscriber implements EventSubscriberInterface
{
    const PUBLIC_PATHS = [
        '/',
        '/connect/twitch/callback',
        '/connect/google/callback'
    ];

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest'
        ];
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();

        if (in_array($request->getPathInfo(), self::PUBLIC_PATHS)) {
            return;
        }

        if (!$request->query->has('token')) {
            throw new UnauthorizedHttpException('token', 'Query parameter "token" is missing');
        }

        $token = $request->query->get('token');

        if ($token !== $_ENV['API_TOKEN']) {
            throw new AccessDeniedHttpException('The provided authentication token is wrong');
        }
    }

}
