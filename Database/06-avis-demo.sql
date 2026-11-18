-- =====================================================================
-- Vite & Gourmand : avis de démonstration pour la page d'accueil (US-01)
-- Un avis est lié à une commande : on crée donc des clients et des commandes terminées.
-- Comptes de démonstration désactivés et SANS mot de passe utilisable ('!' n'est pas une
-- empreinte bcrypt) : impossible de s'y connecter ou de réinitialiser leur mot de passe.
-- Commandes de 2025 (VG-2025-…) : pas de conflit avec la numérotation de 2026.
-- Rejouable : ON CONFLICT DO NOTHING.
-- =====================================================================

-- =====================================================================
-- Vite & Gourmand : avis de démonstration pour la page d'accueil (US-01)
-- Un avis est lié à une commande : on crée donc des clients et des commandes terminées.
-- Comptes de démonstration désactivés et SANS mot de passe utilisable ('!' n'est pas une
-- empreinte bcrypt) : impossible de s'y connecter ou de réinitialiser leur mot de passe.
-- Commandes de 2025 (VG-2025-…) : pas de conflit avec la numérotation de 2026.
-- Rejouable : ON CONFLICT DO NOTHING.
-- =====================================================================

INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, actif, role_id)
SELECT v.nom, v.prenom, v.email, '!', false, (SELECT role_id FROM role WHERE libelle = 'utilisateur')
FROM (VALUES
   ('Lacoste', 'Marie', 'demo-marie@vite-gourmand.test'),
   ('Bernard', 'Thomas', 'demo-thomas@vite-gourmand.test'),
   ('Moreau', 'Sophie', 'demo-sophie@vite-gourmand.test'),
   ('Garcia', 'Antoine', 'demo-antoine@vite-gourmand.test')
) AS v(nom, prenom, email)
ON CONFLICT (email) DO NOTHING;

-- Livraison dans Bordeaux (0 €) et nombre de personnes sous le seuil de remise : prix = prix par personne × personnes
INSERT INTO commande (numero_commande, date_commande, date_prestation, heure_livraison, nombre_personne,
   prix_menu, prix_livraison, prix_total, statut, adresse_prestation, code_postal_prestation, ville_prestation,
   menu_id, utilisateur_id)
SELECT v.numero, v.date_commande, v.date_prestation, '12:00', v.personnes,
   m.prix_par_personne * v.personnes, 0, m.prix_par_personne * v.personnes, 'terminee',
   'Place de la Bourse', '33000', 'Bordeaux', m.menu_id, u.utilisateur_id
FROM (VALUES
   ('VG-2025-0101', TIMESTAMP '2025-12-08 10:00', DATE '2025-12-24', 8, 'Menu de Noël', 'demo-marie@vite-gourmand.test'),
   ('VG-2025-0102', TIMESTAMP '2025-04-02 15:30', DATE '2025-04-20', 5, 'Menu de Pâques', 'demo-thomas@vite-gourmand.test'),
   ('VG-2025-0103', TIMESTAMP '2025-06-10 09:15', DATE '2025-06-21', 10, 'Menu Événement', 'demo-sophie@vite-gourmand.test'),
   ('VG-2025-0104', TIMESTAMP '2025-09-01 18:45', DATE '2025-09-06', 6, 'Menu Classique', 'demo-antoine@vite-gourmand.test'),
   ('VG-2025-0105', TIMESTAMP '2025-10-03 11:00', DATE '2025-10-11', 6, 'Menu Végétarien', 'demo-marie@vite-gourmand.test'),
   ('VG-2025-0106', TIMESTAMP '2025-11-02 14:00', DATE '2025-11-15', 8, 'Menu Végan', 'demo-thomas@vite-gourmand.test')
) AS v(numero, date_commande, date_prestation, personnes, menu, email)
JOIN menu m ON m.titre = v.menu
JOIN utilisateur u ON u.email = v.email
ON CONFLICT (numero_commande) DO NOTHING;

-- 4 avis validés (affichés), 1 en attente et 1 refusé (pour vérifier qu'ils ne s'affichent pas)
INSERT INTO avis (note, description, statut, date_avis, numero_commande) VALUES
   (5, 'Un réveillon parfait : le chapon était fondant et la bûche a fait l''unanimité. Livraison à l''heure, rien à redire.', 'valide', '2025-12-27 18:00', 'VG-2025-0101'),
   (4, 'Très bon déjeuner de Pâques, le risotto aux cèpes était délicieux. Un peu juste en quantité pour les grands mangeurs.', 'valide', '2025-04-23 20:10', 'VG-2025-0102'),
   (5, 'Julie et José ont géré notre fête de fin d''année scolaire de A à Z. Le matériel prêté était impeccable.', 'valide', '2025-06-24 09:30', 'VG-2025-0103'),
   (5, 'Les huîtres et le pavé de Bazas comme au restaurant, chez nous. On recommande les yeux fermés !', 'valide', '2025-09-08 12:00', 'VG-2025-0104'),
   (4, 'Bon menu végétarien, en attente de modération.', 'en_attente', '2025-10-13 19:00', 'VG-2025-0105'),
   (1, 'Avis refusé par la modération (propos hors sujet).', 'refuse', '2025-11-17 08:00', 'VG-2025-0106')
ON CONFLICT (numero_commande) DO NOTHING;
