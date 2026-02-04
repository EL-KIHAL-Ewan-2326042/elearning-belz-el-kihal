<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use App\Repository\QuizAttemptRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: QuizAttemptRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(security: "is_granted('ROLE_USER')"),
        new \ApiPlatform\Metadata\Get(security: "is_granted('ROLE_USER')"),
        new Post(processor: \App\State\QuizAttemptProcessor::class, security: "is_granted('ROLE_USER')"),
    ],
    normalizationContext: ['groups' => ['quiz_attempt:read']],
    order: ['submittedAt' => 'DESC']
)]
#[ApiFilter(SearchFilter::class, properties: ['student' => 'exact', 'quiz' => 'exact'])]
class QuizAttempt
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['quiz_attempt:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'quizAttempts')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['quiz_attempt:read'])]
    private ?Student $student = null;

    #[ORM\ManyToOne(inversedBy: 'attempts')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['quiz_attempt:read'])]
    private ?Quiz $quiz = null;

    #[ORM\Column]
    #[Groups(['quiz_attempt:read'])]
    private ?int $score = null;

    #[ORM\Column]
    #[Groups(['quiz_attempt:read'])]
    private ?int $maxScore = null;

    #[ORM\Column(type: Types::JSON)]
    #[Groups(['quiz_attempt:read'])]
    private array $answers = [];

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['quiz_attempt:read'])]
    private ?\DateTimeInterface $submittedAt = null;

    #[ORM\Column]
    #[Groups(['quiz_attempt:read'])]
    private ?int $timeSpentSeconds = null;

    public function __construct()
    {
        $this->submittedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStudent(): ?Student
    {
        return $this->student;
    }

    public function setStudent(?Student $student): static
    {
        $this->student = $student;
        return $this;
    }

    public function getQuiz(): ?Quiz
    {
        return $this->quiz;
    }

    public function setQuiz(?Quiz $quiz): static
    {
        $this->quiz = $quiz;
        return $this;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(int $score): static
    {
        $this->score = $score;
        return $this;
    }

    public function getMaxScore(): ?int
    {
        return $this->maxScore;
    }

    public function setMaxScore(int $maxScore): static
    {
        $this->maxScore = $maxScore;
        return $this;
    }

    public function getAnswers(): array
    {
        return $this->answers;
    }

    public function setAnswers(array $answers): static
    {
        $this->answers = $answers;
        return $this;
    }

    public function getSubmittedAt(): ?\DateTimeInterface
    {
        return $this->submittedAt;
    }

    public function setSubmittedAt(\DateTimeInterface $submittedAt): static
    {
        $this->submittedAt = $submittedAt;
        return $this;
    }

    public function getTimeSpentSeconds(): ?int
    {
        return $this->timeSpentSeconds;
    }

    public function setTimeSpentSeconds(int $timeSpentSeconds): static
    {
        $this->timeSpentSeconds = $timeSpentSeconds;
        return $this;
    }
}
