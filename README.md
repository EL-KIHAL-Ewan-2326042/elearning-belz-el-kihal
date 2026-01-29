# EduLearn - Matteo Belz & Ewan El Kihal

Cette plateforme d'apprentissage en ligne propose des fonctionnalités de transcription automatique et de génération de questionnaires par intelligence artificielle.

## Architecture du projet

Le projet repose sur une structure hybride qui sépare l'administration de l'espace étudiant.

Le dossier backend contient une application Symfony 7, avec SecurityBundle, API Platform, Doctrine et Boostrap. Elle gère la logique métier, la persistance des données et l'interface destinée aux professeurs via le moteur de template Twig (authentifiée par sessions classiques). C'est également ici que sont implémentés les services d'intelligence artificielle pour la transcription des vidéos et la création des QCM.

Le dossier frontend abrite une application React, avec Axios, Tailwind. Elle communique avec le backend via une interface de programmation (api) sécurisée par des jetons jwt pour offrir une expérience fluide aux étudiants.

## Configuration requise

Pour faire fonctionner ce projet, vous devez disposer des éléments suivants sur votre machine :

- PHP en version 8.2 ou 8.4
- Composer pour la gestion des dépendances php
- Node.js (version 18 ou supérieure) et npm
- Symfony CLI pour le serveur local
- Ffmpeg pour le traitement des fichiers audio et vidéo par les services de transcription

### Configuration spécifique de php

Il est impératif d'activer l'extension sodium dans votre configuration php. Pour cela, localisez votre fichier php.ini et assurez-vous que la ligne suivante n'est pas commentée (retirez le point-virgule au début si nécessaire) :

```ini
extension=sodium
```

Vérifiez également que les extensions ctype, iconv et openssl sont bien actives.

## Procédure d'installation

L'installation se déroule en deux étapes principales pour le serveur et l'interface.

### Installation du backend

Rendez-vous dans le répertoire backend.
Créez le fichier `.env` en copiant le fichier `.env.example` et renseignez vos paramètres de connexion à la base de données, vos clés API, vos secrets JWT et votre secret d'application. La clé API de Kimi est optionnelle.

Ensuite, lancez l'installation des dépendances :

```bash
cd backend
composer install
npm install
npm run build
```
Exécutez les commandes suivantes pour initialiser la structure des données :

```bash
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

Enfin, générez les clés SSL pour la sécurité des jetons jwt (nécessaire pour l'api étudiante).
Si la commande automatique `lexik:jwt:generate-keypair` échoue (notamment sous Windows), utilisez OpenSSL manuellement :

```bash
mkdir config/jwt
openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
```

Lors de la création de la clé privée, entrez une passphrase et **n'oubliez pas de la reporter** dans votre fichier `.env` (variable `JWT_PASSPHRASE`).

### Installation du frontend

Dans un autre terminal, installez les dépendances propres à l'application étudiante.

```bash
cd frontend
npm install
```

## Précisions sur la configuration de l'intelligence artificielle

Le système nécessite des clés api pour fonctionner. Vous devez les ajouter dans un fichier .env crée dans le même dossier que le fichier .env.local (soit le dossier backend).

Le projet peut fonctionner entièrement avec Mistral AI seul.

Si vous configurez également Kimi, il sera utilisé en priorité pour les transcriptions **et** les QCM, avec un basculement automatique sur Mistral en cas d'erreur.

L'API Kimi testée est celle de Kimi Code, mais l'API classique devrait également fonctionner. Elle donne des résultats généralement meilleurs que Mistral (il y a un essai gratuit de 7 jours). 

```env
# Configuration obligatoire
MISTRAL_API_KEY=votre_cle_ici

# Configuration optionnelle pour Kimi
KIMI_API_KEY=votre_cle_ici
```

## Lancement du projet

Pour démarrer l'ensemble de l'application, vous devrez lancer trois processus différents. Dans trois terminaux séparés, exécutez :

1. Le serveur Symfony (dans le dossier backend) :
   ```bash
   symfony serve
   ```

2. Le serveur React (dans le dossier frontend) :
   ```bash
   npm run dev
   ```

Pour accéder à l'application, accédez via ```localhost:8000```.

---
## Au cas où...

Si jamais il ya une erreur, lancez 
```
php bin/console doctrine:schema:update --force
```
et relancez le serveur Symfony.

Vous retrouverez davantage d'informations sur l'architecture du projet dans ARCHITECTURE.md.