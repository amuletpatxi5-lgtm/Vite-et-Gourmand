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

    // US-08 un seul avis par commande, seulement par son client et une fois la commande terminée.
    public static function creer(string $numero, int $utilisateurId, int $note, string $description): bool
    {
        $requete = Database::connexion()->prepare(
            "INSERT INTO avis (note, description, numero_commande)
            SELECT :note, :description, c.numero_commande
            FROM commande c
            WHERE c.numero_commande = :numero AND c.utilisateur_id = :id AND c.statut = 'terminee'
            ON CONFLICT (numero_commande) DO NOTHING"
        );
        $requete->execute(['note' => $note, 'description' => $description, 'numero' => $numero, 'id' => $utilisateurId]);

        return $requete->rowCount() === 1;
    }

    // US-11 : les plus anciens avis d'abord
    public static function enAttente(): array
    {
        return Database::connexion()->query(
            "SELECT a.avis_id, a.note, a.description, a.date_avis, a.numero_commande,
                    u.prenom, u.nom, m.titre AS menu_titre
            FROM avis a
            JOIN commande c ON c.numero_commande = a.numero_commande
            JOIN utilisateur u ON u.utilisateur_id = c.utilisateur_id
            JOIN menu m ON m.menu_id = c.menu_id
            WHERE a.statut = 'en_attente'
            ORDER BY a.date_avis"
        )->fetchAll();
    }
    // US-11 : publie ou refuse un avis encore en attente (false s'il a déjà traité)
    public static function moderer(int $avisId, string $decision): bool
    {
        $requete = Database::connexion()->prepare(
            "UPDATE avis SET statut = :decision WHERE avis_id = :id AND statut = 'en_attente'"
        );
        $requete->execute(['decision' => $decision, 'id' => $avisId]);

        return $requete->rowCount() === 1;
    }
}
