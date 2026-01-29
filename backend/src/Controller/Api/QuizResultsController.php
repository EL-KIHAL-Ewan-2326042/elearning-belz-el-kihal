<?php

namespace App\Controller\Api;

use App\Entity\Quiz;
use App\Dto\QuizResultsDto;
use App\Repository\QuizAttemptRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class QuizResultsController extends AbstractController
{
    public function __construct(
        private QuizAttemptRepository $attemptRepository
    ) {}

    public function __invoke(Quiz $quiz): QuizResultsDto
    {
        $attempts = $this->attemptRepository->findBy(['quiz' => $quiz], ['submittedAt' => 'DESC']);
        
        $dto = new QuizResultsDto();
        $dto->quiz = $quiz;
        $dto->attempts = $attempts;
        $dto->totalAttempts = count($attempts);
        
        if ($dto->totalAttempts > 0) {
            $totalPercentage = 0;
            foreach ($attempts as $attempt) {
                if ($attempt->getMaxScore() > 0) {
                    $totalPercentage += ($attempt->getScore() / $attempt->getMaxScore()) * 100;
                }
            }
            $dto->averageScore = round($totalPercentage / $dto->totalAttempts, 1);
        }

        return $dto;
    }
}
