<?php

declare(strict_types=1);

namespace App\Model;

use App\Database;

final class Utilisateur
{
    public static function emailExiste(string $email): bool
    {
        $requete = Database::connexion()->prepare('SELECT 1 FROM utilisateur WHERE email = :email');
        $requete->execute(['email' => $email]);

        return $requete->fetchColumn() !==false;
    }

    // Le rôle du compte client est fixé ici, jamais lu dans le formulaire
    public static function creer(array $donnees): void
    {
        $requete = Database::connexion()->prepare(
            "INSERT INTO utilisateur (email, mot_de_passe, nom, prenom, telephone, adresse_postale, code_postal, ville, pays, role_id)
            VALUES (:email, :mot_de_passe, :nom, :prenom, :telephone, :adresse_postale, :code_postal, :ville, :pays,
                    (SELECT role_id FROM role WHERE libelle = 'utilisateur'))"
        );
        $requete->execute($donnees);
    }
}
