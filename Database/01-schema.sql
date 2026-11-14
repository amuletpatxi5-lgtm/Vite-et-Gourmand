-- =====================================================================
-- Vite & Gourmand : création de la base de données (PostgreSQL 16)
-- Source : MCD Looping (Database/mcd-vite-et-gourmand.loo)
-- Script généré brut : Database/01-schema-genere.sql
-- Ce fichier en est la version corrigée pour PostgreSQL.
--
-- La base doit être créée en UTF8 (accents : Noël, Végétarien...).
-- La connexion PHP fixe le fuseau horaire sur Europe/Paris, pour que
-- CURRENT_TIMESTAMP donne l'heure de Bordeaux.
-- =====================================================================

-- Tout ou rien : si une instruction échoue, aucune table n'est créée.
BEGIN;

-- Permet de relancer le script sur une base de test sans erreur.
DROP TABLE IF EXISTS contient, compose, reinitialisation, historique_statut,
    horaire, avis, commande, image, plat, menu, utilisateur, allergene,
    regime, theme, role CASCADE;

-- ---------------------------------------------------------------------
-- Tables de référence
-- ---------------------------------------------------------------------

CREATE TABLE role(
   role_id SERIAL,
   libelle VARCHAR(50) NOT NULL,
   PRIMARY KEY(role_id),
   UNIQUE(libelle)
);

CREATE TABLE theme(
   theme_id SERIAL,
   libelle VARCHAR(50) NOT NULL,
   PRIMARY KEY(theme_id),
   UNIQUE(libelle)
);

CREATE TABLE regime(
   regime_id SERIAL,
   libelle VARCHAR(50) NOT NULL,
   PRIMARY KEY(regime_id),
   UNIQUE(libelle)
);

CREATE TABLE allergene(
   allergene_id SERIAL,
   libelle VARCHAR(50) NOT NULL,
   PRIMARY KEY(allergene_id),
   UNIQUE(libelle)
);

-- ---------------------------------------------------------------------
-- Comptes
-- ---------------------------------------------------------------------

CREATE TABLE utilisateur(
   utilisateur_id SERIAL,
   email VARCHAR(255) NOT NULL,
   mot_de_passe VARCHAR(255) NOT NULL,   -- empreinte password_hash, jamais le mot de passe
   nom VARCHAR(50),                      -- facultatifs : un compte employé n'a
   prenom VARCHAR(50),                   -- qu'un email et un mot de passe
   telephone VARCHAR(20),
   adresse_postale VARCHAR(255),
   code_postal CHAR(5),
   ville VARCHAR(100),
   pays VARCHAR(50),
   actif BOOLEAN NOT NULL DEFAULT TRUE,
   role_id INT NOT NULL,
   PRIMARY KEY(utilisateur_id),
   UNIQUE(email),
   FOREIGN KEY(role_id) REFERENCES role(role_id),
   CONSTRAINT ck_utilisateur_code_postal CHECK (code_postal ~ '^[0-9]{5}$')
);

CREATE TABLE reinitialisation(
   reinitialisation_id SERIAL,
   jeton_hash CHAR(64) NOT NULL,          -- empreinte SHA-256 du jeton envoyé par mail
   date_demande TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
   date_expiration TIMESTAMP NOT NULL,
   date_utilisation TIMESTAMP,
   utilisateur_id INT NOT NULL,
   PRIMARY KEY(reinitialisation_id),
   UNIQUE(jeton_hash),
   FOREIGN KEY(utilisateur_id) REFERENCES utilisateur(utilisateur_id) ON DELETE CASCADE,
   CONSTRAINT ck_reinitialisation_dates CHECK (date_expiration > date_demande)
);

-- ---------------------------------------------------------------------
-- Catalogue
-- ---------------------------------------------------------------------

