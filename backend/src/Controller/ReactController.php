<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ReactController extends AbstractController
{
    /**
     * Catch-all route for React frontend.
     * Matches all paths EXCEPT /api, /teacher, /_profiler, /_wdt
     */
    #[Route(
        path: '/{reactRouting}',
        name: 'react_app',
        requirements: ['reactRouting' => '^(?!api|teacher|courses|login|logout|dashboard|_profiler|_wdt|debug).*'],
        defaults: ['reactRouting' => ''],
        priority: -100,
        methods: ['GET']
    )]
    public function index(): Response
    {
        return $this->render('react/app.html.twig');
    }
}
