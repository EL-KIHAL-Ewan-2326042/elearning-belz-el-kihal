<?php

namespace App\Repository;

use App\Entity\QuizAttempt;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<QuizAttempt>
 */
class QuizAttemptRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QuizAttempt::class);
    }

    public function findGlobalStatsByStudent(\App\Entity\Student $student): array
    {
        $qb = $this->createQueryBuilder('qa')
            ->select('AVG(qa.score * 20 / qa.maxScore) as avgScore')
            ->addSelect('COUNT(qa.id) as totalAttempts')
            ->addSelect('SUM(CASE WHEN (qa.score * 100 / qa.maxScore) >= 50 THEN 1 ELSE 0 END) as successCount')
            ->where('qa.student = :student')
            ->setParameter('student', $student);

        return $qb->getQuery()->getSingleResult();
    }

    public function findContextualStatsByStudentAndTeacher(\App\Entity\Student $student, \App\Entity\Teacher $teacher): array
    {
        $qb = $this->createQueryBuilder('qa')
            ->join('qa.quiz', 'q')
            ->join('q.course', 'c')
            ->select('AVG(qa.score * 20 / qa.maxScore) as avgScore')
            ->addSelect('COUNT(qa.id) as totalAttempts')
            ->addSelect('SUM(CASE WHEN (qa.score * 100 / qa.maxScore) >= 50 THEN 1 ELSE 0 END) as successCount')
            ->where('qa.student = :student')
            ->andWhere('c.teacher = :teacher')
            ->setParameter('student', $student)
            ->setParameter('teacher', $teacher);

        return $qb->getQuery()->getSingleResult();
    }

    public function findAttemptsWithDetails(\App\Entity\Student $student, ?\App\Entity\Teacher $teacher = null): array
    {
        $qb = $this->createQueryBuilder('qa')
            ->join('qa.quiz', 'q')
            ->join('q.course', 'c')
            ->leftJoin('c.teacher', 't')
            ->addSelect('q', 'c', 't')
            ->where('qa.student = :student')
            ->setParameter('student', $student)
            ->orderBy('qa.submittedAt', 'ASC');

        if ($teacher) {
            $qb->andWhere('c.teacher = :teacher')
               ->setParameter('teacher', $teacher);
        }

        return $qb->getQuery()->getResult();
    }
}
