<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use App\Repository\CourseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Controller\Api\GenerateQuizController;
use App\Dto\GenerateQuizDto;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;

#[ORM\Entity(repositoryClass: CourseRepository::class)]
#[ApiFilter(SearchFilter::class, properties: ['teacher' => 'exact'])]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(),
        new Patch(),
        new Delete(),
        new Post(
            uriTemplate: '/courses/{id}/generate_quiz',
            controller: GenerateQuizController::class,
            input: GenerateQuizDto::class,
            output: Quiz::class,
            name: 'generate_quiz'
        )
    ],
    normalizationContext: ['groups' => ['course:read']],
    denormalizationContext: ['groups' => ['course:write']]
)]
class Course
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['course:list', 'course:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['course:list', 'course:read', 'course:write', 'quiz_attempt:read', 'quiz:read'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['course:list', 'course:read', 'course:write'])]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['course:read', 'course:write'])]
    private ?string $summary = null;

    #[ORM\ManyToOne(inversedBy: 'courses')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['course:list', 'course:read'])]
    private ?Teacher $teacher = null;

    /** @var Collection<int, Video> */
    #[ORM\OneToMany(mappedBy: 'course', targetEntity: Video::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['course:read'])]
    private Collection $videos;

    /** @var Collection<int, Document> */
    #[ORM\OneToMany(mappedBy: 'course', targetEntity: Document::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['course:read'])]
    private Collection $documents;

    /** @var Collection<int, Quiz> */
    #[ORM\OneToMany(mappedBy: 'course', targetEntity: Quiz::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['course:read'])]
    private Collection $quizzes;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['course:list', 'course:read'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['course:list', 'course:read', 'course:write'])]
    private ?string $thumbnail = null;

    public function __construct()
    {
        $this->videos = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->quizzes = new ArrayCollection();
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

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(?string $summary): static
    {
        $this->summary = $summary;
        return $this;
    }

    public function getTeacher(): ?Teacher
    {
        return $this->teacher;
    }

    public function setTeacher(?Teacher $teacher): static
    {
        $this->teacher = $teacher;
        return $this;
    }

    /** @return Collection<int, Video> */
    public function getVideos(): Collection
    {
        return $this->videos;
    }

    public function addVideo(Video $video): static
    {
        if (!$this->videos->contains($video)) {
            $this->videos->add($video);
            $video->setCourse($this);
        }
        return $this;
    }

    public function removeVideo(Video $video): static
    {
        if ($this->videos->removeElement($video)) {
            if ($video->getCourse() === $this) {
                $video->setCourse(null);
            }
        }
        return $this;
    }

    /** @return Collection<int, Document> */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(Document $document): static
    {
        if (!$this->documents->contains($document)) {
            $this->documents->add($document);
            $document->setCourse($this);
        }
        return $this;
    }

    public function removeDocument(Document $document): static
    {
        if ($this->documents->removeElement($document)) {
            if ($document->getCourse() === $this) {
                $document->setCourse(null);
            }
        }
        return $this;
    }

    /** @return Collection<int, Quiz> */
    public function getQuizzes(): Collection
    {
        return $this->quizzes;
    }

    public function addQuiz(Quiz $quiz): static
    {
        if (!$this->quizzes->contains($quiz)) {
            $this->quizzes->add($quiz);
            $quiz->setCourse($this);
        }
        return $this;
    }

    public function removeQuiz(Quiz $quiz): static
    {
        if ($this->quizzes->removeElement($quiz)) {
            if ($quiz->getCourse() === $this) {
                $quiz->setCourse(null);
            }
        }
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getThumbnail(): ?string
    {
        return $this->thumbnail;
    }

    public function setThumbnail(?string $thumbnail): static
    {
        $this->thumbnail = $thumbnail;
        return $this;
    }
}
