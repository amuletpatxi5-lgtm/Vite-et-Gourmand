<?php

declare(strict_types=1);

namespace App\Model;

use App\Database;

final class Avis
{
    public static function valides(int $limite = 6): array
    {
        $requete = Database::connexion()->prepare(
            "SELECT a.note, a.description, a.date_avis, u.prenom, left(u.nom, 1) AS initiale, m.titre AS menu_titre
            FROM avis a
            JOIN commande c ON c.numero_commande = a.numero_commande
            JOIN utilisateur u ON u.utilisateur_id = c.utilisateur_id
            JOIN menu m on m.menu_id = c.menu_id
            WHERE a.statut = 'valide'
            ORDER BY a.date_avis DESC
            LIMIT :limite"
        );
        $requete->execute(['limite' => $limite]);

        return $requete->fetchAll();
    }
}
