<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;

class VideoTranscriberService
{
    public function __construct(
        private HttpClientInterface $client,
        #[Autowire('%env(default::MISTRAL_API_KEY)%')]
        private ?string $mistralApiKey = null
    ) {
    }

    public function transcribe(string $filePath): string
    {
        if (empty($this->mistralApiKey)) {
            return "[Transcription indisponible : Clé API Mistral manquante]";
        }

        try {
            // Préparer le fichier pour Mistral (renommer mp4 en m4a si nécessaire)
            $preparedFilePath = $this->prepareFileForMistral($filePath);

            // Utilisation de l'API Mistral pour la transcription audio
            $formFields = [
                'file' => DataPart::fromPath($preparedFilePath, 'audio.m4a', 'audio/m4a'),
                'model' => 'mistral-ocr-latest',
            ];
            $formData = new FormDataPart($formFields);

            $response = $this->client->request('POST', 'https://api.mistral.ai/v1/audio/transcriptions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->mistralApiKey,
                ] + $formData->getPreparedHeaders()->toArray(),
                'body' => $formData->bodyToIterable(),
            ]);

            $content = $response->toArray();
            
            // Nettoyer le fichier temporaire si créé
            if ($preparedFilePath !== $filePath && file_exists($preparedFilePath)) {
                @unlink($preparedFilePath);
            }

            return $content['text'] ?? '';

        } catch (\Exception $e) {
            // Nettoyer en cas d'erreur
            if (isset($preparedFilePath) && $preparedFilePath !== $filePath && file_exists($preparedFilePath)) {
                @unlink($preparedFilePath);
            }
            
            return "[Erreur lors de la transcription de la vidéo : " . $e->getMessage() . "]";
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
