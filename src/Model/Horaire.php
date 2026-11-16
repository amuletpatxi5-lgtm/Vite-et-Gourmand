<?php

declare(strict_types=1);

namespace App\Model;

use App\Database;

final class Horaire
{
    // Les 7 jours dans l'ordre de la semaine (un tri alphabétique mettrait dimanche en premier)
    public static function tous(): array
    {
        $sql = "SELECT jour, heure_ouverture, heure_fermeture
                FROM horaire
                ORDER BY array_position(
                    ARRAY['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'],
                    jour::text
                )";

        return Database::connexion()->query($sql)->fetchAll();
    }
}
