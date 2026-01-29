<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class TranscriptionProgressController extends AbstractController
{
    #[Route('/api/transcription-progress/{id}', name: 'api_transcription_progress', methods: ['GET'])]
    public function getProgress(string $id): JsonResponse
    {
        $file = sys_get_temp_dir() . '/transcription_' . $id . '.json';

        if (!file_exists($file)) {
            // Either not started or finished and cleaned up (or ID invalid)
            // returning 0 or 100 depends on context, but here maybe just null
            return new JsonResponse(['progress' => null]);
        }

        $data = json_decode(file_get_contents($file), true);
        return new JsonResponse($data);
    }
}
