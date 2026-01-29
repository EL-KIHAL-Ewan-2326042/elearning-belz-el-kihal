<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Psr\Log\LoggerInterface;

class AiQuizService
{
    public function __construct(
        private HttpClientInterface $client,
        private LoggerInterface $logger,
        #[Autowire('%env(default::KIMI_API_KEY)%')] 
        private ?string $kimiApiKey = null,
        #[Autowire('%env(default::MISTRAL_API_KEY)%')] 
        private ?string $mistralApiKey = null
    ) {
    }

    public function generateQuizFromText(string $text, array $counts = ['total' => 5]): array
    {
        $prompt = $this->buildPrompt($text, $counts);
        
        // Try Kimi first if key is available
        if (!empty($this->kimiApiKey)) {
            try {
                $this->logger->info('Attempting QCM generation with Kimi API');
                return $this->callKimiApi($prompt);
            } catch (\Exception $e) {
                $this->logger->warning('Kimi API failed, falling back to Mistral: ' . $e->getMessage());
            }
        }
        
        // Fallback to Mistral
        if (!empty($this->mistralApiKey)) {
            try {
                $this->logger->info('Attempting QCM generation with Mistral API');
                return $this->callMistralApi($prompt);
            } catch (\Exception $e) {
                throw new \RuntimeException("Erreur lors de la génération du QCM (Mistral): " . $e->getMessage());
            }
        }
        
        throw new \RuntimeException('Aucune clé API configurée (KIMI_API_KEY ou MISTRAL_API_KEY).');
    }

    private function buildPrompt(string $text, array $counts): string
    {
        $total = $counts['total'] ?? 5;
        $trueFalse = $counts['true_false'] ?? 0;
        $mcqSingle = $counts['mcq_single'] ?? 0;
        $mcqMultiple = $counts['mcq_multiple'] ?? 0;
        
        // If specific counts aren't provided or don't match total, default to generic instruction
        $distributionInstruction = "";
        if (($trueFalse + $mcqSingle + $mcqMultiple) > 0) {
            $distributionInstruction = "Tu dois générer exactement :
- $trueFalse questions de type Vrai/Faux
- $mcqSingle questions à choix multiple (une seule bonne réponse)
- $mcqMultiple questions à choix multiple (plusieurs bonnes réponses possibles, minimum 2 bonnes réponses)";
        }

        return <<<EOT
Tu es un expert pédagogique. Génère un QCM de $total questions basé sur le texte ci-dessous.
$distributionInstruction

Le format de sortie DOIT être un JSON valide respectant cette structure exacte :
{
    "questions": [
        {
            "content": "La question ici ?",
            "type": "multiple_choice", // ou "true_false"
            "answers": [
                {"content": "Réponse A", "isCorrect": true},
                {"content": "Réponse B", "isCorrect": false},
                {"content": "Réponse C", "isCorrect": false}
            ]
        }
    ]
}

Pour les questions à choix multiples (plusieurs réponses), assure-toi qu'il y a bien plusieurs réponses avec "isCorrect": true.

Texte à analyser :
$text
EOT;
    }

    private function callKimiApi(string $prompt): array
    {
        $response = $this->client->request('POST', 'https://api.moonshot.ai/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->kimiApiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'moonshot-v1-8k',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.7,
            ],
        ]);

        return $this->parseApiResponse($response->toArray());
    }

    private function callMistralApi(string $prompt): array
    {
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

        return $this->parseApiResponse($response->toArray());
    }

    private function parseApiResponse(array $content): array
    {
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
            // Ensure type is set if missing
            foreach ($quizData['questions'] as &$q) {
                if (!isset($q['type'])) {
                    $q['type'] = 'multiple_choice'; // Default
                }
            }
            return $quizData['questions'];
        }
        
        // Fallback: if it's a direct array of questions
        if (array_is_list($quizData)) {
            return $quizData;
        }

        throw new \RuntimeException('Format JSON invalide reçu de l\'IA (clé "questions" manquante)');
    }
}
