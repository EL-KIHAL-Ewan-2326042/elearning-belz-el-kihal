<?php

namespace App\Service;

use App\Entity\Answer;
use App\Entity\Course;
use App\Entity\Question;
use App\Entity\Quiz;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class QuizGeneratorService
{
    public function __construct(
        private EntityManagerInterface $em,
        private AiQuizService $aiQuizService,
        private LoggerInterface $logger
    ) {
    }

    public function generateQuizFromCourse(Course $course, array $counts = ['total' => 5]): Quiz
    {
        // 1. Extraire tout le contenu
        $fullContent = $this->extractContent($course);

        // 2. Générer les questions via IA
        $questionsData = $this->aiQuizService->generateQuizFromText($fullContent, $counts);

        // 3. Créer le QCM en base
        return $this->createQuizEntity($course, $questionsData);
    }

    private function extractContent(Course $course): string
    {
        $content = "Titre: " . $course->getTitle() . "\n";
        $content .= "Description: " . $course->getDescription() . "\n";
        $content .= "Résumé: " . $course->getSummary() . "\n\n";

        // Documents PDF
        foreach ($course->getDocuments() as $document) {
            $content .= "Document '{$document->getTitle()}':\n";
            if ($document->getContent()) {
                 // Limiter la taille pour éviter de saturer le prompt
                $content .= substr($document->getContent(), 0, 5000) . "\n\n";
            } else {
                $content .= "(Contenu non extrait)\n\n";
            }
        }

        // Vidéos
        foreach ($course->getVideos() as $video) {
            $content .= "Vidéo '{$video->getTitle()}': " . $video->getDescription() . "\n";
            if ($video->getTranscription()) {
                 // Limiter la taille pour éviter de saturer le prompt
                $content .= "Transcription:\n" . substr($video->getTranscription(), 0, 5000) . "\n\n";
            } else {
                $content .= "(Transcription non disponible)\n\n";
            }
        }

        return $content;
    }

    private function createQuizEntity(Course $course, array $questionsData): Quiz
    {
        // Calculate the next quiz number for this course
        $existingQuizzesCount = $course->getQuizzes()->count();
        $quizNumber = $existingQuizzesCount + 1;
        
        $quiz = new Quiz();
        $quiz->setTitle('QCM #' . $quizNumber . ' - ' . $course->getTitle());
        $quiz->setCourse($course);
        $quiz->setIsGeneratedByAI(true);
        $quiz->setCreatedAt(new \DateTime());
        $quiz->setDescription("QCM généré automatiquement par IA basé sur le contenu du cours.");

        foreach ($questionsData as $index => $qData) {
            $question = new Question();
            $question->setContent($qData['content']);
            $question->setOrderNumber($index + 1);
            $question->setPoints(1);
            $question->setQuiz($quiz);
            
            // Determine if multiple choice
            if (isset($qData['type']) && $qData['type'] === 'multiple_choice_multiple') {
                $question->setIsMultiple(true);
            } else {
                 // Fallback inference: check if prompt requested multiple for this question? 
                 // Or check answer count? No, standard generic inference is safer.
                 // Actually, let's look at the correct answers count.
                 $correctCount = 0;
                 foreach ($qData['answers'] as $a) {
                     if ($a['isCorrect']) $correctCount++;
                 }
                 $question->setIsMultiple($correctCount > 1);
            }

            foreach ($qData['answers'] as $aData) {
                $answer = new Answer();
                $answer->setContent($aData['content']);
                $answer->setIsCorrect($aData['isCorrect']);
                $answer->setQuestion($question);
                
                $question->addAnswer($answer);
            }

            $quiz->addQuestion($question);
        }

        $this->em->persist($quiz);
        $this->em->flush();

        return $quiz;
    }
}
