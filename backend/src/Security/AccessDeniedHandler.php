<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;

class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function handle(Request $request, AccessDeniedException $accessDeniedException): ?Response
    {
        // If the user is logged in (Student) but tries to access Teacher area (Access Denied)
        // Redirect them to the login page (or logout them first ideally, but redirect is what was asked)
        
        // Flash message could be added if we had access to FlashBag here easily, 
        // but simple redirect is safer.
        
        return new RedirectResponse($this->urlGenerator->generate('app_teacher_login'));
    }
}
