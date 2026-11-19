# Vite-et-Gourmand

Application Web et Mobile : Prestations de service traiteur à Bordeaux depuis 25 ans pour tous types d'évènements
Accès aux différentes offres et possibilités de livraison, menus filtrables, commande avec calcul du prix et de la livraison, suivi des commandes, avis clients, espace employé et statistiques pour l'administrateur.


Projet réalisé pour l'ECF du titre professionnel Développeur Web et Web Mobile (Studi).


- **Site en ligne** : https://vite-et-gourmand-1m5h.onrender.com (hébergement gratuit : le premier chargement peut prendre jusqu'à une minute)
- **Gestion de projet** : https://github.com/users/amuletpatxi5-lgtm/projects/5/views/1

## Technologies

| Rôle | Choix |
|---|---|
| Back-end | PHP 8.3, architecture MVC sans framework |
| Base relationnelle | PostgreSQL 16 |
| Base NoSQL | MongoDB 7 (statistiques des commandes) |
| Front-end | HTML, CSS, JavaScript sans bibliothèque |
| Mails | PHPMailer ; Mailpit en local, Brevo en production |
| Distance de livraison | API IGN (géocodage) et OpenRouteService (itinéraire) |
| Environnement | Docker et Docker Compose |
| Déploiement | Render (application et PostgreSQL), MongoDB Atlas |

## Installation en local

**Prérequis** : Docker Desktop et Git.

1. Cloner le dépôt :
   ```bash
   git clone https://github.com/amuletpatxi5-lgtm/Vite-et-Gourmand.git
   cd Vite-et-Gourmand
   ```
2. Créer le fichier d'environnement, puis remplacer `change-moi` par un mot de passe de votre choix :
   ```bash
   cp .env.example .env
   ```
   `ORS_API_KEY` est facultative : sans elle, la distance est estimée à vol d'oiseau.
3. Lancer les conteneurs (application, PostgreSQL, MongoDB, Mailpit) :
   ```bash
   docker compose up -d --build
   ```
4. Installer les dépendances PHP dans le conteneur :
   ```bash
   docker compose exec app composer install
   ```
5. Créer la base et charger les données, **dans cet ordre** :
   ```bash
   docker compose exec db sh -c 'for f in 01-schema 02-data 03-comptes-test 04-galerie-menus 05-sequence-commande 06-avis-demo; do psql -U "$POSTGRES_USER" -d "$POSTGRES_DB" -v ON_ERROR_STOP=1 -f /scripts/$f.sql; done'
   ```
6. Charger les statistiques de démonstration dans MongoDB :
   ```bash
   docker compose cp Database/07-stats-demo.js mongo:/tmp/07-stats-demo.js
   docker compose exec mongo sh -c 'mongosh --quiet --file /tmp/07-stats-demo.js'

   ```
7. Ouvrir le site sur http://localhost:8080 et les mails reçus sur http://localhost:8025 (Mailpit).

## Comptes de test

| Rôle | Email |
|---|---|
| Administrateur | `admin@vite-gourmand.test` |
| Employé | `employe@vite-gourmand.test` |
| Client | `client@vite-gourmand.test` |

Les mots de passe sont fournis dans la copie à rendre et dans le manuel d'utilisation.

## Organisation du code

```
Public/      point d'entrée (index.php, routeur), CSS, JavaScript, images
src/         contrôleurs, modèles, services (Mailer, Stats, Tarif, Distance)
templates/   vues HTML
Database/    scripts SQL (schéma, données, comptes de test) et données MongoDB
Docs/        charte graphique, maquettes, wireframes, MCD et MLD
```

## Workflow Git

- une branche par fonctionnalité (`feature/...`), puis une *pull request* vers `develop` ;
- une *pull request* de release `develop` vers `main` à chaque livraison ;
- `main` est déployée automatiquement sur Render.

## Sécurité

Les secrets (mots de passe, clés d'API) sont dans `.env`, ignoré par Git, et dans les variables d'environnement de Render. Le détail des mécanismes de sécurité et des tests est dans la documentation technique.
