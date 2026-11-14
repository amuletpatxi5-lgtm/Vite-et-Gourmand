-- =====================================================================
-- Vite & Gourmand : données de démonstration, partie catalogue
-- Source : Database/donnees/menus-et-allergenes.md (aligné sur les maquettes)
-- À exécuter juste après 01-schema.sql, sur une base vide.
--
-- Les comptes de test, commandes et avis viendront dans un second temps,
-- une fois la connexion PHP en place (empreintes password_hash).
--
-- Les clés étrangères sont retrouvées par leur libellé ou leur titre
-- (sous-requêtes) : le script ne dépend pas des numéros SERIAL.
-- =====================================================================

BEGIN;

-- ---------------------------------------------------------------------
-- Tables de référence
-- ---------------------------------------------------------------------

INSERT INTO role (libelle) VALUES
   ('utilisateur'),
   ('employe'),
   ('administrateur');

INSERT INTO theme (libelle) VALUES
   ('Noël'),
   ('Pâques'),
   ('Classique'),
   ('Événement');

INSERT INTO regime (libelle) VALUES
   ('Classique'),
   ('Végétarien'),
   ('Végan');

-- Les 14 allergènes à déclaration obligatoire
INSERT INTO allergene (libelle) VALUES
   ('Gluten'),
   ('Crustacés'),
   ('Œufs'),
   ('Poissons'),
   ('Arachides'),
   ('Soja'),
   ('Lait'),
   ('Fruits à coque'),
   ('Céleri'),
   ('Moutarde'),
   ('Graines de sésame'),
   ('Sulfites'),
   ('Lupin'),
   ('Mollusques');

-- ---------------------------------------------------------------------
-- Plats (7 entrées, 7 plats, 6 desserts)
-- ---------------------------------------------------------------------

INSERT INTO plat (titre_plat, type_plat) VALUES
   ('Foie gras mi-cuit, chutney de figues', 'entree'),                       -- E1
   ('Saumon gravlax, crème d''aneth', 'entree'),                             -- E2
   ('Velouté de potimarron aux châtaignes', 'entree'),                       -- E3
   ('Œufs mimosa revisités', 'entree'),                                      -- E4
   ('Tartare de légumes croquants, vinaigrette aux agrumes', 'entree'),      -- E5
   ('Asperges vertes, sauce mousseline', 'entree'),                          -- E6
   ('Huîtres d''Arcachon, mignonnette', 'entree'),                           -- E7
   ('Chapon fermier rôti, marrons et pommes dauphines', 'plat'),             -- P1
   ('Agneau de Pauillac, jus au thym, gratin dauphinois', 'plat'),           -- P2
   ('Risotto aux cèpes et parmesan', 'plat'),                                -- P3
   ('Curry de légumes au lait de coco, riz basmati', 'plat'),                -- P4
   ('Lotte à l''armoricaine, riz pilaf', 'plat'),                            -- P5
   ('Magret de canard, sauce aux cerises, purée de patate douce', 'plat'),   -- P6
   ('Pavé de bœuf, sauce bordelaise, pommes grenaille', 'plat'),             -- P7
   ('Bûche de Noël chocolat-praliné', 'dessert'),                            -- D1
   ('Nid de Pâques, mousse chocolat et œufs pralinés', 'dessert'),           -- D2
   ('Tarte aux fruits de saison', 'dessert'),                                -- D3
   ('Salade de fruits frais à la menthe', 'dessert'),                        -- D4
   ('Cannelés bordelais', 'dessert'),                                        -- D5
   ('Pavlova aux fruits rouges', 'dessert');                                 -- D6

