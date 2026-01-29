<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', priority: 10)]
    public function index(): Response
    {
        // If user is logged in (session-based, for teachers), redirect to courses
        if ($this->getUser()) {
            return $this->redirectToRoute('course_index');
        }
        
        // Otherwise, render the React app directly (no redirect)
        return $this->render('react/app.html.twig');
    }
}
