<?php

namespace App\Controller\Api;

use App\Entity\Course;
use App\Entity\Quiz;
use App\Dto\GenerateQuizDto;
use App\Service\QuizGeneratorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class GenerateQuizController extends AbstractController
{
    public function __construct(
        private QuizGeneratorService $quizGenerator
    ) {}

    public function __invoke(Course $course, GenerateQuizDto $dto): Quiz
    {
        return $this->quizGenerator->generateFromCourse($course, $dto->questionCount);
    }
}
