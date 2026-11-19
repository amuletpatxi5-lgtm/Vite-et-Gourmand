<?php

declare(strict_types=1);

namespace App\Model;

use App\Database;

final class Menu
{
    public static function filtrer(array $filtres): array
    {
        $sql = 'SELECT m.menu_id, m.titre, m.description, m.nombre_de_personne_minimum,
                       m.prix_par_personne, t.libelle AS theme, r.libelle AS regime
                FROM menu m
                JOIN theme t ON t.theme_id = m.theme_id
                JOIN regime r ON r.regime_id = m.regime_id
                WHERE m.actif';
        $parametres = [];

        if ($filtres['prix_min'] !== null) {
            $sql .= ' AND m.prix_par_personne >= :prix_min';
            $parametres['prix_min'] = $filtres['prix_min'];
        }
        if ($filtres['prix_max'] !== null) {
            $sql .= ' AND m.prix_par_personne <= :prix_max';
            $parametres['prix_max'] = $filtres['prix_max'];
        }
        if ($filtres['theme'] !== null) {
            $sql .= ' AND m.theme_id = :theme';
            $parametres['theme'] = $filtres['theme'];
        }
        if ($filtres['regime'] !== null) {
            $sql .= ' AND m.regime_id = :regime';
            $parametres['regime'] = $filtres['regime'];
        }
        // Menus possibles pour ce nombre de convives (minimum du menu atteint)
        if ($filtres['personnes'] !== null) {
            $sql .= ' AND m.nombre_de_personne_minimum <= :personnes';
            $parametres['personnes'] = $filtres['personnes'];
        }

        $sql .= ' ORDER BY m.titre';

        $requete = Database::connexion()->prepare($sql);
        $requete->execute($parametres);

        return $requete->fetchAll();
    }

    public static function themes(): array
    {
        return Database::connexion()->query('SELECT theme_id, libelle FROM theme ORDER BY libelle')->fetchAll();
    }

    public static function regimes(): array
    {
        return Database::connexion()->query('SELECT regime_id, libelle FROM regime ORDER BY libelle')->fetchAll();
    }

    public static function trouver(int $id): ?array
    {
        $requete = Database::connexion()->prepare(
            'SELECT m.menu_id, m.titre, m.description, m.nombre_de_personne_minimum,
                    m.prix_par_personne, m.conditions, m.quantite_restante,
                    t.libelle AS theme, r.libelle AS regime
             FROM menu m
             JOIN theme t ON t.theme_id = m.theme_id
             JOIN regime r ON r.regime_id = m.regime_id
             WHERE m.menu_id = :id AND m.actif'
        );
        $requete->execute(['id' => $id]);

        return $requete->fetch() ?: null;
    }

    // Plats du menu et leurs allergènes, rangés par type : ['entree' => [...], 'plat' => [...], 'dessert' => [...]]
    public static function plats(int $id): array
    {
        $requete = Database::connexion()->prepare(
            "SELECT p.titre_plat, p.type_plat,
                    string_agg(a.libelle, ', ' ORDER BY a.libelle) AS allergenes
             FROM compose c
             JOIN plat p ON p.plat_id = c.plat_id
             LEFT JOIN contient ct ON ct.plat_id = p.plat_id
             LEFT JOIN allergene a ON a.allergene_id = ct.allergene_id
             WHERE c.menu_id = :id
             GROUP BY p.plat_id, p.titre_plat, p.type_plat
             ORDER BY p.titre_plat"
        );
        $requete->execute(['id' => $id]);

        $plats = ['entree' => [], 'plat' => [], 'dessert' => []];
        foreach ($requete->fetchAll() as $plat) {
            $plats[$plat['type_plat']][] = $plat;
        }

        return $plats;
    }

    public static function images(int $id): array
    {
        $requete = Database::connexion()->prepare(
            'SELECT chemin, texte_alternatif FROM image WHERE menu_id = :id ORDER BY ordre'
        );
        $requete->execute(['id' => $id]);

        return $requete->fetchAll();
    }

    // US-09 : tous les menus, y compris ceux retirés de la vente
    public static function tousPourGestion(): array
    {
        return Database::connexion()->query(
            'SELECT menu_id, titre, prix_par_personne, quantite_restante, actif FROM menu ORDER BY titre'
            )->fetchAll();
    }

    // US-09 : prix, stock et mise en vente d'un menu
    public static function modifierVente(int $id, string $prix, int $stock, bool $actif): bool
    {
        $requete = Database::connexion()->prepare(
            'UPDATE menu SET prix_par_personne = :prix, quantite_restante = :stock, actif = :actif WHERE menu_id = :id'
        );
         $requete->execute(['prix' => $prix, 'stock' => $stock, 'actif' => $actif ? 'true' : 'false', 'id' => $id]);

        return $requete->rowCount() === 1;
    }
}
