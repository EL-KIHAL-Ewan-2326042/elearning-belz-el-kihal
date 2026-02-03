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
        private PdfParserService $pdfParser,
        private VideoTranscriberService $videoTranscriber,
        private AiQuizService $aiQuizService,
        private LoggerInterface $logger
    ) {
    }

    public function generateQuizFromCourse(Course $course, int $questionCount = 5): Quiz
    {
        // 1. Extraire tout le contenu
        $fullContent = $this->extractContent($course);

        // 2. Générer les questions via IA
        $questionsData = $this->aiQuizService->generateQuizFromText($fullContent, $questionCount);

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
            try {
                if ($document->getFileName()) {
                    // Supposons que les fichiers sont dans public/uploads/documents/
                    // VichUploader stocke le nom de fichier, le chemin complet dépend de la config
                    // On va essayer de deviner le chemin absolu
                    $filePath = __DIR__ . '/../../public/uploads/documents/' . $document->getFileName();
                    
                    if (file_exists($filePath)) {
                        $text = $this->pdfParser->parsePdf($filePath);
                        $content .= "Contenu du document '{$document->getTitle()}':\n" . substr($text, 0, 5000) . "\n\n";
                    }
                }
            } catch (\Exception $e) {
                $this->logger->error("Erreur parsing PDF: " . $e->getMessage());
            }
        }

        // Vidéos
        foreach ($course->getVideos() as $video) {
            try {
                // Pour l'instant on utilise le titre et la description
                // Si on a le système de transcription, on l'appelle ici
                $content .= "Vidéo '{$video->getTitle()}': " . $video->getDescription() . "\n";
                
                // Si transcription dispo (fichier vidéo uploadé)
                /*
                if ($video->getFileName()) {
                    $videoPath = __DIR__ . '/../../public/uploads/videos/' . $video->getFileName();
                    if (file_exists($videoPath)) {
                        $transcription = $this->videoTranscriber->transcribe($videoPath);
                        $content .= "Transcription: " . $transcription . "\n";
                    }
                }
                */
            } catch (\Exception $e) {
                $this->logger->error("Erreur transcription vidéo: " . $e->getMessage());
            }
        }

        return $content;
    }

    private function createQuizEntity(Course $course, array $questionsData): Quiz
    {
        $quiz = new Quiz();
        $quiz->setTitle('QCM IA - ' . $course->getTitle());
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
