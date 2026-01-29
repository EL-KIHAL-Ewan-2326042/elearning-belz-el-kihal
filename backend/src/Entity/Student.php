<?php

namespace App\Entity;

use App\Repository\StudentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;

#[ORM\Entity(repositoryClass: StudentRepository::class)]
#[ApiResource]
class Student extends User
{
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $studentNumber = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $enrollmentDate = null;

    /** @var Collection<int, QuizAttempt> */
    #[ORM\OneToMany(mappedBy: 'student', targetEntity: QuizAttempt::class)]
    private Collection $quizAttempts;

    public function __construct()
    {
        $this->quizAttempts = new ArrayCollection();
        $this->setRoles(['ROLE_STUDENT']);
    }

    public function getStudentNumber(): ?string
    {
        return $this->studentNumber;
    }

    public function setStudentNumber(?string $studentNumber): static
    {
        $this->studentNumber = $studentNumber;
        return $this;
    }

    public function getEnrollmentDate(): ?\DateTimeInterface
    {
        return $this->enrollmentDate;
    }

    public function setEnrollmentDate(?\DateTimeInterface $enrollmentDate): static
    {
        $this->enrollmentDate = $enrollmentDate;
        return $this;
    }

    /** @return Collection<int, QuizAttempt> */
    public function getQuizAttempts(): Collection
    {
        return $this->quizAttempts;
    }

    public function addQuizAttempt(QuizAttempt $quizAttempt): static
    {
        if (!$this->quizAttempts->contains($quizAttempt)) {
            $this->quizAttempts->add($quizAttempt);
            $quizAttempt->setStudent($this);
        }
        return $this;
    }

    public function removeQuizAttempt(QuizAttempt $quizAttempt): static
    {
        if ($this->quizAttempts->removeElement($quizAttempt)) {
            if ($quizAttempt->getStudent() === $this) {
                $quizAttempt->setStudent(null);
            }
        }
        return $this;
    }
}
