<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class AiQuizService
{
    public function __construct(
        private HttpClientInterface $client,
        #[Autowire('%env(MISTRAL_API_KEY)%')]
        private string $mistralApiKey
    ) {
    }

    public function generateQuizFromText(string $text, int $questionCount = 5): array
    {
        if (empty($this->mistralApiKey)) {
            throw new \RuntimeException('La clé API Mistral n\'est pas configurée.');
        }

        $prompt = <<<EOT
Tu es un expert pédagogique. Génère un QCM de $questionCount questions basé sur le texte ci-dessous.
Le format de sortie DOIT être un JSON valide respectant cette structure exacte :
{
    "questions": [
        {
            "content": "La question ici ?",
            "answers": [
                {"content": "Réponse A", "isCorrect": true},
                {"content": "Réponse B", "isCorrect": false},
                {"content": "Réponse C", "isCorrect": false}
            ]
        }
    ]
}

Texte à analyser :
$text
EOT;

        try {
            $response = $this->client->request('POST', 'https://api.mistral.ai/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->mistralApiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'model' => 'mistral-tiny',
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt]
                    ],
                    'temperature' => 0.7,
                    'response_format' => ['type' => 'json_object'] 
                ],
            ]);

            $content = $response->toArray();
            $rawContent = $content['choices'][0]['message']['content'] ?? '';
            
            $jsonString = str_replace(['```json', '```'], '', $rawContent);
            $quizData = json_decode($jsonString, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                 if (preg_match('/\{.*\}/s', $rawContent, $matches)) {
                    $quizData = json_decode($matches[0], true);
                }
            }

            if (!$quizData) {
                throw new \RuntimeException('Erreur de décodage JSON depuis l\'IA');
            }

            // Ensure we have the questions array
            if (isset($quizData['questions']) && is_array($quizData['questions'])) {
                return $quizData['questions'];
            }
            
            // Fallback: if it's a direct array of questions
            if (array_is_list($quizData)) {
                return $quizData;
            }

            throw new \RuntimeException('Format JSON invalide reçu de l\'IA (clé "questions" manquante)');

        } catch (\Exception $e) {
            throw new \RuntimeException("Erreur lors de la génération du QCM: " . $e->getMessage());
        }
    }
}
