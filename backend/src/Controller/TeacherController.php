<?php

namespace App\Controller;

use App\Repository\TeacherRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/teachers')]
#[IsGranted('ROLE_TEACHER')]
class TeacherController extends AbstractController
{
    #[Route('', name: 'teacher_index', methods: ['GET'])]
    public function index(TeacherRepository $teacherRepository): Response
    {
        $teachers = $teacherRepository->findAll();
        
        return $this->render('teacher/index.html.twig', [
            'teachers' => $teachers,
        ]);
    }
    #[Route('/student/{id}/history', name: 'teacher_student_history', methods: ['GET'])]
    public function studentHistory(\App\Entity\Student $student, \App\Repository\QuizAttemptRepository $attemptRepository): Response
    {
        $attempts = $attemptRepository->findBy(['student' => $student], ['submittedAt' => 'DESC']);

        return $this->render('teacher/student_history.html.twig', [
            'student' => $student,
            'attempts' => $attempts,
        ]);
    }

    #[Route('/students', name: 'teacher_students_list', methods: ['GET'])]
    public function listStudents(\App\Repository\StudentRepository $studentRepository): Response
    {
        $students = $studentRepository->findAll();
        
        return $this->render('teacher/student_list.html.twig', [
            'students' => $students,
        ]);
    }

    #[Route('/students/{id}/analytics', name: 'teacher_student_analytics', methods: ['GET'])]
    public function studentAnalytics(\App\Entity\Student $student, \App\Repository\QuizAttemptRepository $attemptRepository): Response
    {
        /** @var \App\Entity\Teacher $teacher */
        $teacher = $this->getUser();
        
        // Use the Repository methods we created earlier (they work fine in PHP context too)
        // Just need to handle cases where no attempts exist to avoid errors in twig or aggregation
        
        try {
            $globalStats = $attemptRepository->findGlobalStatsByStudent($student);
        } catch (\Exception $e) {
             $globalStats = ['avgScore' => 0, 'totalAttempts' => 0, 'successCount' => 0];
        }

        try {
            $contextualStats = $attemptRepository->findContextualStatsByStudentAndTeacher($student, $teacher);
        } catch (\Exception $e) {
            $contextualStats = ['avgScore' => 0, 'totalAttempts' => 0, 'successCount' => 0];
        }
        
        $attempts = $attemptRepository->findAttemptsWithDetails($student);
        
        // Prepare Data for Charts (Global)
        $evolutionData = [];
        $courseDistribution = [];
        $profDistribution = [];
        
        foreach ($attempts as $attempt) {
            $course = $attempt->getQuiz()->getCourse();
             $date = $attempt->getSubmittedAt()->format('Y-m-d');
             $courseTitle = $course->getTitle();
             $teacherName = $course->getTeacher() ? $course->getTeacher()->getFirstName() . ' ' . $course->getTeacher()->getLastName() : 'Inconnu';
             
             // Evolution
            if (!isset($evolutionData[$date])) {
                $evolutionData[$date] = ['date' => $date, 'scoreSum' => 0, 'count' => 0];
            }
            $evolutionData[$date]['scoreSum'] += ($attempt->getScore() * 20 / $attempt->getMaxScore());
            $evolutionData[$date]['count']++;
            
            // Course Distribution (with teacher name)
            $courseLabel = $courseTitle . ' - ' . $teacherName;
             if (!isset($courseDistribution[$courseLabel])) {
                $courseDistribution[$courseLabel] = 0;
            }
            $courseDistribution[$courseLabel]++;

            // Professor Distribution
            if (!isset($profDistribution[$teacherName])) {
                $profDistribution[$teacherName] = 0;
            }
            $profDistribution[$teacherName]++;
        }
        
        $evolutionLabels = array_keys($evolutionData);
        $evolutionValues = array_map(function($item) {
            return round($item['scoreSum'] / $item['count'], 2);
        }, array_values($evolutionData));
        
        // Prepare Contextual Data
        $myCourseAttempts = array_filter($attempts, function($a) use ($teacher) {
            return $a->getQuiz()->getCourse()->getTeacher() && $a->getQuiz()->getCourse()->getTeacher()->getId() === $teacher->getId();
        });



        $myCoursesList = [];
        $contextualEvolutionData = [];
        $contextualCourseDistribution = [];
        
        foreach ($myCourseAttempts as $attempt) {
            $date = $attempt->getSubmittedAt()->format('Y-m-d');
            $courseTitle = $attempt->getQuiz()->getCourse()->getTitle();
            
            // Course List Table
             if (!isset($myCoursesList[$courseTitle])) {
                $myCoursesList[$courseTitle] = [
                    'id' => $attempt->getQuiz()->getCourse()->getId(),
                    'title' => $courseTitle,
                    'attempts' => 0,
                    'maxScore' => 0,
                    'lastAttempt' => null
                ];
            }
            $myCoursesList[$courseTitle]['attempts']++;
            $myCoursesList[$courseTitle]['maxScore'] = max($myCoursesList[$courseTitle]['maxScore'], ($attempt->getScore() * 20 / $attempt->getMaxScore()));
            $myCoursesList[$courseTitle]['lastAttempt'] = $attempt->getSubmittedAt();
            
            // Contextual Evolution
            if (!isset($contextualEvolutionData[$date])) {
                $contextualEvolutionData[$date] = ['date' => $date, 'scoreSum' => 0, 'count' => 0];
            }
            $contextualEvolutionData[$date]['scoreSum'] += ($attempt->getScore() * 20 / $attempt->getMaxScore());
            $contextualEvolutionData[$date]['count']++;
            
            // Contextual Distribution
             if (!isset($contextualCourseDistribution[$courseTitle])) {
                $contextualCourseDistribution[$courseTitle] = 0;
            }
            $contextualCourseDistribution[$courseTitle]++;
        }
        
        $contextualEvolutionLabels = array_keys($contextualEvolutionData);
        $contextualEvolutionValues = array_map(function($item) {
            return round($item['scoreSum'] / $item['count'], 2);
        }, array_values($contextualEvolutionData));

        return $this->render('teacher/student_analytics.html.twig', [
            'student' => $student,
            'globalStats' => $globalStats,
            'contextualStats' => $contextualStats,
            'evolutionLabels' => json_encode($evolutionLabels),
            'evolutionValues' => json_encode($evolutionValues),
            'courseLabels' => json_encode(array_keys($courseDistribution)),
            'courseValues' => json_encode(array_values($courseDistribution)),
            'profLabels' => json_encode(array_keys($profDistribution)),
            'profValues' => json_encode(array_values($profDistribution)),
            'contextualEvolutionLabels' => json_encode($contextualEvolutionLabels),
            'contextualEvolutionValues' => json_encode($contextualEvolutionValues),
            'contextualCourseLabels' => json_encode(array_keys($contextualCourseDistribution)),
            'contextualCourseValues' => json_encode(array_values($contextualCourseDistribution)),
            'myCourses' => $myCoursesList
        ]);
    }
}
