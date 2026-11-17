<?php

declare(strict_types=1);

namespace App\Model;

use App\Database;
use Throwable;

final class Reinitialisation
{
    // C'est que l'empreinte du jeton qui est stocké
    public static function creer(int $utilisateurId, string $jetonHash): void
    {
        $requete = Database::connexion()->prepare(
            "INSERT INTO reinitialisation (jeton_hash, date_expiration, utilisateur_id)
            VALUES (:jeton_hash, CURRENT_TIMESTAMP + INTERVAL '1hour', :utilisateur_id)"
        );
        $requete->execute(['jeton_hash' => $jetonHash, 'utilisateur_id' => $utilisateurId]);
    }

    public static function estValide(string $jetonHash): bool
    {
        $requete = Database::connexion()->prepare(
            'SELECT 1
            FROM reinitialisation r
            JOIN utilisateur u ON u.utilisateur_id = r.utilisateur_id
            WHERE r.jeton_hash = :jeton_hash
               AND r.date_utilisation IS NULL
               AND r.date_expiration > CURRENT_TIMESTAMP
               AND u.actif'
        );
        $requete->execute(['jeton_hash' =>  $jetonHash]);

        return $requete->fetchColumn() !== false;
    }

    // Change le mot de passe ; false si le lien n'est plus valable
    public static function utiliser(string $jetonHash, string $empreinte): bool
    {
        $pdo = Database::connexion();
        $pdo->beginTransaction();

        try {
            $requete = $pdo->prepare(
                'UPDATE reinitialisation r SET date_utilisation = CURRENT_TIMESTAMP
                FROM utilisateur u
                WHERE u.utilisateur_id = r.utilisateur_id
                  AND r.jeton_hash = :jeton_hash
                  AND r.date_utilisation IS NULL
                  AND r.date_expiration > CURRENT_TIMESTAMP
                  AND u.actif
                RETURNING r.utilisateur_id'
            );
            $requete->execute(['jeton_hash' => $jetonHash]);
            $utilisateurId = $requete->fetchColumn();

            if ($utilisateurId === false) {
                $pdo->rollBack();
                return false;
            }

            $pdo->prepare('UPDATE utilisateur SET mot_de_passe = :empreinte WHERE utilisateur_id = :id')
                ->execute(['empreinte' => $empreinte, 'id' => $utilisateurId]);

            // Les autres liens envoyés à ce compte ne servent plus
            $pdo->prepare(
                'UPDATE reinitialisation SET date_utilisation = CURRENT_TIMESTAMP
                 WHERE utilisateur_id = :id AND date_utilisation IS NULL'
            )->execute(['id' => $utilisateurId]);

            $pdo->commit();
            return true;
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