CREATE TABLE menu(
   menu_id SERIAL,
   titre VARCHAR(100) NOT NULL,
   description TEXT NOT NULL,
   nombre_de_personne_minimum INT NOT NULL,
   prix_par_personne DECIMAL(6,2) NOT NULL,
   conditions TEXT NOT NULL,
   quantite_restante INT NOT NULL,
   actif BOOLEAN NOT NULL DEFAULT TRUE,
   regime_id INT NOT NULL,
   theme_id INT NOT NULL,
   PRIMARY KEY(menu_id),
   UNIQUE(titre),
   FOREIGN KEY(regime_id) REFERENCES regime(regime_id),
   FOREIGN KEY(theme_id) REFERENCES theme(theme_id),
   CONSTRAINT ck_menu_personnes CHECK (nombre_de_personne_minimum >= 1),
   CONSTRAINT ck_menu_prix CHECK (prix_par_personne > 0),
   CONSTRAINT ck_menu_stock CHECK (quantite_restante >= 0)
);

CREATE TABLE plat(
   plat_id SERIAL,
   titre_plat VARCHAR(100) NOT NULL,
   type_plat VARCHAR(10) NOT NULL,
   PRIMARY KEY(plat_id),
   CONSTRAINT ck_plat_type CHECK (type_plat IN ('entree', 'plat', 'dessert'))
);

CREATE TABLE image(
   image_id SERIAL,
   chemin VARCHAR(255) NOT NULL,
   texte_alternatif VARCHAR(255) NOT NULL,   -- RGAA : description lue par les lecteurs d'écran
   ordre INT NOT NULL,
   menu_id INT NOT NULL,
   PRIMARY KEY(image_id),
   FOREIGN KEY(menu_id) REFERENCES menu(menu_id) ON DELETE CASCADE,
   CONSTRAINT ck_image_ordre CHECK (ordre >= 1)
);

-- Composition d'un menu : un plat déjà utilisé dans un menu ne peut pas
-- être supprimé (RESTRICT par défaut) ; supprimer un menu vide sa composition.
CREATE TABLE compose(
   menu_id INT,
   plat_id INT,
   PRIMARY KEY(menu_id, plat_id),
   FOREIGN KEY(menu_id) REFERENCES menu(menu_id) ON DELETE CASCADE,
   FOREIGN KEY(plat_id) REFERENCES plat(plat_id)
);

-- Allergènes d'un plat : supprimer un plat supprime ses lignes d'allergènes.
CREATE TABLE contient(
   allergene_id INT,
   plat_id INT,
   PRIMARY KEY(allergene_id, plat_id),
   FOREIGN KEY(allergene_id) REFERENCES allergene(allergene_id),
   FOREIGN KEY(plat_id) REFERENCES plat(plat_id) ON DELETE CASCADE
);

-- ---------------------------------------------------------------------
-- Commandes
-- ---------------------------------------------------------------------

CREATE TABLE commande(
   numero_commande VARCHAR(20),              -- format VG-2026-0042
   date_commande TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
   date_prestation DATE NOT NULL,
   heure_livraison TIME NOT NULL,
   nombre_personne INT NOT NULL,
   prix_menu DECIMAL(8,2) NOT NULL,
   prix_livraison DECIMAL(6,2) NOT NULL,
   statut VARCHAR(30) NOT NULL DEFAULT 'en_attente',
   pret_materiel BOOLEAN NOT NULL DEFAULT FALSE,
   adresse_prestation VARCHAR(255) NOT NULL,
   code_postal_prestation CHAR(5) NOT NULL,
   ville_prestation VARCHAR(100) NOT NULL,
   distance_km DECIMAL(6,2) NOT NULL DEFAULT 0,
   remise DECIMAL(8,2) NOT NULL DEFAULT 0,
   prix_total DECIMAL(8,2) NOT NULL,
   mode_contact VARCHAR(10),
   motif_annulation TEXT,
   date_restitution DATE,
   menu_id INT NOT NULL,
   utilisateur_id INT NOT NULL,
   PRIMARY KEY(numero_commande),
   FOREIGN KEY(menu_id) REFERENCES menu(menu_id),
   FOREIGN KEY(utilisateur_id) REFERENCES utilisateur(utilisateur_id),
   CONSTRAINT ck_commande_statut CHECK (statut IN ('en_attente', 'accepte',
      'en_preparation', 'en_livraison', 'livre', 'attente_retour_materiel',
      'terminee', 'annulee')),
   CONSTRAINT ck_commande_personnes CHECK (nombre_personne >= 1),
   CONSTRAINT ck_commande_montants CHECK (prix_menu >= 0 AND prix_livraison >= 0
      AND distance_km >= 0 AND remise >= 0 AND prix_total >= 0),
   CONSTRAINT ck_commande_code_postal CHECK (code_postal_prestation ~ '^[0-9]{5}$'),
   CONSTRAINT ck_commande_contact CHECK (mode_contact IN ('telephone', 'email')),
   CONSTRAINT ck_commande_dates CHECK (date_prestation >= date_commande::date)
);

