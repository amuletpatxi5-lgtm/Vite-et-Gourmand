-- =======================================================
-- Comptes de test (un compte par rôle)
-- Exécution après 01-schema.sql et 02-data.sql.
--
-- Aucun mot de passe ne figurent ici, que les empreintes
-- password_hash (bcrypt) générées dans le conteneur app. 
--
-- ON CONFLICT : le script peut être relancé sans erreur. 
-- =======================================================

BEGIN;

-- Administrateur (José) : email et mot de passe uniquement
INSERT INTO utilisateur (email, mot_de_passe, role_id)
VALUES (
    'admin@vite-gourmand.test',
    '$2y$10$lbw6YHtgPUeoxcVlQIQJXu7Tg9ffTbbYxBM3RbZ.ir42Wc8RX14MK',
    (SELECT role_id FROM role WHERE libelle = 'administrateur')
)
ON CONFLICT (email) DO NOTHING;

-- Employé : email et mot de passe uniquement
INSERT INTO utilisateur (email, mot_de_passe, role_id)
VALUES (
    'employe@vite-gourmand.test',
    '$2y$10$bhJOsFQqUYde2Dlzy15fWucCBTJM8sL6O61YHdW7wEufkx.cbLPvm',
    (SELECT role_id FROM role WHERE libelle = 'employe')
)
ON CONFLICT (email) DO NOTHING;

-- Client : toutes les informations, nécessaires à la livraison
INSERT INTO utilisateur (email, mot_de_passe, nom, prenom, telephone, adresse_postale, code_postal, ville, pays, role_id)
VALUES (
    'client@vite-gourmand.test',
    '$2y$10$lGVOjgbsDbxpdRc3pFrv5eqCqQoFtAgh3jTQdGd1u3wohTSc4Rfka',
    'Dupont', 'Jean-Christophe', '0758889900', '12 rue Sainte-Catherine', '33000', 'Bordeaux', 'France',
    (SELECT role_id FROM role WHERE libelle = 'utilisateur')
)
ON CONFLICT (email) DO NOTHING;

COMMIT;
