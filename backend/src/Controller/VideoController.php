<?php

namespace App\Controller;

use App\Entity\Video;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/teacher/video')]
#[IsGranted('ROLE_TEACHER')]
class VideoController extends AbstractController
{
    #[Route('/{id}/transcription', name: 'video_update_transcription', methods: ['POST'])]
    public function updateTranscription(Request $request, Video $video, EntityManagerInterface $entityManager): Response
    {
        // Ensure the teacher owns the course (optional, but good practice)
        // For now, consistent with other controllers, we trust ROLE_TEACHER or rely on Voter if stricter
        // security is needed. Assuming simple role check for now as per other controllers.
        
        $transcription = $request->request->get('transcription');
        $video->setTranscription($transcription);
        $video->setTranscriptionUpdatedAt(new \DateTime());

        $entityManager->flush();

        $this->addFlash('success', 'Transcription mise à jour avec succès.');

        return $this->redirectToRoute('course_show', ['id' => $video->getCourse()->getId()]);
    }
}
