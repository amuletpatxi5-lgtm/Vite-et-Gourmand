<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\Horaire;

use App\Model\Utilisateur;

class Controller
{
    protected function render(string $vue, array $donnees = []): void
    {
        $horaires = Horaire::tous();
        extract($donnees, EXTR_SKIP);

        ob_start();
        require __DIR__ . '/../../templates/' . $vue . '.php';
        $contenu = ob_get_clean();

        require __DIR__ . '/../../templates/layout.php';
    }

    public function introuvable(): void
    {
        http_response_code(404);
        $this->render('404', ['titre' => 'Page introuvable']);
    }

    protected function partiel(string $vue, array $donnees =[]): void
    {
        extract($donnees, EXTR_SKIP);
        require __DIR__ . '/../../templates/' .$vue . '.php';
    }

    // Page réservée à certains rôles. Les autres comptes voient « page introuvable » : on ne révèle pas que la page existe.
    protected function exigerRole(string ...$roles): void
    {
        exiger_connexion();
        $compte = utilisateur_connecte();

        // Compte désactivé par l'administrateur depuis la connexion : la session est coupée tout de suite
        if (!Utilisateur::estActif($compte['utilisateur_id'])) {
            $_SESSION = [];
            session_regenerate_id(true);
            message_flash("Votre compte a été désactivé. Contactez l'administrateur.");
            rediriger('/connexion');
        }

        if (!in_array($compte['role'], $roles, true)) {
            $this->introuvable();
            exit;
        }
    }

}
