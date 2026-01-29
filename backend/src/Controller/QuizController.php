<?php

namespace App\Controller;

use App\Entity\Quiz;
use App\Entity\Question;
use App\Entity\Answer;
use App\Form\QuizType;
use App\Form\QuestionType;
use App\Repository\QuizRepository;
use App\Repository\QuizAttemptRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
        $user = $this->getUser();
        
        // Récupérer uniquement les QCM des cours du professeur connecté
        $quizzes = $quizRepository->createQueryBuilder('q')
            ->join('q.course', 'c')
            ->where('c.teacher = :teacher')
            ->setParameter('teacher', $user)
            ->orderBy('q.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
        
        return $this->render('quiz/index.html.twig', [
            'quizzes' => $quizzes,
        ]);
    }

    #[Route('/{id}', name: 'quiz_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Quiz $quiz): Response
    {
        if ($quiz->getCourse()->getTeacher() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas accéder à ce QCM.');
        }
        
        return $this->render('quiz/show.html.twig', [
            'quiz' => $quiz,
        ]);
    }

    #[Route('/{id}/edit', name: 'quiz_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, Quiz $quiz, EntityManagerInterface $em): Response
    {
        if ($quiz->getCourse()->getTeacher() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier ce QCM.');
        }

        $form = $this->createForm(QuizType::class, $quiz);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $quiz->setUpdatedAt(new \DateTime());
            $em->flush();

            $this->addFlash('success', 'QCM modifié avec succès !');
            return $this->redirectToRoute('quiz_show', ['id' => $quiz->getId()]);
        }

        return $this->render('quiz/edit.html.twig', [
            'quiz' => $quiz,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'quiz_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Quiz $quiz, EntityManagerInterface $em): Response
    {
        if ($quiz->getCourse()->getTeacher() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas supprimer ce QCM.');
        }

        if ($this->isCsrfTokenValid('delete' . $quiz->getId(), $request->request->get('_token'))) {
            $em->remove($quiz);
            $em->flush();
            $this->addFlash('success', 'QCM supprimé avec succès !');
        }

        return $this->redirectToRoute('quiz_index');
    }

    #[Route('/{id}/questions/new', name: 'quiz_question_new', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function newQuestion(Request $request, Quiz $quiz, EntityManagerInterface $em): Response
    {
        if ($quiz->getCourse()->getTeacher() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier ce QCM.');
        }

        $question = new Question();
        $question->setQuiz($quiz);
        $question->setOrderNumber($quiz->getQuestions()->count() + 1);
        $question->setPoints(1);
        
        // Ajouter 4 réponses par défaut
        for ($i = 0; $i < 4; $i++) {
            $answer = new Answer();
            $answer->setContent('');
            $answer->setIsCorrect(false);
            $question->addAnswer($answer);
        }

        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($question);
            $quiz->setUpdatedAt(new \DateTime());
            $em->flush();

            $this->addFlash('success', 'Question ajoutée avec succès !');
            return $this->redirectToRoute('quiz_show', ['id' => $quiz->getId()]);
        }

        return $this->render('quiz/question_form.html.twig', [
            'quiz' => $quiz,
            'question' => $question,
            'form' => $form,
            'is_new' => true,
        ]);
    }

    #[Route('/questions/{id}/edit', name: 'quiz_question_edit', methods: ['GET', 'POST'])]
    public function editQuestion(Request $request, Question $question, EntityManagerInterface $em): Response
    {
        $quiz = $question->getQuiz();
        
        if ($quiz->getCourse()->getTeacher() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier cette question.');
        }

        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $quiz->setUpdatedAt(new \DateTime());
            $em->flush();

            $this->addFlash('success', 'Question modifiée avec succès !');
            return $this->redirectToRoute('quiz_show', ['id' => $quiz->getId()]);
        }

        return $this->render('quiz/question_form.html.twig', [
            'quiz' => $quiz,
            'question' => $question,
            'form' => $form,
            'is_new' => false,
        ]);
    }

    #[Route('/questions/{id}/delete', name: 'quiz_question_delete', methods: ['POST'])]
    public function deleteQuestion(Request $request, Question $question, EntityManagerInterface $em): Response
    {
        $quiz = $question->getQuiz();
        
        if ($quiz->getCourse()->getTeacher() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas supprimer cette question.');
        }

        if ($this->isCsrfTokenValid('delete_question' . $question->getId(), $request->request->get('_token'))) {
            $em->remove($question);
            $em->flush();
            
            // Réordonner les questions restantes
            $remainingQuestions = $quiz->getQuestions();
            $order = 1;
            foreach ($remainingQuestions as $q) {
                $q->setOrderNumber($order++);
            }
            $quiz->setUpdatedAt(new \DateTime());
            $em->flush();
            
            $this->addFlash('success', 'Question supprimée avec succès !');
        }

        return $this->redirectToRoute('quiz_show', ['id' => $quiz->getId()]);
    }

    #[Route('/{id}/results', name: 'quiz_results', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function results(Quiz $quiz, QuizAttemptRepository $attemptRepository): Response
    {
        if ($quiz->getCourse()->getTeacher() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas voir les résultats de ce QCM.');
        }

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
        $quiz = $attempt->getQuiz();
        
        if ($quiz->getCourse()->getTeacher() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas voir cette tentative.');
        }

        return $this->render('quiz/attempt_detail.html.twig', [
            'attempt' => $attempt,
            'quiz' => $quiz,
        ]);
    }
}
