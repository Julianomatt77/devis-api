<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class JWTCreatedListener
{
    #[AsEventListener(event: 'lexik_jwt_authentication.on_jwt_created')]
    public function onJWTCreated(JWTCreatedEvent $event)
    {
        $user = $event->getUser();

        // Vérification si l'utilisateur est soft deleted
        if ($user->getDeletedAt() !== null) {
            throw new AccessDeniedHttpException('Votre compte '. $user->getUsername() .' est désactivé.');
        }
    }
}
