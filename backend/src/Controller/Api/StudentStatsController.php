<?php

namespace App\Controller\Api;

use App\Repository\QuizAttemptRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
class StudentStatsController extends AbstractController
{
    public function __construct(
        private QuizAttemptRepository $attemptRepository
    ) {}

    #[Route('/me/stats', name: 'api_me_stats', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getMyStats(): JsonResponse
    {
        $user = $this->getUser();
        
        // 1. Global Stats (Aggregate SQL)
        $globalStats = $this->attemptRepository->findGlobalStatsByStudent($user);
        
        // 2. Course Distribution (Aggregate SQL)
        $courseDistribution = $this->attemptRepository->findCourseDistributionByStudent($user);

        // 3. Evolution (Last 10 attempts for chart)
        $evolutionRaw = $this->attemptRepository->findEvolutionStatsByStudent($user, 10);
        $evolution = [];
        // We get DESC, but charts usually want ASC (chronological)
        // So we process then reverse, or reverse then process.
        // Let's process then reverse to match previous output structure
        foreach ($evolutionRaw as $row) {
            $score20 = ($row['score'] / max($row['maxScore'], 1)) * 20;
            $evolution[] = [
                'date' => $row['submittedAt']->format('d/m H:i'),
                'score' => round($score20, 1),
                'quiz' => $row['quizTitle']
            ];
        }
        $evolution = array_reverse($evolution);

        // 4. Recent Attempts (Last 5)
        $recentRaw = $this->attemptRepository->findRecentAttempts($user, 5);
        $recentAttempts = [];
        foreach ($recentRaw as $row) {
             $score20 = ($row['score'] / max($row['maxScore'], 1)) * 20;
             $recentAttempts[] = [
                'quizTitle' => $row['quizTitle'],
                'courseTitle' => $row['courseTitle'],
                'score' => round($score20, 1),
                'date' => $row['submittedAt']->format('d/m/Y H:i')
             ];
        }

        // Prepare response
        $totalAttempts = (int) $globalStats['totalAttempts'];
        // avgScore from SQL is already 0-20 scale?
        // SQL: AVG(qa.score * 20 / qa.maxScore)
        $avgScore = $globalStats['avgScore'] !== null ? round((float)$globalStats['avgScore'], 1) : 0;
        $successCount = (int) $globalStats['successCount'];
        $successRate = $totalAttempts > 0 ? round(($successCount / $totalAttempts) * 100) : 0;

        return $this->json([
            'avgScore' => $avgScore,
            'successRate' => $successRate,
            'totalAttempts' => $totalAttempts,
            'coursesCount' => count($courseDistribution),
            'evolution' => $evolution,
            'courseDistribution' => $courseDistribution,
            'recentAttempts' => $recentAttempts
        ]);
    }
}
