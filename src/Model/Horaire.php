<?php

declare(strict_types=1);

namespace App\Model;

use App\Database;
use Throwable;

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

        // US-09 : nouveaux horaires des 7 jours, en une seule transaction ; [null, null] = fermé ce jour-là
    public static function modifier(array $horaires): void
    {
        $pdo = Database::connexion();
        $requete = $pdo->prepare(
            'UPDATE horaire SET heure_ouverture = :ouverture, heure_fermeture = :fermeture WHERE jour = :jour'
        );
        $pdo->beginTransaction();

        try {
            foreach ($horaires as $jour => [$ouverture, $fermeture]) {
                $requete->execute(['ouverture' => $ouverture, 'fermeture' => $fermeture, 'jour' => $jour]);
            }
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
