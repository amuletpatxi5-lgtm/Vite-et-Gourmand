# Vite & Gourmand : menus, plats et allergènes (données de travail)

Source : ancien projet ECF-Vite-et-Gourmand-Patxi-2026-10-25, fichier vite-gourmand/sql/02_data.sql.
Statut : propositions à valider. Ces données serviront aux maquettes (mockups), puis au jeu de données SQL.
Les incohérences relevées à la reprise sont tranchées en bas du fichier.

## Thèmes (liste de l'énoncé)
Noël, Pâques, Classique, Événement

## Régimes
Classique, Végétarien, Vegan, Sans gluten (le dernier est un ajout, l'énoncé autorise à enrichir la liste)

## Les 14 allergènes à déclaration obligatoire (règlement UE 1169/2011, annexe II)
1. Gluten
2. Crustacés
3. Œufs
4. Poissons
5. Arachides
6. Soja
7. Lait
8. Fruits à coque
9. Céleri
10. Moutarde
11. Graines de sésame
12. Sulfites
13. Lupin
14. Mollusques

## Plats et allergènes

### Entrées
| N° | Plat | Description | Allergènes |
|---|---|---|---|
| E1 | Foie gras mi-cuit, chutney de figues | Foie gras de canard du Sud-Ouest, chutney maison, pain d'épices | Gluten, Sulfites |
| E2 | Saumon gravlax, crème d'aneth | Saumon mariné 48 h au sel, sucre et aneth, crème d'aneth | Poissons, Lait |
| E3 | Velouté de potimarron aux châtaignes | Velouté onctueux, éclats de châtaignes grillées, crème légère | Lait, Fruits à coque |
| E4 | Œufs mimosa revisités | Œufs de plein air, mayonnaise à la moutarde de Bordeaux, herbes fraîches | Œufs, Moutarde |
| E5 | Tartare de légumes croquants, vinaigrette aux agrumes | Courgette, fenouil, radis et pomme verte, huile d'olive et citron | Aucun |
| E6 | Asperges vertes, sauce mousseline | Asperges des Landes, sauce mousseline au beurre et jaune d'œuf | Œufs, Lait |
| E7 | Huîtres d'Arcachon, mignonnette | Six huîtres n°3 du bassin, vinaigre à l'échalote, pain de seigle | Mollusques, Gluten |

### Plats
| N° | Plat | Description | Allergènes |
|---|---|---|---|
| P1 | Chapon fermier rôti, marrons et pommes dauphines | Chapon des Landes, jus corsé, marrons confits | Gluten, Œufs, Lait |
| P2 | Agneau de Pauillac, jus au thym, gratin dauphinois | Gigot cuit sept heures, gratin crémeux | Lait |
| P3 | Risotto aux cèpes et parmesan | Riz carnaroli, cèpes du Médoc, parmesan 24 mois, vin blanc | Lait, Sulfites |
| P4 | Curry de légumes au lait de coco, riz basmati | Légumes de saison, lait de coco, épices douces, coriandre | Aucun |
| P5 | Lotte à l'armoricaine, riz pilaf | Lotte de la criée, sauce tomate, cognac et crustacés | Poissons, Crustacés, Sulfites |
| P6 | Magret de canard, sauce aux cerises, purée de patate douce | Magret du Gers rosé, sauce aux cerises et vin rouge | Lait, Sulfites |
| P7 | Pavé de bœuf, sauce bordelaise, pommes grenaille | Bœuf de Bazas, sauce au vin de Bordeaux et moelle | Lait, Sulfites |

### Desserts
| N° | Plat | Description | Allergènes |
|---|---|---|---|
| D1 | Bûche de Noël chocolat-praliné | Biscuit, mousse chocolat noir 70 %, croustillant praliné noisette | Gluten, Œufs, Lait, Fruits à coque |
| D2 | Nid de Pâques, mousse chocolat et œufs pralinés | Mousse chocolat au lait, œufs pralinés, crumble cacao | Œufs, Lait, Fruits à coque |
| D3 | Tarte aux fruits de saison | Pâte sablée, crème pâtissière vanille, fruits du marché | Gluten, Œufs, Lait |
| D4 | Salade de fruits frais à la menthe | Fruits de saison, sirop léger au citron vert et menthe | Aucun |
| D5 | Cannelés bordelais | Petits cannelés caramélisés, cœur moelleux au rhum et vanille | Gluten, Œufs, Lait |
| D6 | Pavlova aux fruits rouges | Meringue croustillante, crème fouettée, fruits rouges | Œufs, Lait |

## Menus

| N° | Menu | Thème | Régime | Min. pers. | Prix pour le minimum | Stock | Composition |
|---|---|---|---|---|---|---|---|
| M1 | Réveillon de Noël | Noël | Classique | 6 | 174 € | 12 | E1, P1, D1 |
| M2 | Noël végétarien | Noël | Végétarien | 4 | 100 € | 8 | E3, P3, D1 |
| M3 | Brunch de Pâques | Pâques | Classique | 6 | 150 € | 10 | E6, P2, D2 |
| M4 | Pâques en famille (vegan) | Pâques | Vegan | 4 | 88 € | 6 | E5, P4, D4 |
| M5 | Déjeuner bordelais | Classique | Classique | 2 | 56 € | 20 | E7, P7, D5 |
| M6 | Table végétarienne | Classique | Végétarien | 2 | 48 € | 15 | E3, P3, D3 |
| M7 | Cocktail d'entreprise | Événement | Classique | 10 | 320 € | 5 | E2, P6, D6 |
| M8 | Buffet de mariage sans gluten | Événement | Sans gluten | 20 | 760 € | 3 | E2, P7, D6 |

### Descriptions
- M1 Réveillon de Noël : Le grand classique de Julie et José : foie gras, chapon fermier et bûche maison. Un repas de fête complet, livré prêt à réchauffer.
- M2 Noël végétarien : Un Noël sans viande mais avec tout le goût : velouté de potimarron, risotto aux cèpes du Médoc et bûche chocolat-praliné.
- M3 Brunch de Pâques : Asperges des Landes, agneau de Pauillac cuit sept heures et nid de Pâques chocolaté : le déjeuner de printemps par excellence.
- M4 Pâques en famille (vegan) : Fraîcheur et douceur : tartare de légumes croquants, curry au lait de coco et salade de fruits à la menthe. 100 % végétal.
- M5 Déjeuner bordelais : Huîtres du bassin, pavé de bœuf de Bazas sauce bordelaise et cannelés : Bordeaux dans l'assiette, pour deux ou pour dix.
- M6 Table végétarienne : Velouté de saison, risotto aux cèpes et tarte aux fruits du marché. Simple, généreux, de saison.
- M7 Cocktail d'entreprise : Pour vos réceptions : saumon gravlax, magret aux cerises en bouchées et pavlova. Service en plateaux et chafing dishes.
- M8 Buffet de mariage sans gluten : Un buffet généreux et sans gluten pour le plus beau jour : gravlax, pavé de bœuf sauce bordelaise, pavlova aux fruits rouges.

### Conditions (encadré "Conditions de commande")
- M1 : À commander au moins 10 jours avant la prestation. Les plats sont livrés froids avec les consignes de réchauffage. Conservation 48 h au réfrigérateur.
- M2 : À commander au moins 10 jours avant la prestation. Livré froid, à réchauffer (consignes fournies). Conservation 48 h au réfrigérateur.
- M3 : À commander au moins 7 jours avant la prestation. Disponible de mi-mars à fin avril. Livré chaud sur demande (créneau 11h-13h).
- M4 : À commander au moins 7 jours avant la prestation. Livré froid, le curry se réchauffe en 10 minutes.
- M5 : À commander au moins 3 jours avant. Les huîtres sont ouvertes le matin même et doivent être consommées le jour de la livraison.
- M6 : À commander au moins 3 jours avant. Livré froid, à réchauffer. Conservation 48 h au réfrigérateur.
- M7 : À commander au moins 15 jours avant. Du matériel est prêté (plateaux, chafing dishes, nappage) : il doit être restitué sous 10 jours ouvrés après la prestation, faute de quoi 600 € de frais sont facturés (voir CGV).
- M8 : À commander au moins 30 jours avant, acompte de 30 % à la commande. Matériel prêté (restitution sous 10 jours ouvrés, 600 € de frais sinon). Devis personnalisé au-delà de 80 personnes.

## Horaires (pied de page des maquettes)
Lundi : fermé · Mardi à vendredi : 9 h - 19 h · Samedi : 9 h - 18 h · Dimanche : 9 h - 13 h

## Décisions prises le 6 novembre 2026
1. Gravlax (E2) : blinis supprimés. Le plat ne contient plus de gluten ni d'œufs, le buffet de mariage sans gluten (M8) devient cohérent.
2. Foie gras (E1) : gluten déclaré (pain d'épices).
3. Risotto au parmesan (P3) : conservé dans les menus végétariens M2 et M6. Ce sont des menus végétariens, pas vegan.
4. Magret (P6) : servi en bouchées dans le cocktail d'entreprise (M7), servi en plat avec sa purée dans un repas. Le format suit le type de prestation.
