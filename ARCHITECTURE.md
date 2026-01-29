# Architecture du projet EduLearn

## Vue d'ensemble

EduLearn est une plateforme e-learning avec une architecture **hybride** :
- **Backend** : Symfony 7 (PHP 8.2) avec rendu Twig pour l'interface professeur
- **Frontend** : React 19 (Vite) pour l'interface étudiant (SPA)
- **Base de données** : MySQL (AlwaysData)
- **API** : REST (API Platform) + endpoints custom pour le frontend React

```
┌─────────────────────────────────────────────────────────────────────┐
│                           NAVIGATEUR                                 │
├─────────────────────────────┬───────────────────────────────────────┤
│   Interface Professeur      │       Interface Étudiant              │
│   (Twig + Bootstrap 5)      │       (React 19 + TailwindCSS)        │
│   Port: 8000                │       Port: 3000 (dev)                │
├─────────────────────────────┴───────────────────────────────────────┤
│                         SYMFONY BACKEND                              │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐                  │
│  │ Controllers │  │   Services  │  │  Entities   │                  │
│  │  (Twig)     │  │  (AI, Quiz) │  │  (Doctrine) │                  │
│  └─────────────┘  └─────────────┘  └─────────────┘                  │
├─────────────────────────────────────────────────────────────────────┤
│                    BASE DE DONNÉES MYSQL                             │
└─────────────────────────────────────────────────────────────────────┘
```

---

## Structure des dossiers

```
elearning-belz-el-kihal/
├── backend/                 # Symfony 7
│   ├── config/              # Configuration bundles, routes, services
│   ├── public/              # Point d'entrée (index.php)
│   ├── src/
│   │   ├── Controller/      # Contrôleurs (Twig + API)
│   │   ├── Entity/          # Entités Doctrine
│   │   ├── Repository/      # Requêtes BDD
│   │   ├── Service/         # Logique métier (AI, Quiz, Transcription)
│   │   ├── Form/            # Formulaires Symfony
│   │   └── EventListener/   # Listeners (JWT, etc.)
│   ├── templates/           # Templates Twig
│   └── uploads/             # Fichiers uploadés (PDF, vidéos)
│
├── frontend/                # React 19 (Vite)
│   ├── src/
│   │   ├── api/             # Appels API (axios)
│   │   ├── components/      # Composants réutilisables
│   │   ├── context/         # Context React (Auth)
│   │   └── pages/           # Pages de l'application
│   └── vite.config.js       # Config Vite + Proxy API
│
└── vendor/                  # Dépendances Composer (root level)
```

---

## Modèle de données (entités)

| Entité | Description |
|--------|-------------|
| `User` | Classe abstraite (email, password, roles) |
| `Student` | Étudiant (hérite de User) |
| `Teacher` | Professeur (hérite de User) |
| `Course` | Cours créé par un professeur |
| `Video` | Vidéo associée à un cours |
| `Document` | PDF associé à un cours |
| `Quiz` | QCM (manuel ou généré par IA) |
| `Question` | Question de QCM |
| `Answer` | Réponse possible à une question |
| `QuizAttempt` | Tentative d'un étudiant sur un QCM |

---

## Endpoints d'API

### Authentification (JWT)
| Méthode | Endpoint | Description |
|---------|----------|-------------|
| POST | `/api/login_check` | Connexion, retourne le Token JWT |
| GET | `/api/me` | Récupérer l'utilisateur courant |

### API étudiant (React)

Les endpoints suivants sont gérés par API Platform (REST) :
| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/api/courses` | Liste des cours |
| GET | `/api/courses/{id}` | Détail d'un cours (avec vidéos, docs) |
| GET | `/api/quizzes` | Liste des QCM |
| GET | `/api/quizzes/{id}` | Détail d'un QCM |
| POST | `/api/quiz_attempts` | Soumettre une tentative de QCM |
| GET | `/api/quizzes/{id}/results` | Résultats détaillés d'un QCM |

Endpoints personnalisés :
| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/api/me/stats` | Statistiques globales de l'étudiant |

### API Professeur (Admin)
Ces actions sont souvent déclenchées par l'interface Twig mais exposées via API :
| Méthode | Endpoint | Description |
|---------|----------|-------------|
| POST | `/api/courses/{id}/generate_quiz` | Générer un QCM via IA |
| GET | `/api/transcription-progress/{id}` | Statut de la transcription (SSE/Polling) |

---

## Services backend

| Service | Rôle |
|---------|------|
| `AiQuizService` | Appels API IA (Kimi + Mistral fallback) |
| `QuizGeneratorService` | Génération de QCM à partir du contenu |
| `TranscriptionService` | Transcription vidéo (Kimi + Mistral fallback) |
| `PdfParserService` | Extraction texte des PDFs |

---

## Sécurité

- **JWT** : LexikJWTAuthenticationBundle pour l'API
- **Rôles** : `ROLE_STUDENT`, `ROLE_TEACHER`, `ROLE_ADMIN`
- **Firewall** : 
  - `/api/*` → Mode **Stateless** (Le JWT est vérifié s'il est présent)
  - `/teacher/*`, `/course/*` → Session Symfony classique
- **Contrôle d'accès ** :
  - L'API est **publique par défaut** (`PUBLIC_ACCESS`) pour permettre la consultation (GET).
  - L'authentification est requise au niveau des contrôleurs/ressources pour les actions sensibles (POST, stats perso).
  - Via l'API platform, on peut accéder à tout.

---

## Commandes utiles

```bash
# Backend (depuis /backend)
symfony server:start          # Démarrer le serveur
php bin/console cache:clear   # Vider le cache
php bin/console doctrine:migrations:migrate  # Migrations BDD

# Frontend (depuis /frontend)  
npm run dev                   # Serveur dev (port 3000)
npm run build                 # Build production
```

---

## Variables d'environnement (.env)

```env
# Base de données
DATABASE_URL="mysql://user:pass@host:port/dbname"

# JWT
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=votre_passphrase

# APIs IA
KIMI_API_KEY=sk-kimi-xxx
MISTRAL_API_KEY=xxx
```
