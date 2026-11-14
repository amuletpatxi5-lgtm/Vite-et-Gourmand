CREATE TABLE ROLE(
   role_id COUNTER,
   libelle VARCHAR(50) NOT NULL,
   PRIMARY KEY(role_id),
   UNIQUE(libelle)
);

CREATE TABLE THEME(
   theme_id COUNTER,
   libelle VARCHAR(50) NOT NULL,
   PRIMARY KEY(theme_id),
   UNIQUE(libelle)
);

CREATE TABLE REGIME(
   regime_id COUNTER,
   libelle VARCHAR(50) NOT NULL,
   PRIMARY KEY(regime_id),
   UNIQUE(libelle)
);

CREATE TABLE ALLERGENE(
   allergene_id COUNTER,
   libelle VARCHAR(50) NOT NULL,
   PRIMARY KEY(allergene_id),
   UNIQUE(libelle)
);

CREATE TABLE UTILISATEUR(
   utilisateur_id COUNTER,
   email VARCHAR(255) NOT NULL,
   mot_de_passe VARCHAR(255) NOT NULL,
   nom VARCHAR(50),
   prenom VARCHAR(50),
   telephone VARCHAR(20),
   adresse_postale VARCHAR(255),
   code_postal CHAR(5),
   ville VARCHAR(100),
   pays VARCHAR(50),
   actif LOGICAL NOT NULL,
   role_id INT NOT NULL,
   PRIMARY KEY(utilisateur_id),
   UNIQUE(email),
   FOREIGN KEY(role_id) REFERENCES ROLE(role_id)
);

CREATE TABLE MENU(
   menu_id COUNTER,
   titre VARCHAR(100) NOT NULL,
   description TEXT NOT NULL,
   nombre_de_personne_minimum INT NOT NULL,
   prix_par_personne DECIMAL(6,2) NOT NULL,
   conditions TEXT NOT NULL,
   quantite_restante INT NOT NULL,
   actif LOGICAL NOT NULL,
   regime_id INT NOT NULL,
   theme_id INT NOT NULL,
   PRIMARY KEY(menu_id),
   UNIQUE(titre),
   FOREIGN KEY(regime_id) REFERENCES REGIME(regime_id),
   FOREIGN KEY(theme_id) REFERENCES THEME(theme_id)
);

CREATE TABLE PLAT(
   plat_id COUNTER,
   titre_plat VARCHAR(100) NOT NULL,
   type_plat VARCHAR(10) NOT NULL,
   PRIMARY KEY(plat_id)
);

CREATE TABLE IMAGE(
   image_id COUNTER,
   chemin VARCHAR(255) NOT NULL,
   texte_alternatif VARCHAR(255) NOT NULL,
   ordre INT NOT NULL,
   menu_id INT NOT NULL,
   PRIMARY KEY(image_id),
   FOREIGN KEY(menu_id) REFERENCES MENU(menu_id)
);

CREATE TABLE COMMANDE(
   numero_commande VARCHAR(20),
   date_commande DATETIME NOT NULL,
   date_prestation DATE NOT NULL,
   heure_livraison TIME NOT NULL,
   nombre_personne INT NOT NULL,
   prix_menu DECIMAL(8,2) NOT NULL,
   prix_livraison DECIMAL(6,2) NOT NULL,
   statut VARCHAR(30) NOT NULL,
   pret_materiel LOGICAL NOT NULL,
   adresse_prestation VARCHAR(255) NOT NULL,
   code_postal_prestation CHAR(5) NOT NULL,
   ville_prestation VARCHAR(100) NOT NULL,
   distance_km DECIMAL(6,2) NOT NULL,
   remise DECIMAL(8,2) NOT NULL,
   prix_total DECIMAL(8,2) NOT NULL,
   mode_contact VARCHAR(10),
   motif_annulation TEXT,
   date_restitution DATE,
   menu_id INT NOT NULL,
   utilisateur_id INT NOT NULL,
   PRIMARY KEY(numero_commande),
   FOREIGN KEY(menu_id) REFERENCES MENU(menu_id),
   FOREIGN KEY(utilisateur_id) REFERENCES UTILISATEUR(utilisateur_id)
);

CREATE TABLE AVIS(
   avis_id COUNTER,
   note INT NOT NULL,
   description TEXT NOT NULL,
   statut VARCHAR(10) NOT NULL,
   date_avis DATETIME NOT NULL,
   numero_commande VARCHAR(20) NOT NULL,
   PRIMARY KEY(avis_id),
   UNIQUE(numero_commande),
   FOREIGN KEY(numero_commande) REFERENCES COMMANDE(numero_commande)
);

CREATE TABLE HORAIRE(
   horaire_id COUNTER,
   jour VARCHAR(10) NOT NULL,
   heure_ouverture TIME,
   heure_fermeture TIME,
   PRIMARY KEY(horaire_id),
   UNIQUE(jour)
);

CREATE TABLE HISTORIQUE_STATUT(
   historique_id COUNTER,
   statut VARCHAR(30) NOT NULL,
   date_changement DATETIME NOT NULL,
   numero_commande VARCHAR(20) NOT NULL,
   PRIMARY KEY(historique_id),
   FOREIGN KEY(numero_commande) REFERENCES COMMANDE(numero_commande)
);

CREATE TABLE REINITIALISATION(
   reinitialisation_id COUNTER,
   jeton_hash CHAR(64) NOT NULL,
   date_demande DATETIME NOT NULL,
   date_expiration DATETIME NOT NULL,
   date_utilisation DATETIME,
   utilisateur_id INT NOT NULL,
   PRIMARY KEY(reinitialisation_id),
   UNIQUE(jeton_hash),
   FOREIGN KEY(utilisateur_id) REFERENCES UTILISATEUR(utilisateur_id)
);

CREATE TABLE compose(
   menu_id INT,
   plat_id INT,
   PRIMARY KEY(menu_id, plat_id),
   FOREIGN KEY(menu_id) REFERENCES MENU(menu_id),
   FOREIGN KEY(plat_id) REFERENCES PLAT(plat_id)
);

CREATE TABLE contient(
   allergene_id INT,
   plat_id INT,
   PRIMARY KEY(allergene_id, plat_id),
   FOREIGN KEY(allergene_id) REFERENCES ALLERGENE(allergene_id),
   FOREIGN KEY(plat_id) REFERENCES PLAT(plat_id)
);
