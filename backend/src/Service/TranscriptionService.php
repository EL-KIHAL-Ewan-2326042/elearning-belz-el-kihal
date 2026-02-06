<?php

namespace App\Service;

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
        if (empty($this->mistralApiKey)) {
            throw new \RuntimeException('Clé API Mistral manquante pour la transcription.');
        }

        try {
            // Mise à jour de la progression
            if ($progressId && file_exists(sys_get_temp_dir() . '/transcription_' . $progressId . '.json')) {
                file_put_contents(sys_get_temp_dir() . '/transcription_' . $progressId . '.json', json_encode(['progress' => 25]));
            }

            // Renommer le fichier .mp4 en .m4a pour l'API Mistral
            $filePath = $this->prepareFileForMistral($inputPath);

            if ($progressId && file_exists(sys_get_temp_dir() . '/transcription_' . $progressId . '.json')) {
                file_put_contents(sys_get_temp_dir() . '/transcription_' . $progressId . '.json', json_encode(['progress' => 50]));
            }

            // Appel à l'API Mistral pour la transcription
            $formFields = [
                'file' => DataPart::fromPath($filePath, 'audio.m4a', 'audio/m4a'),
                'model' => 'mistral-ocr-latest',
            ];
            $formData = new FormDataPart($formFields);

            $response = $this->client->request('POST', 'https://api.mistral.ai/v1/audio/transcriptions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->mistralApiKey,
                ] + $formData->getPreparedHeaders()->toArray(),
                'body' => $formData->bodyToIterable(),
            ]);

            if ($progressId && file_exists(sys_get_temp_dir() . '/transcription_' . $progressId . '.json')) {
                file_put_contents(sys_get_temp_dir() . '/transcription_' . $progressId . '.json', json_encode(['progress' => 100]));
            }

            $data = $response->toArray();
            $text = $data['text'] ?? '';

            // Nettoyage du fichier temporaire si différent de l'original
            if ($filePath !== $inputPath && file_exists($filePath)) {
                @unlink($filePath);
            }

            return $text;
        } catch (\Exception $e) {
            // Nettoyage en cas d'erreur
            if (isset($filePath) && $filePath !== $inputPath && file_exists($filePath)) {
                @unlink($filePath);
            }
            throw $e;
        }
    }

    /**
     * Prépare le fichier pour l'API Mistral.
     * Renomme les fichiers .mp4 en .m4a (même contenu, extension différente)
     */
    private function prepareFileForMistral(string $filePath): string
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        // Si c'est déjà un format supporté directement, retourner tel quel
        if (in_array($extension, ['m4a', 'mp3', 'wav', 'ogg'])) {
            return $filePath;
        }

        // Pour les fichiers .mp4, les renommer en .m4a
        if ($extension === 'mp4') {
            $tempPath = sys_get_temp_dir() . '/' . uniqid('audio_', true) . '.m4a';
            
            // Copier le fichier avec la nouvelle extension
            if (!copy($filePath, $tempPath)) {
                throw new \RuntimeException("Impossible de copier le fichier pour la préparation Mistral.");
            }
            
            return $tempPath;
        }

        // Pour les autres formats vidéo, conversion nécessaire via ffmpeg
        $tempPath = sys_get_temp_dir() . '/' . uniqid('audio_', true) . '.m4a';
        
        // FFMpeg commande pour extraire l'audio au format m4a
        $cmd = "ffmpeg -y -i \"$filePath\" -vn -c:a aac -b:a 128k \"$tempPath\" 2>&1";
        
        exec($cmd, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new \RuntimeException("FFMpeg conversion failed: " . implode("\n", $output));
        }

        return $tempPath;
    }
}
