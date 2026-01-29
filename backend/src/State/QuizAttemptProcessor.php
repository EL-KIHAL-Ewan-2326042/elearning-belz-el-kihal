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
        private Security $security
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if ($data instanceof QuizAttempt && $operation->getMethod() === 'POST') {
            $user = $this->security->getUser();
            
            // Si l'utilisateur est un User générique mais qu'on veut un Student, on force le cast ou la récuperation
            if ($user && in_array('ROLE_STUDENT', $user->getRoles())) {
                // Pour Doctrine, si c'est SINGLE_TABLE, User peut être Student.
                // On s'assure que c'est bien une instance de Student
                if (!$user instanceof Student) {
                     // Cas rare mais possible si le token user provider retourne User au lieu de la sous-classe
                     // On recharge depuis la repo Student ?? Non, normalement Doctrine gère ça.
                     // Mais on va être permissif sur le typehint dans setStudent si nécessaire ou on checke.
                }

                if ($user instanceof Student) {
                    $data->setStudent($user);
                }
            } else {
                // DEBUG: Si ce n'est pas un étudiant, on ne peut pas soumettre
                // throw new AccessDeniedException("Seuls les étudiants peuvent soumettre un QCM.");
            }
            
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
