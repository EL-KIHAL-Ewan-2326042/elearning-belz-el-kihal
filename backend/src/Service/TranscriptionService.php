<?php

namespace App\Service;

use Smalot\PdfParser\Parser;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Psr\Log\LoggerInterface;

class TranscriptionService
{
    private string $projectDir;

    public function __construct(
        #[Autowire('%kernel.project_dir%')] string $projectDir,
        private HttpClientInterface $client,
        private LoggerInterface $logger,
        #[Autowire('%env(default::KIMI_API_KEY)%')] private ?string $kimiApiKey = null,
        #[Autowire('%env(default::MISTRAL_API_KEY)%')] private ?string $mistralApiKey = null
    ) {
        $this->projectDir = $projectDir;
    }

    public function extractPdfText(string $pdfPath): string
    {
        $parser = new Parser();
        try {
            $pdf = $parser->parseFile($pdfPath);
            return $pdf->getText();
        } catch (\Exception $e) {
            return "Erreur lors de l'extraction du PDF : " . $e->getMessage();
        }
    }

    public function transcribe(string $inputPath, ?string $progressId = null): string
    {
        $audioPath = $this->convertVideoToAudio($inputPath);

        try {
            // 1. Try Kimi Audio API
            if ($this->kimiApiKey) {
                try {
                    $this->logger->info('Attempting transcription with Kimi API');
                    return $this->callTranscriptionApi(
                        'https://api.moonshot.ai/v1/audio/transcriptions',
                        $this->kimiApiKey,
                        $audioPath,
                        'kimi-audio',
                        $progressId
                    );
                } catch (\Exception $e) {
                    $this->logger->warning('Kimi transcription failed, falling back to Mistral: ' . $e->getMessage());
                    file_put_contents($this->projectDir . '/api_error.log', "Kimi Error: " . $e->getMessage() . "\n", FILE_APPEND);
                }
            }

            // 2. Fallback to Mistral Voxtral API
            if ($this->mistralApiKey) {
                try {
                    $this->logger->info('Attempting transcription with Mistral API (Voxtral)');
                    return $this->callTranscriptionApi(
                        'https://api.mistral.ai/v1/audio/transcriptions',
                        $this->mistralApiKey,
                        $audioPath,
                        'voxtral-mini-latest',
                        $progressId
                    );
                } catch (\Exception $e) {
                    $this->logger->error('Mistral transcription failed: ' . $e->getMessage());
                    file_put_contents($this->projectDir . '/api_error.log', "Mistral Error: " . $e->getMessage() . "\n", FILE_APPEND);
                }
            }

            throw new \RuntimeException("Aucune API disponible pour la transcription (Kimi ou Mistral)");

        } finally {
            // Cleanup temporary audio file
            if ($audioPath !== $inputPath && file_exists($audioPath)) {
                try {
                    @unlink($audioPath);
                } catch (\Throwable $e) {
                    // Ignore cleanup error
                }
            }
        }
    }

    private function callTranscriptionApi(string $url, string $apiKey, string $filePath, string $model, ?string $progressId): string
    {
        if ($progressId && file_exists(sys_get_temp_dir() . '/transcription_' . $progressId . '.json')) {
            file_put_contents(sys_get_temp_dir() . '/transcription_' . $progressId . '.json', json_encode(['progress' => 50]));
        }

        $formFields = [
            'file' => DataPart::fromPath($filePath),
            'model' => $model,
            'language' => 'fr',
            'response_format' => 'json'
        ];
        $formData = new FormDataPart($formFields);

        $response = $this->client->request('POST', $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'multipart/form-data',
            ] + $formData->getPreparedHeaders()->toArray(),
            'body' => $formData->bodyToIterable(),
        ]);

        if ($progressId && file_exists(sys_get_temp_dir() . '/transcription_' . $progressId . '.json')) {
            file_put_contents(sys_get_temp_dir() . '/transcription_' . $progressId . '.json', json_encode(['progress' => 100]));
        }

        $data = $response->toArray();
        return $data['text'] ?? '';
    }

    private function convertVideoToAudio(string $filePath): string
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        // If already audio, return as is (strict WAV for Whisper-like APIs is usually better but MP3/M4A often work)
        if (in_array($extension, ['wav', 'mp3', 'm4a'])) {
            return $filePath;
        }

        $outputPath = sys_get_temp_dir() . '/' . uniqid('audio_', true) . '.wav';

        // FFMpeg command to extract audio: 16kHz mono WAV is standard for most STT APIs
        $cmd = "ffmpeg -y -i \"$filePath\" -ar 16000 -ac 1 -c:a pcm_s16le \"$outputPath\" 2>&1";

        exec($cmd, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \RuntimeException("FFMpeg conversion failed: " . implode("\n", $output));
        }

        return $outputPath;
    }
}
