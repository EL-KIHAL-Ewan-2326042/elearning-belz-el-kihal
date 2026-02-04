<?php

use App\Entity\QuizAttempt;
use App\Entity\Student;
use App\Kernel;

require_once __DIR__.'/vendor/autoload_runtime.php';

return function (array $context) {
    $kernel = new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);

    return new class($kernel) {
        public function __construct(private Kernel $kernel)
        {
            $this->kernel->boot();
            $this->run();
        }

        public function run(): void
        {
            $container = $this->kernel->getContainer();
            $em = $container->get('doctrine')->getManager();
            
            echo "--- TESTING DOCTRINE RELATIONS ---\n";
            
            // 1. Fetch Student directly
            $studentRepo = $em->getRepository(Student::class);
            $student = $studentRepo->find(10);
            
            if (!$student) {
                echo "ERROR: Student ID 10 not found via StudentRepository!\n";
            } else {
                echo "SUCCESS: Found Student ID 10: " . $student->getEmail() . " (Class: " . get_class($student) . ")\n";
                echo "Roles: " . implode(', ', $student->getRoles()) . "\n";
            }

            // 2. Fetch QuizAttempts via Student
            if ($student) {
                echo "Checking attempts via Student->getQuizAttempts():\n";
                foreach ($student->getQuizAttempts() as $attempt) {
                    echo "- Attempt ID: " . $attempt->getId() . " (Score: " . $attempt->getScore() . ")\n";
                }
            }
            
            // 3. Fetch QuizAttempts directly via Repository
            $attemptRepo = $em->getRepository(QuizAttempt::class);
            $attempts = $attemptRepo->findAll();
            
            echo "Checking all attempts via QuizAttemptRepository:\n";
            foreach ($attempts as $attempt) {
                $s = $attempt->getStudent();
                echo "- Attempt ID: " . $attempt->getId() . " -> Student: " . ($s ? "ID " . $s->getId() . " (" . get_class($s) . ")" : "NULL") . "\n";
            }
            
            echo "--- END TEST ---\n";
        }
    };
};
