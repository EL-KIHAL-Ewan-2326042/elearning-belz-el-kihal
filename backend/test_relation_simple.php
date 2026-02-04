<?php

use App\Kernel;
use App\Entity\Student;
use App\Entity\QuizAttempt;
use Doctrine\ORM\EntityManagerInterface;

require_once __DIR__.'/vendor/autoload.php';

$kernel = new Kernel($_SERVER['APP_ENV'] ?? 'dev', (bool) ($_SERVER['APP_DEBUG'] ?? true));
$kernel->boot();

$container = $kernel->getContainer();
/** @var EntityManagerInterface $em */
$em = $container->get('doctrine')->getManager();

echo "--- TESTING DOCTRINE RELATIONS ---\n";

// 1. Fetch Student directly
$studentRepo = $em->getRepository(Student::class);
$student = $studentRepo->find(10);

if (!$student) {
    echo "ERROR: Student ID 10 not found via StudentRepository!\n";
} else {
    echo "SUCCESS: Found Student ID 10: " . $student->getUserIdentifier() . " (Class: " . get_class($student) . ")\n";
    echo "Roles: " . implode(', ', $student->getRoles()) . "\n";
}

// 2. Fetch QuizAttempts via Relation
if ($student) {
    echo "Checking attempts via Student->getQuizAttempts():\n";
    foreach ($student->getQuizAttempts() as $attempt) {
        echo "- Attempt ID: " . $attempt->getId() . " (Score: " . $attempt->getScore() . ")\n";
    }
}

// 3. Fetch QuizAttempts via Repository
$attemptRepo = $em->getRepository(QuizAttempt::class);
$attempts = $attemptRepo->findAll();

echo "Checking all attempts via QuizAttemptRepository:\n";
foreach ($attempts as $attempt) {
    $s = $attempt->getStudent();
    echo "- Attempt ID: " . $attempt->getId() . " -> Student: " . ($s ? "ID " . $s->getId() . " (" . get_class($s) . ")" : "NULL") . "\n";
}

echo "--- END TEST ---\n";
