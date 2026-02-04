# 🎓 ELearning Belz El Kihal

Plateforme d'e-learning moderne avec transcription automatique par IA et génération de QCM intelligents.

## 🚀 Fonctionnalités Clés
- **Transcription IA (Turbo)** : Utilise Groq (Whisper Large V3) pour transcrire les vidéos en ~2 secondes.
- **QCM Génératifs** : Mistral AI analyse le contenu (vidéo transcrite + documents PDF) pour créer des quiz pertinents.
- **Upload Unifié** : Interface drag-and-drop avec barre de progression temps réel.

---

## 🛠️ Prérequis
Assurez-vous d'avoir installé :
- **PHP 8.2+**
- **Composer**
- **Node.js** (v18+) & NPM
- **Symfony CLI**
- **FFMpeg** (Accessible via la commande `ffmpeg` dans le terminal)

## 📦 Installation

### 1. Backend (Symfony)
```bash
cd backend
composer install
```

Configurez votre base de données dans `.env` ou `.env.local` :
```env
DATABASE_URL="mysql://user:password@127.0.0.1:3306/elearning?serverVersion=8.0.32&charset=utf8mb4"
```

Configurez les clés API (Obligatoire pour l'IA) :
```env
# Pour la transcription ultra-rapide
GROQ_API_KEY=gsk_...

# Pour la génération de QCM
MISTRAL_API_KEY=...
```

Installez la base de données :
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

Téléchargez les modèles Whisper (pour le mode fallback local) :
```bash
# Optionnel si vous utilisez Groq, mais recommandé
php bin/console whisper:install
```

Lancer le serveur :
```bash
symfony server:start
```

### 2. Frontend (React/Webpack)
Le frontend est intégré via Webpack Encore.

```bash
cd backend
npm install
npm run dev -- --watch
```

---

## 🧩 Structure du Projet
- `src/Service/TranscriptionService.php` : Logique hybride (Groq API + Whisper Local Fallback).
- `src/Service/QuizGeneratorService.php` : Orchestration de la génération de QCM via Mistral.
- `src/EventListener/MediaUploadListener.php` : Déclenchement automatique de l'IA après upload.
- `templates/course/_form.html.twig` : Interface d'upload avec gestion de la progression.

## ⚠️ Dépannage
- **Transcription bloquée ?** Vérifiez `api_error.log` à la racine.
- **Erreur "FFMpeg" ?** Assurez-vous que FFMpeg est dans votre PATH système.
- **Pas de QCM ?** Vérifiez que la transcription a bien réussi (le texte doit apparaître sur la page du cours).
