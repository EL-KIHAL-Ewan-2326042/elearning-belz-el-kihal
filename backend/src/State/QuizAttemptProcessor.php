<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\QuizAttempt;
use App\Entity\Student;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class QuizAttemptProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        private Security $security,
        private \Doctrine\ORM\EntityManagerInterface $entityManager
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if ($data instanceof QuizAttempt && $operation->getMethod() === 'POST') {
            $user = $this->security->getUser();

            if (!$user) {
                throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException('Vous devez être connecté.');
            }

            $currentId = $user->getId();
            
            // Try to find the Student entity for this user
            $student = $this->entityManager->getRepository(Student::class)->find($currentId);

            if (!$student) {
                // Determine if the user is actually a teacher trying to test
                if ($this->security->isGranted('ROLE_TEACHER')) {
                     // Teachers can submit but result might not be linked to a 'Student' entity 
                     // unless we create a dual account. 
                     // For now, let's allow it but warn or just process without student?
                     // NO, data->setStudent requires Student.
                     // If teacher, maybe we shouldn't save a QuizAttempt? Or Teacher is not a Student.
                     // Let's throw for now to see if the User is a Student.
                     // Actually, if the user IS a Student, find() MUST return it.
                }
                
                // If we are here, we have a User ID that is NOT in the Student table (or mapped as Student)
                // Throwing exception to reveal this state
                throw new \RuntimeException(sprintf(
                    'Impossible de lier votre compte (ID: %d, Roles: %s) à un profil Étudiant. Êtes-vous bien inscrit comme étudiant ?', 
                    $currentId, 
                    json_encode($user->getRoles())
                ));
            }

            $data->setStudent($student);
            $this->calculateScore($data);
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }

    private function calculateScore(QuizAttempt $attempt): void
    {
        $quiz = $attempt->getQuiz();
        if (!$quiz) return;

        $score = 0;
        $maxScore = 0;
        $studentAnswers = $attempt->getAnswers(); // Format: { questionId: answerId }

        foreach ($quiz->getQuestions() as $question) {
            $maxScore += $question->getPoints();
            
            $questionId = $question->getId();
            if (isset($studentAnswers[$questionId])) {
                $givenAnswerId = $studentAnswers[$questionId];
                
                foreach ($question->getAnswers() as $answer) {
                    if ($answer->getId() === $givenAnswerId && $answer->isCorrect()) {
                        $score += $question->getPoints();
                        break;
                    }
                }
            }
        }

        $attempt->setScore($score);
        $attempt->setMaxScore($maxScore);
    }
}
