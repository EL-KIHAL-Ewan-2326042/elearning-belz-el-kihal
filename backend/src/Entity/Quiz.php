<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use App\Repository\QuizRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Controller\Api\QuizResultsController;
use App\Dto\QuizResultsDto;

#[ORM\Entity(repositoryClass: QuizRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(),
        new Patch(),
        new Delete(),
        new Get(
            uriTemplate: '/quizzes/{id}/results',
            controller: QuizResultsController::class,
            output: QuizResultsDto::class,
            name: 'quiz_results'
        )
    ],
    normalizationContext: ['groups' => ['quiz:read']],
    denormalizationContext: ['groups' => ['quiz:write']]
)]
#[ApiFilter(SearchFilter::class, properties: ['course' => 'exact'])]
class Quiz
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['quiz:list', 'quiz:read', 'course:read', 'quiz_attempt:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['quiz:list', 'quiz:read', 'course:read', 'quiz:write', 'quiz_attempt:read'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['quiz:list', 'quiz:read', 'course:read', 'quiz:write'])]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'quizzes')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['quiz:list', 'quiz:read', 'quiz:write', 'quiz_attempt:read'])]
    private ?Course $course = null;

    /** @var Collection<int, Question> */
    #[ORM\OneToMany(mappedBy: 'quiz', targetEntity: Question::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['orderNumber' => 'ASC'])]
    #[Groups(['quiz:read', 'quiz:write'])]
    private Collection $questions;

    /** @var Collection<int, QuizAttempt> */
    #[ORM\OneToMany(mappedBy: 'quiz', targetEntity: QuizAttempt::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $attempts;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['quiz:list', 'quiz:read', 'course:read'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column]
    #[Groups(['quiz:list', 'quiz:read', 'quiz:write'])]
    private bool $isGeneratedByAI = false;

    public function __construct()
    {
        $this->questions = new ArrayCollection();
        $this->attempts = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getCourse(): ?Course
    {
        return $this->course;
    }

    public function setCourse(?Course $course): static
    {
        $this->course = $course;
        return $this;
    }

    /** @return Collection<int, Question> */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(Question $question): static
    {
        if (!$this->questions->contains($question)) {
            $this->questions->add($question);
            $question->setQuiz($this);
        }
        return $this;
    }

    public function removeQuestion(Question $question): static
    {
        if ($this->questions->removeElement($question)) {
            if ($question->getQuiz() === $this) {
                $question->setQuiz(null);
            }
        }
        return $this;
    }

    /** @return Collection<int, QuizAttempt> */
    public function getAttempts(): Collection
    {
        return $this->attempts;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['quiz:list', 'quiz:read', 'course:read'])]
    private ?\DateTimeInterface $updatedAt = null;

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function isGeneratedByAI(): bool
    {
        return $this->isGeneratedByAI;
    }

    public function setIsGeneratedByAI(bool $isGeneratedByAI): static
    {
        $this->isGeneratedByAI = $isGeneratedByAI;
        return $this;
    }
}
