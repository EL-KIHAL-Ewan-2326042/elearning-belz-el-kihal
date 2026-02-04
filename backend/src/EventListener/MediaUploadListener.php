<?php

namespace App\EventListener;

use App\Entity\Document;
use App\Entity\Video;
use App\Service\TranscriptionService;
use Doctrine\ORM\EntityManagerInterface;
use Vich\UploaderBundle\Event\Event;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

use Symfony\Component\HttpFoundation\RequestStack;

#[AsEventListener(event: 'vich_uploader.post_upload')]
class MediaUploadListener
{
    public function __construct(
        private TranscriptionService $transcriptionService,
        private EntityManagerInterface $em,
        private RequestStack $requestStack
    ) {
    }

    public function onVichUploaderPostUpload(Event $event): void
    {
        // Transcription can be long, so we disable the time limit for this request
        set_time_limit(0);
        
        $object = $event->getObject();
        $mapping = $event->getMapping();

        // Check for progress ID in request
        $request = $this->requestStack->getCurrentRequest();
        $progressId = $request ? $request->request->get('transcription_progress_id') : null;

        // CRITICAL: Release session lock so that concurrent progress requests are not blocked
        if ($request && $request->hasSession()) {
            $request->getSession()->save();
        }

        // 1. Handle Video Transcription
        if ($object instanceof Video && $mapping->getMappingName() === 'course_videos') {
            $fileName = $object->getFileName();
            if ($fileName) {
                // Determine absolute path
                $filePath = $mapping->getUploadDestination() . '/' . $fileName;

                if (file_exists($filePath)) {
                    $log = __DIR__ . '/../../transcription_listener.log';
                    file_put_contents($log, date('H:i:s') . " - STARTING transcription for $fileName\n", FILE_APPEND);
                   
                    try {
                        // Pass progress ID if available
                        $text = $this->transcriptionService->transcribe($filePath, $progressId);
                        file_put_contents($log, date('H:i:s') . " - Transcription DONE. Length: " . strlen($text) . "\n", FILE_APPEND);
                        
                        $object->setTranscription($text);
                        // No need to persist/flush here usually if it's pre_persist, but this is post_upload
                        // so the entity is already managed. We just need to flush.
                        // $this->em->flush(); 
                        file_put_contents($log, date('H:i:s') . " - Flush SKIPPED (handled by global flush).\n", FILE_APPEND); 
                        file_put_contents($log, date('H:i:s') . " - Flush DONE.\n", FILE_APPEND);
                    } catch (\Exception $e) {
                         file_put_contents($log, date('H:i:s') . " - ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n", FILE_APPEND);
                    }
                }
            }
        }

        // 2. Handle PDF Text Extraction
        if ($object instanceof Document && $mapping->getMappingName() === 'course_documents') {
            $fileName = $object->getFileName();
            if ($fileName) {
                $filePath = $mapping->getUploadDestination() . '/' . $fileName;
                
                // Only process PDFs
                if (file_exists($filePath) && str_ends_with(strtolower($fileName), '.pdf')) {
                    try {
                        $text = $this->transcriptionService->extractPdfText($filePath);
                        $object->setContent($text);
                        // $this->em->flush();
                    } catch (\Exception $e) {
                        // Silent fail
                    }
                }
            }
        }
    }
}
