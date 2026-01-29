<?php

namespace App\Service;

use App\Entity\Answer;
use App\Entity\Course;
use App\Entity\Question;
use App\Entity\Quiz;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\AI\Platform\PlatformInterface;
use Symfony\AI\Platform\Message\UserMessage;

class QuizGeneratorService
{
    public function __construct(
        private PlatformInterface $platform,
        private EntityManagerInterface $em
    ) {}

    public function generateFromCourse(Course $course, int $questionCount = 10): Quiz
    {
        $content = $this->extractCourseContent($course);
        
        $prompt = <<<PROMPT
Tu es un expert en création de QCM pédagogiques.
Génère exactement {$questionCount} questions de QCM basées sur le contenu suivant :

{$content}

IMPORTANT: Réponds UNIQUEMENT avec du JSON valide, sans texte avant ou après.

Format JSON attendu :
{
    "title": "QCM - [Titre basé sur le contenu]",
    "description": "[Description courte du QCM]",
    "questions": [
        {
            "content": "[Texte de la question]",
            "answers": [
                {"content": "[Réponse A]", "isCorrect": false},
                {"content": "[Réponse B]", "isCorrect": true},
                {"content": "[Réponse C]", "isCorrect": false},
                {"content": "[Réponse D]", "isCorrect": false}
            ]
        }
    ]
}

Règles :
- Chaque question doit avoir exactement 4 réponses
- Une seule réponse correcte par question
- Questions variées et pertinentes par rapport au contenu
- Niveau adapté à des étudiants
PROMPT;

        $response = $this->platform->invoke('openai:gpt-4o-mini', new UserMessage($prompt));
        $responseContent = $response->asText();
        $jsonStart = strpos($responseContent, '{');
        $jsonEnd = strrpos($responseContent, '}') + 1;
        $jsonString = substr($responseContent, $jsonStart, $jsonEnd - $jsonStart);
        
        $quizData = json_decode($jsonString, true);
        
        if (!$quizData) {
            throw new \RuntimeException('Impossible de parser la réponse IA: ' . json_last_error_msg());
        }
        
        return $this->createQuizFromData($course, $quizData);
    }

    private function extractCourseContent(Course $course): string
    {
        $content = "Titre du cours: {$course->getTitle()}\n";
        $content .= "Description: {$course->getDescription()}\n\n";
        
        if ($course->getSummary()) {
            $content .= "Résumé: {$course->getSummary()}\n\n";
        }
        
        foreach ($course->getVideos() as $video) {
            $content .= "Vidéo: {$video->getTitle()}\n";
            if ($video->getDescription()) {
                $content .= "Description: {$video->getDescription()}\n";
            }
            $content .= "\n";
        }
        
        foreach ($course->getDocuments() as $document) {
            $content .= "Document: {$document->getTitle()}\n";
            if ($document->getDescription()) {
                $content .= "Description: {$document->getDescription()}\n";
            }
            $content .= "\n";
        }
        
        return $content;
    }

    private function createQuizFromData(Course $course, array $data): Quiz
    {
        $quiz = new Quiz();
        $quiz->setTitle($data['title'] ?? 'QCM - ' . $course->getTitle());
        $quiz->setDescription($data['description'] ?? null);
        $quiz->setCourse($course);
        $quiz->setCreatedAt(new \DateTime());
        $quiz->setIsGeneratedByAI(true);

        foreach ($data['questions'] ?? [] as $index => $qData) {
            $question = new Question();
            $question->setContent($qData['content']);
            $question->setOrderNumber($index + 1);
            $question->setPoints(1);
            $question->setQuiz($quiz);
            $quiz->addQuestion($question);

            foreach ($qData['answers'] ?? [] as $aData) {
                $answer = new Answer();
                $answer->setContent($aData['content']);
                $answer->setIsCorrect($aData['isCorrect'] ?? false);
                $answer->setQuestion($question);
                $question->addAnswer($answer);
            }
        }

        $this->em->persist($quiz);
        $this->em->flush();

        return $quiz;
    }
}
