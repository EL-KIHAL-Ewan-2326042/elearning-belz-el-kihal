<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: 'lexik_jwt_authentication.on_jwt_created')]
final class JWTCreatedListener
{
    public function __invoke(JWTCreatedEvent $event): void
    {
        $payload = $event->getData();
        $user = $event->getUser();

        // Ajouter l'ID utilisateur
        if (method_exists($user, 'getId')) {
            $payload['id'] = $user->getId();
        }

        // Ajouter les informations de l'utilisateur dans le payload
        if (method_exists($user, 'getFirstName')) {
            $payload['firstName'] = $user->getFirstName();
        }
        
        if (method_exists($user, 'getLastName')) {
            $payload['lastName'] = $user->getLastName();
        }

        // S'assurer que les rôles sont présents
        if (method_exists($user, 'getRoles')) {
            $payload['roles'] = $user->getRoles();
        }

        $event->setData($payload);
    }
}
