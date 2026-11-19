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

    public static function trouverParEmail(string $email): ?array
    {
        $requete = Database::connexion()->prepare(
           'SELECT u.utilisateur_id, u.prenom, u.mot_de_passe, u.actif, r.libelle AS role
            FROM utilisateur u
            JOIN role r ON r.role_id = u.role_id
            WHERE u.email = :email'
        );
        $requete->execute(['email' => $email]);

        return $requete->fetch() ?: null;
    }
    
    // Coordonnées du compte, pour préremplir les formulaires
    public static function trouver(int $id): ?array
    {
        $requete = Database::connexion()->prepare(
            'SELECT nom, prenom, email, telephone, adresse_postale, code_postal, ville
             FROM utilisateur
             WHERE utilisateur_id = :id'
        );
        $requete->execute(['id' => $id]);

        return $requete->fetch() ?: null;
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

        // US-12 : compte employé créé par l'administrateur (le rôle est fixé ici, jamais lu dans le formulaire)
    public static function creerEmploye(string $email, string $empreinte): void
    {
        Database::connexion()->prepare(
            "INSERT INTO utilisateur (email, mot_de_passe, role_id)
             VALUES (:email, :mot_de_passe, (SELECT role_id FROM role WHERE libelle = 'employe'))"
        )->execute(['email' => $email, 'mot_de_passe' => $empreinte]);
    }

    // US-12 : comptes employés, les plus récents d'abord
    public static function employes(): array
    {
        return Database::connexion()->query(
            "SELECT u.utilisateur_id, u.email, u.actif
             FROM utilisateur u
             JOIN role r ON r.role_id = u.role_id
             WHERE r.libelle = 'employe'
             ORDER BY u.utilisateur_id DESC"
        )->fetchAll();
    }

    // US-12 : active ou désactive un compte EMPLOYÉ uniquement (jamais un client ni l'administrateur)
    public static function changerActifEmploye(int $id, bool $actif): bool
    {
        $requete = Database::connexion()->prepare(
            "UPDATE utilisateur SET actif = :actif
             WHERE utilisateur_id = :id AND role_id = (SELECT role_id FROM role WHERE libelle = 'employe')"
        );
        // 'true' / 'false' en texte : PDO enverrait false sous forme de chaîne vide, refusée par PostgreSQL
        $requete->execute(['actif' => $actif ? 'true' : 'false', 'id' => $id]);

        return $requete->rowCount() === 1;
    }

    // Le compte est-il toujours actif ? (vérifié sur les pages réservées : un compte désactivé perd l'accès tout de suite)
    public static function estActif(int $id): bool
    {
        $requete = Database::connexion()->prepare('SELECT actif FROM utilisateur WHERE utilisateur_id = :id');
        $requete->execute(['id' => $id]);

        return $requete->fetchColumn() === true;
    }
}