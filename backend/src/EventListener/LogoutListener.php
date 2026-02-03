<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Event\LogoutEvent;

#[AsEventListener]
class LogoutListener
{
    public function __invoke(LogoutEvent $event): void
    {
        $response = $event->getResponse();
        
        if ($response) {
            // Force browser to clear localStorage, sessionStorage, and cookies
            // This ensures potential React JWT tokens are wiped when logging out of Symfony
            $response->headers->set('Clear-Site-Data', '"storage", "cookies"');
        }
    }
}
