# Écarts entre l'annexe 1 de l'énoncé et la base Vite & Gourmand

L'annexe 1 de l'énoncé(p.10) propose un MCD de départ. Le MCD du projet (Looping, `Database/mcd-vite-et-gourmand.loo`) le reprend et le complète. Ce document liste chaque écart et sa justification. Le script de référence est `Database/01-schema.sql` (PostgreSQL 16).

Les numéros de page renvoient à l'énoncé.

## 1. Choix généraux

| Sujet | Annexe 1 | Projet | Justification |
|---|---|---|---|
| Identifiants | `AUTO_INCREMENT` (syntaxe MySQL) | `SERIAL` | Équivalent PostgreSQL de l'auto-incrément. |
| Longueur des textes | `VARCHAR(50)` partout | Longueur adaptée à chaque rubrique, `TEXT` pour les textes longs | Un email, une adresse ou une description dépassent souvent 50 caractères. |
| Montants | `DOUBLE` | `DECIMAL` | Un `DOUBLE` peut provoquer des erreurs d'arrondi tant dis que `DECIMAL` garde les centimes exacts. |
| Heures | `VARCHAR(50)` | `TIME` | Permet la comparaison et le trie des heures également de refuser une valeur invalide. |
| Booléens | `BOOL` | `BOOLEAN` | Type natif PostgreSQL. |
| Contraintes | Aucune | `NOT NULL`, `UNIQUE`, `CHECK`, clés étrangères, index | La base refuse elle-même les données incohérentes (prix négatif, note hors de 1 à 5, statut inconnu). Les index accélèrent les filtres de l'espace employé (p. 8). |

## 2. Comptes

| Élément | Annexe 1 | Projet | Justification |
|---|---|---|---|
| `utilisateur.nom` | Absent | Ajouté | C'est ce que demande le nom et le prénom à la création du compte (p. 5). |
| `utilisateur.password` | `VARCHAR(50)` | `mot_de_passe VARCHAR(255)` | On stocke l'empreinte `password_hash`, jamais le mot de passe. Une empreinte fait 60 caractères, 255 caractères c'est la taille recommandée par la documentation PHP, ce qui laisse la place au futur algorithme. |
| `utilisateur.email` | `VARCHAR(50)` | `VARCHAR(255)`, `UNIQUE` | L'email doit être unique, il sert d'identifiant de connexion (p. 5).|
| `utilisateur.code_postal` | Absent | Ajouté | Il complète l'adresse postale demandée (p. 5) et pré-remplit l'adresse de la commande. |
| `utilisateur.actif` | Absent | Ajouté | L'administrateur doit pouvoir rendre un compte employé inutilisable (p. 9). On désactive le compte au lieu de le supprimer, pour garder l'historique. |
| Nom, prénom, téléphone, adresse | Obligatoires dans l'esprit de l'annexe | Facultatifs | Un email et un mot de passe pour un compte employé(p. 9). Rendu obligatoires par le formulaire d'inscription côté application. |
| `role.role_id` | `INT` | `SERIAL`, `libelle` `UNIQUE` | Même logique que les autres tables de référence. |
| Association `possede` | Utilisateur 1,1 / Rôle 0,n | Clé étrangère `role_id` dans `utilisateur` | Traduction directe de l'association en MLD. |
| Entité `reinitialisation` | Absente | Ajoutée | Réinitialisation du mot de passe par un lien envoyé par mail (p. 5). La table stocke l'empreinte du jeton, sa date d'expiration et sa date d'utilisation, pour qu'un lien ne serve qu'une fois. |

## 3. Catalogue

| Élément | Annexe 1 | Projet | Justification |
|---|---|---|---|
| `menu.regime` | Attribut `VARCHAR(50)` en plus de l'association `adapte` | Supprimé, remplacé par la clé étrangère `regime_id` | L'attribut faisait doublon avec l'association vers `regime`. |
| `menu.conditions` | Absent | Ajouté (`TEXT`) | Chaque menu a ses conditions (délai de commande, stockage), à mettre en évidence (p. 4 et 6). |
| `menu.description` | `VARCHAR(50)` | `TEXT` | Une présentation de menu dépasse 50 caractères. Une seule description par menu, les cartes en affichent le début. |
| `menu.titre` | `VARCHAR(50)` | `VARCHAR(100)`, `UNIQUE` | Deux menus ne doivent pas porter le même titre. |
| `menu.prix_par_personne` | `DOUBLE` | `DECIMAL(6,2)` | Nom gardé comme dans l'annexe. Le prix pour le nombre minimum de personnes (p. 4) se calcule à l'affichage. |
| `menu.nombre_personne_minimum` | Présent | `nombre_de_personne_minimum` | Même donnée, avec une contrainte de valeur minimale à 1. |
| `menu.quantite_restante` | Présent | Gardé, avec `CHECK >= 0` | Stock disponible (p. 4), qui ne peut pas devenir négatif. |
| `menu.actif` | Absent | Ajouté | Un menu déjà commandé ne peut pas être supprimé, on le retire plutôt de la vente en le désactivant. Moins de risque de cassé l'historique.|
| `plat.photo` | `BLOB` dans `plat` | Supprimé | Rien n'est demandé pour le plat d'après l'énoncé uniquement une galerie d'images pour le menu.(p. 4). |
| Entité `image` | Absente | Ajoutée, liée au menu | Galerie du menu (p. 4) : chemin du fichier, ordre d'affichage et texte alternatif. Le texte alternatif répond au RGAA (p. 9). Les fichiers restent sur le serveur, la base ne stocke que leur chemin. |
| `plat.type_plat` | Absent | Ajouté (`entree`, `plat`, `dessert`) | Pour un menu : une liste d'entrées, de plats et de desserts (p. 4). |
| Association `propose` (menu et plat) | `propose` | Table `compose` | L'annexe utilise le nom `propose` pour deux associations (menu et thème, menu et plat). Le renommage évite le conflit de noms. Un plat peut appartenir à plusieurs menus (p. 4). |
| Association `propose` (menu et thème) | Menu 1,1 / Thème 0,n | Clé étrangère `theme_id` dans `menu` | Traduction directe en MLD. |
| Association `contient` | Plat 0,n / Allergène 0,n | Table `contient` | Identique à l'annexe. |

