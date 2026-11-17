-- ============================================================
-- Illustrations des menus Vite & Gourmand (galerie de la page)
-- ============================================================

BEGIN; 

DELETE FROM image; 

INSERT INTO image (chemin, texte_alternatif, ordre, menu_id)
SELECT v.chemin, v.texte, v.ordre, m.menu_id
FROM (VALUES
    ('/images/menus/noel-1.png', 'chapon doré, brin de thym, entouré de marrons et de pommes dauphines en alternance', 1, 'Menu de Noël'),
    ('/images/menus/noel-2.png', 'bûche chocolat, feuilles de houx et baies, éclats de praliné', 2, 'Menu de Noël'),
    ('/images/menus/paques-1.png', 'risotto crémeux, morceaux de cèpes, des copeaux de parmesan et des herbes', 1, 'Menu de Pâques'),
    ('/images/menus/paques-2.png', 'nid à base de copeaux de chocolat, mousse au centre, cinq œufs pralinés de couleurs différentes', 2, 'Menu de Pâques'),
    ('/images/menus/classique-1.png', 'pavé de boeuf, sauce bordelaise, pommes grenaille, thym', 1, 'Menu Classique'),
    ('/images/menus/classique-2.png', 'sept cannelés, disposé en fleur', 2, 'Menu Classique'),
    ('/images/menus/evenement-1.png', 'magret de canard disposé en fines tranches, purée de patate douce, cerises', 1, 'Menu Événement'),
    ('/images/menus/evenement-2.png', 'pavlova meringue, crème et fruits rouges', 2, 'Menu Événement'),
    ('/images/menus/vegetarien-1.png', 'velouté de légumes et potimarron, filet de crème', 1, 'Menu Végétarien'),
    ('/images/menus/vegetarien-2.png', 'tarte aux fruits, abricots, kiwis et framboises', 2, 'Menu Végétarien'),
    ('/images/menus/vegan-1.png', 'curry jaune, pois chiche, poivrons et haricots verts, riz basmati en accompagnement', 1, 'Menu Végan'),
    ('/images/menus/vegan-2.png', 'salade de fruits, feuilles de menthe', 2, 'Menu Végan')
) AS v(chemin, texte, ordre, titre)
JOIN menu m on m.titre = v.titre;

COMMIT;
