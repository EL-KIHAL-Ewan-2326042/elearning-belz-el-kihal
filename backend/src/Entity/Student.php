<?php

namespace App\Entity;

use App\Repository\StudentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: StudentRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(),
        new Patch(),
        new Delete(),
    ],
    normalizationContext: ['groups' => ['user:read']],
    denormalizationContext: ['groups' => ['user:write']],
)]
class Student extends User
{
    /** @var Collection<int, QuizAttempt> */
    #[ORM\OneToMany(mappedBy: 'student', targetEntity: QuizAttempt::class)]
    private Collection $quizAttempts;

    public function __construct()
    {
        $this->quizAttempts = new ArrayCollection();
        $this->setRoles(['ROLE_STUDENT']);
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
