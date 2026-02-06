<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UnifiedLoginController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
        private TokenStorageInterface $tokenStorage
    ) {
    }

    /**
     * Page de connexion unique pour tous (élèves et profs)
     */
    #[Route(path: '/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(Request $request): Response
    {
        // Si déjà connecté, rediriger vers le bon panel
        if ($this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }

        // Traitement du formulaire (POST)
        if ($request->isMethod('POST')) {
            $email = $request->request->get('_username');
            $password = $request->request->get('_password');

            $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);

            if (!$user || !$this->passwordHasher->isPasswordValid($user, $password)) {
                $this->addFlash('error', 'Email ou mot de passe incorrect.');
                return $this->redirectToRoute('app_login');
            }

            // Créer le token d'authentification avec le bon firewall
            $token = new UsernamePasswordToken(
                $user,
                'main',
                $user->getRoles()
            );
            $this->tokenStorage->setToken($token);
            
            // Stocker dans la session de manière compatible avec Symfony
            $session = $request->getSession();
            $session->set('_security_main', serialize($token));
            $session->save();

            // Rediriger selon le rôle
            $roles = $user->getRoles();
            
            if (in_array('ROLE_TEACHER', $roles)) {
                $this->addFlash('success', 'Bienvenue, ' . $user->getFirstName() . ' !');
                return $this->redirectToRoute('course_index');
            }
            
            if (in_array('ROLE_STUDENT', $roles)) {
                $this->addFlash('success', 'Bienvenue, ' . $user->getFirstName() . ' !');
                return $this->redirectToRoute('student_dashboard');
            }

            return $this->redirect('/');
        }

        // Affichage du formulaire (GET)
        return $this->render('security/unified_login.html.twig');
    }

    /**
     * Dashboard étudiant (affiché après connexion)
     */
    #[Route(path: '/dashboard', name: 'student_dashboard', methods: ['GET'])]
    public function studentDashboard(): Response
    {
        $user = $this->getUser();
        
        if (!$user || !in_array('ROLE_STUDENT', $user->getRoles())) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('student/dashboard.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * Redirection automatique vers le bon dashboard
     */
    #[Route(path: '/dashboard-redirect', name: 'app_dashboard', methods: ['GET'])]
    public function dashboardRedirect(): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $roles = $user->getRoles();

        if (in_array('ROLE_TEACHER', $roles)) {
            return $this->redirectToRoute('course_index');
        }

        if (in_array('ROLE_STUDENT', $roles)) {
            return $this->redirectToRoute('student_dashboard');
        }

        return $this->redirect('/');
    }

    /**
     * Déconnexion
     */
    #[Route(path: '/logout', name: 'app_logout', methods: ['GET'])]
    public function logout(): void
    {
        // Cette méthode est interceptée par le firewall
        throw new \LogicException('Cette méthode est interceptée par le firewall.');
    }
}
