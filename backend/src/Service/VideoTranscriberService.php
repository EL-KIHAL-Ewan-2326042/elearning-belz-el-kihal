<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class VideoTranscriberService
{
    public function __construct(
        private HttpClientInterface $client,
        #[Autowire('%env(default::OPENAI_API_KEY)%')]
        private ?string $openAiApiKey = null
    ) {
    }

    public function transcribe(string $filePath): string
    {
        // Si pas de clé API OpenAI, on retourne un placeholder pour l'instant
        // car Mistral ne fait pas de Speech-to-Text.
        if (empty($this->openAiApiKey)) {
            return "[Transcription indisponible : Clé API OpenAI manquante pour Whisper]";
        }

        try {
            // Utilisation de l'API Whisper d'OpenAI
            $response = $this->client->request('POST', 'https://api.openai.com/v1/audio/transcriptions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->openAiApiKey,
                ],
                'body' => [
                    'file' => fopen($filePath, 'r'),
                    'model' => 'whisper-1',
                ],
            ]);

            $content = $response->toArray();
            return $content['text'] ?? '';

        } catch (\Exception $e) {
            // En cas d'erreur (fichier trop gros, format non supporté, etc.)
            // On log l'erreur et on retourne une chaîne vide ou un message
            return "[Erreur lors de la transcription de la vidéo : " . $e->getMessage() . "]";
        }
    }
}
