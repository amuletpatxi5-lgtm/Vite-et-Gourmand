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
}
