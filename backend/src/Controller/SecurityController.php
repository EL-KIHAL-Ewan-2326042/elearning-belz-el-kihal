<?php

namespace App\Controller;

use App\Entity\Teacher;
use App\Entity\Student;
use App\Form\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Entity\User;

class SecurityController extends AbstractController
{
    public function __construct(
        private JWTTokenManagerInterface $jwtManager,
        private TokenStorageInterface $tokenStorage
    ) {
    }

    /**
     * Route de connexion pour les professeurs via formulaire (fallback/session)
     * Redirige directement vers le panel professeur
     */
    #[Route(path: '/teacher/login', name: 'app_teacher_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('course_index');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/teacher/logout', name: 'app_teacher_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * Inscription professeur via formulaire (fallback/session)
     * Redirige directement vers le panel professeur après inscription
     */
    #[Route(path: '/teacher/register', name: 'app_teacher_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
        Security $security
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('course_index');
        }

        $teacher = new Teacher();
        $form = $this->createForm(RegistrationType::class, $teacher);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hashedPassword = $passwordHasher->hashPassword(
                $teacher,
                $form->get('plainPassword')->getData()
            );
            $teacher->setPassword($hashedPassword);

            $em->persist($teacher);
            $em->flush();

            // Auto-login after registration
            $security->login($teacher, 'form_login', 'main');
            
            $this->addFlash('success', 'Bienvenue ! Votre compte a été créé avec succès.');
            return $this->redirectToRoute('course_index');
        }

        return $this->render('security/register.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * API: Récupère l'utilisateur connecté via JWT
     * Cette route utilise le token JWT pour récupérer l'utilisateur
     */
    #[Route(path: '/api/me', name: 'api_me', methods: ['GET'])]
    public function me(): Response
    {
        // Récupérer l'utilisateur depuis le token JWT
        $token = $this->tokenStorage->getToken();
        
        if (!$token) {
            return $this->json(['message' => 'Non authentifié'], Response::HTTP_UNAUTHORIZED);
        }
        
        $user = $token->getUser();

        if (!$user || !is_object($user)) {
            return $this->json(['message' => 'Utilisateur non trouvé'], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getUserIdentifier(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'roles' => $user->getRoles(),
        ]);
    }

    /**
     * API: Inscription étudiant avec retour JWT
     * Retourne directement un token JWT après inscription
     */
    #[Route(path: '/api/register/student', name: 'api_register_student', methods: ['POST'])]
    public function apiRegisterStudent(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em
    ): Response {
        $data = json_decode($request->getContent(), true);

        // Validation
        if (empty($data['email']) || empty($data['plainPassword']) || empty($data['firstName']) || empty($data['lastName'])) {
            return $this->json([
                'message' => 'Tous les champs sont requis: email, plainPassword, firstName, lastName'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Vérifier si l'email existe déjà
        $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if ($existingUser) {
            return $this->json([
                'message' => 'Cet email est déjà utilisé'
            ], Response::HTTP_CONFLICT);
        }

        $student = new Student();
        $student->setEmail($data['email']);
        $student->setFirstName($data['firstName']);
        $student->setLastName($data['lastName']);
        $student->setEnrollmentDate(new \DateTime());
        
        $hashedPassword = $passwordHasher->hashPassword($student, $data['plainPassword']);
        $student->setPassword($hashedPassword);

        $em->persist($student);
        $em->flush();

        // Générer le token JWT
        $token = $this->jwtManager->create($student);

        return $this->json([
            'token' => $token,
            'user' => [
                'id' => $student->getId(),
                'email' => $student->getUserIdentifier(),
                'firstName' => $student->getFirstName(),
                'lastName' => $student->getLastName(),
                'roles' => $student->getRoles(),
            ]
        ], Response::HTTP_CREATED);
    }

    /**
     * API: Inscription professeur avec retour JWT
     * Retourne directement un token JWT après inscription
     */
    #[Route(path: '/api/register/teacher', name: 'api_register_teacher', methods: ['POST'])]
    public function apiRegisterTeacher(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em
    ): Response {
        $data = json_decode($request->getContent(), true);

        // Validation
        if (empty($data['email']) || empty($data['plainPassword']) || empty($data['firstName']) || empty($data['lastName'])) {
            return $this->json([
                'message' => 'Tous les champs sont requis: email, plainPassword, firstName, lastName'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Vérifier si l'email existe déjà
        $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if ($existingUser) {
            return $this->json([
                'message' => 'Cet email est déjà utilisé'
            ], Response::HTTP_CONFLICT);
        }

        $teacher = new Teacher();
        $teacher->setEmail($data['email']);
        $teacher->setFirstName($data['firstName']);
        $teacher->setLastName($data['lastName']);
        
        $hashedPassword = $passwordHasher->hashPassword($teacher, $data['plainPassword']);
        $teacher->setPassword($hashedPassword);

        $em->persist($teacher);
        $em->flush();

        // Générer le token JWT
        $token = $this->jwtManager->create($teacher);

        return $this->json([
            'token' => $token,
            'user' => [
                'id' => $teacher->getId(),
                'email' => $teacher->getUserIdentifier(),
                'firstName' => $teacher->getFirstName(),
                'lastName' => $teacher->getLastName(),
                'roles' => $teacher->getRoles(),
            ]
        ], Response::HTTP_CREATED);
    }
}