## 4. Commandes

| Élément | Annexe 1 | Projet | Justification |
|---|---|---|---|
| Modélisation | Association porteuse de données entre `utilisateur` et `menu` | Table `commande` avec `numero_commande` en clé primaire et deux clés étrangères | Même sens que l'annexe. Le numéro de commande reste l'identifiant, comme dans l'annexe (format `VG-2026-0042`). Ce format fait 12 caractères, 20 laisse de la marge. |
| `date_commande` | `DATE` | `TIMESTAMP` | Garde aussi l'heure de la commande. |
| `heure_livraison` | `VARCHAR(50)` | `TIME` | Voir les choix généraux. |
| Adresse de la prestation | Absente | `adresse_prestation`, `code_postal_prestation`, `ville_prestation` | L'énoncé demande l'adresse et le lieu de la prestation (p. 7). La ville détermine si la livraison est gratuite (Bordeaux) ou payante. |
| `distance_km` | Absent | Ajouté | Hors de Bordeaux, la livraison coûte 5 € plus 0,59 € par kilomètre (p. 7). Garder la distance permet de justifier le prix. |
| `remise`, `prix_total` | Absents | Ajoutés | Réduction de 10 % à partir de 5 personnes de plus que le minimum, détail du prix avant validation (p. 7). Le prix payé reste figé même si le tarif du menu change. |
| `restition_materiel` (orthographe de l'annexe)| `BOOL` | `date_restitution DATE` | Une date dit si le matériel est rendu et quand. Elle sert à vérifier le délai de 10 jours ouvrés (p. 8). |
| `statut` | `VARCHAR(50)` libre | `VARCHAR(30)` avec `CHECK` | Seuls les statuts de l'énoncé sont acceptés (p. 8), plus `en_attente` à la création et `annulee`. |
| `mode_contact`, `motif_annulation` | Absents | Ajoutés | L'employé doit indiquer le mode de contact et le motif avant de modifier ou d'annuler une commande (p. 8). |
| Entité `historique_statut` | Absente | Ajoutée | Le suivi affiche tous les états de la commande avec la date et l'heure de chaque changement (p. 7). |

## 5. Avis et horaires

| Élément | Annexe 1 | Projet | Justification |
|---|---|---|---|
| Lien de l'avis | Association `publie` avec l'utilisateur | Clé étrangère `numero_commande`, `UNIQUE` | L'avis se donne depuis une commande terminée (p. 7). On sait ainsi quel menu est noté, l'auteur se retrouve par la commande, et chaque commande reçoit au plus un avis. |
| `avis.note` | `VARCHAR(50)` | `INT` avec `CHECK` de 1 à 5 | Une note est un nombre entre 1 et 5 (p. 7). |
| `avis.statut` | `VARCHAR(50)` libre | `CHECK` (`en_attente`, `valide`, `refuse`) | Seuls les avis validés par un employé s'affichent sur l'accueil (p. 3 et 8). |
| `avis.date_avis` | Absent | Ajouté | Permet de trier les avis du plus récent au plus ancien. |
| `avis.avis_id` | `INT` | `SERIAL` | Identifiant automatique comme les autres tables. |
| `horaire.heure_ouverture`, `heure_fermeture` | `VARCHAR(50)` | `TIME`, vides si le jour est fermé | Un jour fermé n'a pas d'heures. Une contrainte vérifie que l'ouverture précède la fermeture. |
| `horaire.jour` | `VARCHAR(50)` | `CHECK` du lundi au dimanche, `UNIQUE` | Une ligne par jour, affichée dans le pied de page (p. 4). |

## 6. Données gérées hors de la base relationnelle

| Donnée | Choix | Justification |
|---|---|---|
| Formulaire de contact | Envoyé par mail, pas stocké | L'énoncé demande l'envoi de la demande par mail à l'entreprise (p. 9). Ne pas la stocker limite les données personnelles conservées (RGPD). |
| Statistiques des commandes | MongoDB (classe `Stats`) | L'énoncé impose une base non relationnelle pour le nombre de commandes et le chiffre d'affaires par menu (p. 9). |
