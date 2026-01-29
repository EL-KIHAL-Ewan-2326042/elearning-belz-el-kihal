<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Profiler\Profiler;

class DebugController extends AbstractController
{
    #[Route('/debug-symfony', name: 'app_debug_symfony')]
    public function index(?Profiler $profiler): Response
    {
        $hasProfiler = $profiler !== null;
        
        return new Response(
            '<html><body>'.
            '<h1>Symfony works!</h1>'.
            '<p>Profiler status: ' . ($hasProfiler ? 'Enabled' : 'Disabled') . '</p>'.
            '<p>Current environment: ' . $_ENV['APP_ENV'] . '</p>'.
            '</body></html>'
        );
    }

    #[Route('/debug-twig', name: 'app_debug_twig')]
    public function debugTwig(): Response
    {
        return $this->render('test_twig.html.twig');
    }
}
