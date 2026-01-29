<?php

namespace App\Controller;

use App\Entity\Course;
use App\Entity\Video;
use App\Entity\Document;
use App\Form\CourseType;
use App\Repository\CourseRepository;
use App\Service\QuizGeneratorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/courses')]
#[IsGranted('ROLE_TEACHER')]
class CourseController extends AbstractController
{
    #[Route('', name: 'course_index', methods: ['GET'])]
    public function index(CourseRepository $courseRepository): Response
    {
        $courses = $courseRepository->findBy(['teacher' => $this->getUser()], ['createdAt' => 'DESC']);
        
        return $this->render('course/index.html.twig', [
            'courses' => $courses,
        ]);
    }

    #[Route('/new', name: 'course_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $course = new Course();
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $course->setTeacher($this->getUser());
            $em->persist($course);
            $em->flush();

            $this->addFlash('success', 'Cours créé avec succès !');
            return $this->redirectToRoute('course_show', ['id' => $course->getId()]);
        }

        return $this->render('course/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'course_show', methods: ['GET'])]
    public function show(Course $course): Response
    {
        if ($course->getTeacher() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas accéder à ce cours.');
        }

        return $this->render('course/show.html.twig', [
            'course' => $course,
        ]);
    }

    #[Route('/{id}/edit', name: 'course_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Course $course, EntityManagerInterface $em): Response
    {
        if ($course->getTeacher() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier ce cours.');
        }

        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Cours modifié avec succès !');
            return $this->redirectToRoute('course_show', ['id' => $course->getId()]);
        }

        return $this->render('course/edit.html.twig', [
            'course' => $course,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'course_delete', methods: ['POST'])]
    public function delete(Request $request, Course $course, EntityManagerInterface $em): Response
    {
        if ($course->getTeacher() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas supprimer ce cours.');
        }

        if ($this->isCsrfTokenValid('delete' . $course->getId(), $request->request->get('_token'))) {
            $em->remove($course);
            $em->flush();
            $this->addFlash('success', 'Cours supprimé avec succès !');
        }

        return $this->redirectToRoute('course_index');
    }

    #[Route('/{id}/generate-quiz', name: 'course_generate_quiz', methods: ['POST'])]
    public function generateQuiz(
        Request $request, 
        Course $course, 
        QuizGeneratorService $quizGenerator
    ): Response {
        if ($this->isCsrfTokenValid('generate_quiz' . $course->getId(), $request->request->get('_token'))) {
            try {
                $counts = [
                    'true_false' => (int) $request->request->get('true_false_count', 0),
                    'mcq_single' => (int) $request->request->get('mcq_single_count', 0),
                    'mcq_multiple' => (int) $request->request->get('mcq_multiple_count', 0),
                ];
                $counts['total'] = array_sum($counts);

                if ($counts['total'] === 0) {
                    $counts['total'] = 10; // Default if nothing selected
                }

                $quiz = $quizGenerator->generateQuizFromCourse($course, $counts);
                $this->addFlash('success', "QCM '{$quiz->getTitle()}' généré avec succès ({$quiz->getQuestions()->count()} questions) !");
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Erreur lors de la génération du QCM : ' . $e->getMessage());
            }
        }

        return $this->redirectToRoute('course_show', ['id' => $course->getId()]);
    }
}
