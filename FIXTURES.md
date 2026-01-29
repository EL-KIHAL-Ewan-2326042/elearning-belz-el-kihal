# Identifiants de Connexion - Fixtures

Voici les comptes créés par les fixtures pour tester l'application.
Le mot de passe pour tous les comptes est : `password123`

Attention ! Les QCM liés aux fixtures ne sont pas générés par Mistral et sont donc incorrects !

## Professeurs

| Nom | Email | Rôle | Cours associé |
| :--- | :--- | :--- | :--- |
| **Samir Chtioui** | `samir.chtioui@example.com` | `ROLE_TEACHER` | Introduction à la Mobilité |
| **See Tickets** | `see.tickets@example.com` | `ROLE_TEACHER` | Projet de Groupe SAE 105 |

## Élèves

| Nom | Email | Rôle |
| :--- | :--- | :--- |
| **Matteo Belz** | `matteo.belz@example.com` | `ROLE_STUDENT` |
| **Ewan El Kihal** | `ewan.elkihal@example.com` | `ROLE_STUDENT` |

## Charger les fixtures

Pour charger les données de test dans la base de données, exécutez la commande suivante dans le dossier `backend` :

```bash
php bin/console doctrine:fixtures:load -n
```

## Vérifier le chargement

Pour vérifier que les utilisateurs ont bien été créés :

```bash
php bin/console dbal:run-sql "SELECT * FROM user"
```
