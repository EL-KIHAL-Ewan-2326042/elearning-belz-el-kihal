<?php

namespace App\Controller;

use App\Entity\Quiz;
use App\Repository\QuizRepository;
use App\Repository\QuizAttemptRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/quizzes')]
#[IsGranted('ROLE_TEACHER')]
class QuizController extends AbstractController
{
    #[Route('', name: 'quiz_index', methods: ['GET'])]
    public function index(QuizRepository $quizRepository): Response
    {
        $quizzes = $quizRepository->findBy([], ['createdAt' => 'DESC']);
        
        return $this->render('quiz/index.html.twig', [
            'quizzes' => $quizzes,
        ]);
    }

    #[Route('/{id}', name: 'quiz_show', methods: ['GET'])]
    public function show(Quiz $quiz): Response
    {
        return $this->render('quiz/show.html.twig', [
            'quiz' => $quiz,
        ]);
    }

    #[Route('/{id}/results', name: 'quiz_results', methods: ['GET'])]
    public function results(Quiz $quiz, QuizAttemptRepository $attemptRepository): Response
    {
        $attempts = $attemptRepository->findBy(['quiz' => $quiz], ['submittedAt' => 'DESC']);
        
        // Calculer les statistiques
        $totalAttempts = count($attempts);
        $averageScore = 0;
        
        if ($totalAttempts > 0) {
            $totalPercentage = 0;
            foreach ($attempts as $attempt) {
                $totalPercentage += ($attempt->getScore() / $attempt->getMaxScore()) * 100;
            }
            $averageScore = round($totalPercentage / $totalAttempts, 1);
        }
        
        return $this->render('quiz/results.html.twig', [
            'quiz' => $quiz,
            'attempts' => $attempts,
            'totalAttempts' => $totalAttempts,
            'averageScore' => $averageScore,
        ]);
    }
    #[Route('/attempt/{id}', name: 'quiz_attempt_detail', methods: ['GET'])]
    public function attemptDetail(\App\Entity\QuizAttempt $attempt): Response
    {
        return $this->render('quiz/attempt_detail.html.twig', [
            'attempt' => $attempt,
            'quiz' => $attempt->getQuiz(),
        ]);
    }
}
