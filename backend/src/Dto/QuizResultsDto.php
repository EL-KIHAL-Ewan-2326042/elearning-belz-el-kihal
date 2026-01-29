<?php

namespace App\Dto;

use App\Entity\Quiz;
use Symfony\Component\Serializer\Annotation\Groups;

class QuizResultsDto
{
    #[Groups(['quiz:results'])]
    public ?Quiz $quiz = null;

    #[Groups(['quiz:results'])]
    public int $totalAttempts = 0;

    #[Groups(['quiz:results'])]
    public float $averageScore = 0.0;

    #[Groups(['quiz:results'])]
    public array $attempts = [];
}
