<?php

namespace App\Controller;

use App\Repository\TeacherRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/teachers')]
#[IsGranted('ROLE_TEACHER')]
class TeacherController extends AbstractController
{
    #[Route('', name: 'teacher_index', methods: ['GET'])]
    public function index(TeacherRepository $teacherRepository): Response
    {
        $teachers = $teacherRepository->findAll();
        
        return $this->render('teacher/index.html.twig', [
            'teachers' => $teachers,
        ]);
    }
}
