<?php

namespace App\Service;

use Codewithkyrian\Whisper\Whisper;
use Smalot\PdfParser\Parser;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;

class TranscriptionService
{
    private string $projectDir;

    public function __construct(
        #[Autowire('%kernel.project_dir%')] string $projectDir,
        private HttpClientInterface $client,
        #[Autowire('%env(default::GROQ_API_KEY)%')] private ?string $groqApiKey = null
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
            // 🚀 TURBO MODE: Use Groq API if key is present
            if ($this->groqApiKey) {
                try {
                    if ($progressId && file_exists(sys_get_temp_dir() . '/transcription_' . $progressId . '.json')) {
                        file_put_contents(sys_get_temp_dir() . '/transcription_' . $progressId . '.json', json_encode(['progress' => 50]));
                    }

                    $formFields = [
                        'file' => DataPart::fromPath($audioPath),
                        'model' => 'whisper-large-v3',
                        'language' => 'fr',
                        'response_format' => 'json'
                    ];
                    $formData = new FormDataPart($formFields);

                    $response = $this->client->request('POST', 'https://api.groq.com/openai/v1/audio/transcriptions', [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $this->groqApiKey,
                            'Content-Type' => 'multipart/form-data',
                        ] + $formData->getPreparedHeaders()->toArray(),
                        'body' => $formData->bodyToIterable(),
                    ]);

                    if ($progressId && file_exists(sys_get_temp_dir() . '/transcription_' . $progressId . '.json')) {
                        file_put_contents(sys_get_temp_dir() . '/transcription_' . $progressId . '.json', json_encode(['progress' => 100]));
                    }

                    $data = $response->toArray();
                    return $data['text'] ?? '';
                } catch (\Exception $e) {
                    // Fallback to local if API fails
                    file_put_contents($this->projectDir . '/api_error.log', "Groq Error: " . $e->getMessage() . "\n", FILE_APPEND);
                }
            }

            // 🐢 LOCAL MODE (Fallback)
            $params = null;
            if ($progressId) {
                $params = \Codewithkyrian\Whisper\WhisperFullParams::default()
                    ->withLanguage('fr') // Force French to skip detection (saves time)
                    ->withNThreads(8)    // Optimized for i5-12450H (8 Cores)
                    ->withProgressCallback(function(int $progress) use ($progressId) {
                        $file = sys_get_temp_dir() . '/transcription_' . $progressId . '.json';
                        file_put_contents($file, json_encode(['progress' => $progress]));
                    });
            } else {
                 // Even without progress ID, optimize params
                 $params = \Codewithkyrian\Whisper\WhisperFullParams::default()
                    ->withLanguage('fr')
                    ->withNThreads(8);
            }

            // "base" is better for French than "tiny", while still fast enough on i5.
            $whisper = Whisper::fromPretrained('base', baseDir: $this->projectDir . '/var/whisper_models', params: $params);
            $segments = $whisper->transcribe($audioPath);
            
            $text = '';
            foreach ($segments as $segment) {
                $text .= $segment->text;
            }

            return trim($text);
        } finally {
            // Cleanup temporary audio file
            if ($audioPath !== $inputPath && file_exists($audioPath)) {
                // Windows fix: Force unset whisper to release file handle
                unset($whisper);
                // Windows fix: Suppress errors if file is locked
                try {
                    @unlink($audioPath);
                } catch (\Throwable $e) {
                    // Ignore cleanup error
                }
            }
        }
    }

    private function convertVideoToAudio(string $filePath): string
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        // If already audio, return as is (or strict check for wav)
        if (in_array($extension, ['wav', 'mp3', 'm4a'])) {
            return $filePath;
        }

        $outputPath = sys_get_temp_dir() . '/' . uniqid('audio_', true) . '.wav';
        
        // FFMpeg command to extract audio: 16kHz mono WAV is best for Whisper
        $cmd = "ffmpeg -y -i \"$filePath\" -ar 16000 -ac 1 -c:a pcm_s16le \"$outputPath\" 2>&1";
        
        exec($cmd, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new \RuntimeException("FFMpeg conversion failed: " . implode("\n", $output));
        }

        return $outputPath;
    }
}