-- ---------------------------------------------------------------------
-- Allergènes de chaque plat (E5, P4 et D4 n'en ont aucun)
-- Chaque ligne associe un titre de plat à un libellé d'allergène.
-- ---------------------------------------------------------------------

INSERT INTO contient (plat_id, allergene_id)
SELECT p.plat_id, a.allergene_id
FROM (VALUES
   ('Foie gras mi-cuit, chutney de figues', 'Gluten'),
   ('Foie gras mi-cuit, chutney de figues', 'Sulfites'),
   ('Saumon gravlax, crème d''aneth', 'Poissons'),
   ('Saumon gravlax, crème d''aneth', 'Lait'),
   ('Velouté de potimarron aux châtaignes', 'Lait'),
   ('Velouté de potimarron aux châtaignes', 'Fruits à coque'),
   ('Œufs mimosa revisités', 'Œufs'),
   ('Œufs mimosa revisités', 'Moutarde'),
   ('Asperges vertes, sauce mousseline', 'Œufs'),
   ('Asperges vertes, sauce mousseline', 'Lait'),
   ('Huîtres d''Arcachon, mignonnette', 'Mollusques'),
   ('Huîtres d''Arcachon, mignonnette', 'Gluten'),
   ('Chapon fermier rôti, marrons et pommes dauphines', 'Gluten'),
   ('Chapon fermier rôti, marrons et pommes dauphines', 'Œufs'),
   ('Chapon fermier rôti, marrons et pommes dauphines', 'Lait'),
   ('Agneau de Pauillac, jus au thym, gratin dauphinois', 'Lait'),
   ('Risotto aux cèpes et parmesan', 'Lait'),
   ('Risotto aux cèpes et parmesan', 'Sulfites'),
   ('Lotte à l''armoricaine, riz pilaf', 'Poissons'),
   ('Lotte à l''armoricaine, riz pilaf', 'Crustacés'),
   ('Lotte à l''armoricaine, riz pilaf', 'Sulfites'),
   ('Magret de canard, sauce aux cerises, purée de patate douce', 'Lait'),
   ('Magret de canard, sauce aux cerises, purée de patate douce', 'Sulfites'),
   ('Pavé de bœuf, sauce bordelaise, pommes grenaille', 'Lait'),
   ('Pavé de bœuf, sauce bordelaise, pommes grenaille', 'Sulfites'),
   ('Bûche de Noël chocolat-praliné', 'Gluten'),
   ('Bûche de Noël chocolat-praliné', 'Œufs'),
   ('Bûche de Noël chocolat-praliné', 'Lait'),
   ('Bûche de Noël chocolat-praliné', 'Fruits à coque'),
   ('Nid de Pâques, mousse chocolat et œufs pralinés', 'Œufs'),
   ('Nid de Pâques, mousse chocolat et œufs pralinés', 'Lait'),
   ('Nid de Pâques, mousse chocolat et œufs pralinés', 'Fruits à coque'),
   ('Tarte aux fruits de saison', 'Gluten'),
   ('Tarte aux fruits de saison', 'Œufs'),
   ('Tarte aux fruits de saison', 'Lait'),
   ('Cannelés bordelais', 'Gluten'),
   ('Cannelés bordelais', 'Œufs'),
   ('Cannelés bordelais', 'Lait'),
   ('Pavlova aux fruits rouges', 'Œufs'),
   ('Pavlova aux fruits rouges', 'Lait')
) AS v(titre_plat, allergene)
JOIN plat p ON p.titre_plat = v.titre_plat
JOIN allergene a ON a.libelle = v.allergene;

-- ---------------------------------------------------------------------
-- Menus (prix par personne ; le prix affiché = prix x minimum de personnes)
-- ---------------------------------------------------------------------

INSERT INTO menu (titre, description, nombre_de_personne_minimum,
   prix_par_personne, conditions, quantite_restante, theme_id, regime_id)
SELECT v.titre, v.description, v.minimum, v.prix, v.conditions, v.stock,
   t.theme_id, r.regime_id
FROM (VALUES
   (1, 'Menu de Noël',
    'Le grand classique de Julie et José : foie gras, chapon fermier et bûche maison. Un repas de fête complet, livré prêt à réchauffer.',
    8, 34.00,
    'Commande à passer 7 jours avant la prestation. Matériel à restituer sous 10 jours.',
    12, 'Noël', 'Classique'),
   (2, 'Menu de Pâques',
    'Asperges des Landes sauce mousseline, risotto aux cèpes du Médoc et nid de Pâques chocolaté : un déjeuner de printemps sans viande.',
    4, 22.00,
    'À commander au moins 7 jours avant la prestation. Disponible de mi-mars à fin avril. Livré chaud sur demande (créneau 11h-13h).',
    5, 'Pâques', 'Végétarien'),
   (3, 'Menu Classique',
    'Huîtres du bassin, pavé de bœuf de Bazas sauce bordelaise et cannelés : Bordeaux dans l''assiette, pour deux ou pour dix.',
    2, 28.00,
    'À commander au moins 3 jours avant. Les huîtres sont ouvertes le matin même et doivent être consommées le jour de la livraison.',
    14, 'Classique', 'Classique'),
   (4, 'Menu Événement',
    'Pour vos réceptions : saumon gravlax, magret aux cerises en bouchées et pavlova. Service en plateaux et chafing dishes.',
    8, 29.00,
    'À commander au moins 15 jours avant. Du matériel est prêté (plateaux, chafing dishes, nappage) : il doit être restitué sous 10 jours ouvrés après la prestation, faute de quoi 600 € de frais sont facturés (voir CGV).',
    5, 'Événement', 'Classique'),
   (5, 'Menu Végétarien',
    'Velouté de saison, risotto aux cèpes et tarte aux fruits du marché. Simple, généreux, de saison.',
    4, 25.00,
    'À commander au moins 3 jours avant. Livré froid, à réchauffer. Conservation 48 h au réfrigérateur.',
    8, 'Classique', 'Végétarien'),
   (6, 'Menu Végan',
    'Fraîcheur et douceur : tartare de légumes croquants, curry au lait de coco et salade de fruits à la menthe. 100 % végétal.',
    6, 25.00,
    'À commander au moins 7 jours avant la prestation. Livré froid, le curry se réchauffe en 10 minutes.',
    10, 'Classique', 'Végan')
) AS v(ordre, titre, description, minimum, prix, conditions, stock, theme, regime)
JOIN theme t ON t.libelle = v.theme
JOIN regime r ON r.libelle = v.regime
ORDER BY v.ordre;   -- garde l'ordre M1 à M6 pour les numéros menu_id

-- ---------------------------------------------------------------------
-- Composition des menus (une entrée, un plat, un dessert chacun)
-- P3 (risotto) figure dans deux menus ; P2 et P5 ne sont dans aucun.
-- ---------------------------------------------------------------------

INSERT INTO compose (menu_id, plat_id)
SELECT m.menu_id, p.plat_id
FROM (VALUES
   ('Menu de Noël', 'Foie gras mi-cuit, chutney de figues'),
   ('Menu de Noël', 'Chapon fermier rôti, marrons et pommes dauphines'),
   ('Menu de Noël', 'Bûche de Noël chocolat-praliné'),
   ('Menu de Pâques', 'Asperges vertes, sauce mousseline'),
   ('Menu de Pâques', 'Risotto aux cèpes et parmesan'),
   ('Menu de Pâques', 'Nid de Pâques, mousse chocolat et œufs pralinés'),
   ('Menu Classique', 'Huîtres d''Arcachon, mignonnette'),
   ('Menu Classique', 'Pavé de bœuf, sauce bordelaise, pommes grenaille'),
   ('Menu Classique', 'Cannelés bordelais'),
   ('Menu Événement', 'Saumon gravlax, crème d''aneth'),
   ('Menu Événement', 'Magret de canard, sauce aux cerises, purée de patate douce'),
   ('Menu Événement', 'Pavlova aux fruits rouges'),
   ('Menu Végétarien', 'Velouté de potimarron aux châtaignes'),
   ('Menu Végétarien', 'Risotto aux cèpes et parmesan'),
   ('Menu Végétarien', 'Tarte aux fruits de saison'),
   ('Menu Végan', 'Tartare de légumes croquants, vinaigrette aux agrumes'),
   ('Menu Végan', 'Curry de légumes au lait de coco, riz basmati'),
   ('Menu Végan', 'Salade de fruits frais à la menthe')
) AS v(titre, titre_plat)
JOIN menu m ON m.titre = v.titre
JOIN plat p ON p.titre_plat = v.titre_plat;

-- ---------------------------------------------------------------------
-- Horaires (pied de page) : du lundi au dimanche, 9 h - 19 h
-- ---------------------------------------------------------------------

INSERT INTO horaire (jour, heure_ouverture, heure_fermeture) VALUES
   ('lundi', '09:00', '19:00'),
   ('mardi', '09:00', '19:00'),
   ('mercredi', '09:00', '19:00'),
   ('jeudi', '09:00', '19:00'),
   ('vendredi', '09:00', '19:00'),
   ('samedi', '09:00', '19:00'),
   ('dimanche', '09:00', '19:00');

COMMIT;