-- Suivi daté de tous les statuts d'une commande (exigé par l'énoncé).
CREATE TABLE historique_statut(
   historique_id SERIAL,
   statut VARCHAR(30) NOT NULL,
   date_changement TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
   numero_commande VARCHAR(20) NOT NULL,
   PRIMARY KEY(historique_id),
   FOREIGN KEY(numero_commande) REFERENCES commande(numero_commande) ON DELETE CASCADE,
   CONSTRAINT ck_historique_statut CHECK (statut IN ('en_attente', 'accepte',
      'en_preparation', 'en_livraison', 'livre', 'attente_retour_materiel',
      'terminee', 'annulee'))
);

-- Un avis au plus par commande : UNIQUE(numero_commande).
CREATE TABLE avis(
   avis_id SERIAL,
   note INT NOT NULL,
   description TEXT NOT NULL,
   statut VARCHAR(10) NOT NULL DEFAULT 'en_attente',
   date_avis TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
   numero_commande VARCHAR(20) NOT NULL,
   PRIMARY KEY(avis_id),
   UNIQUE(numero_commande),
   FOREIGN KEY(numero_commande) REFERENCES commande(numero_commande),
   CONSTRAINT ck_avis_note CHECK (note BETWEEN 1 AND 5),
   CONSTRAINT ck_avis_statut CHECK (statut IN ('en_attente', 'valide', 'refuse'))
);

-- ---------------------------------------------------------------------
-- Horaires (pied de page, du lundi au dimanche)
-- ---------------------------------------------------------------------

-- Jour fermé : les deux heures sont vides. Jour ouvert : les deux sont
-- remplies et l'ouverture précède la fermeture.
CREATE TABLE horaire(
   horaire_id SERIAL,
   jour VARCHAR(10) NOT NULL,
   heure_ouverture TIME,
   heure_fermeture TIME,
   PRIMARY KEY(horaire_id),
   UNIQUE(jour),
   CONSTRAINT ck_horaire_jour CHECK (jour IN ('lundi', 'mardi', 'mercredi',
      'jeudi', 'vendredi', 'samedi', 'dimanche')),
   CONSTRAINT ck_horaire_heures CHECK (
      (heure_ouverture IS NULL AND heure_fermeture IS NULL)
      OR (heure_ouverture IS NOT NULL AND heure_fermeture IS NOT NULL
          AND heure_ouverture < heure_fermeture))
);

-- ---------------------------------------------------------------------
-- Index : PostgreSQL n'indexe pas les clés étrangères tout seul.
-- On indexe celles qui servent aux filtres de l'espace employé
-- (commandes par client, par statut) et aux jointures fréquentes.
-- ---------------------------------------------------------------------

CREATE INDEX idx_commande_utilisateur ON commande(utilisateur_id);
CREATE INDEX idx_commande_menu ON commande(menu_id);
CREATE INDEX idx_commande_statut ON commande(statut);
CREATE INDEX idx_historique_commande ON historique_statut(numero_commande);
CREATE INDEX idx_image_menu ON image(menu_id);

COMMIT;
