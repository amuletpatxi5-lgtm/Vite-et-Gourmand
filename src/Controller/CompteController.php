<?php

declare(strict_types=1);

namespace App\Controller;

use App\Mailer;
use App\Model\Utilisateur;

final class CompteController extends Controller
{
    public function inscription(): void
    {
        $valeurs = [
            'nom' => '', 'prenom' => '', 'email' => '', 'telephone' => '',
            'adresse_postale' => '', 'code_postal' => '', 'ville' => '', 'pays' => 'France',
        ];
        $erreurs = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            foreach (array_keys($valeurs) as $champ) {
                $valeurs[$champ] = trim((string) ($_POST[$champ] ?? ''));
            }
            $valeurs['email'] = mb_strtolower($valeurs['email']);
            $valeurs['telephone'] = preg_replace('/[\s.-]/', '', $valeurs['telephone']);
            $motDePasse = (string) ($_POST['mot_de_passe'] ?? '');

            $erreurs = $this->verifier($valeurs, $motDePasse, (string) ($_POST['confirmation'] ?? ''));

            if ($erreurs === []) {
                Utilisateur::creer($valeurs + ['mot_de_passe' => password_hash($motDePasse, PASSWORD_DEFAULT)]);

                // Un échec d'envoi est écrit dans les logs mais ne bloque pas la création du compte
                Mailer::envoyer(
                    $valeurs['email'],
                    'Bienvenue chez Vite & Gourmand',
                    "Bonjour {$valeurs['prenom']},\n\n"
                    . "Votre compte Vite & Gourmand est créé. Vous pouvez dès maintenant commander "
                    . "nos menus en ligne et suivre vos commandes depuis votre espace client.\n\n"
                    . "À bientôt,\nJulie et José"
                );

                message_flash('Votre compte est créé. Vous pouvez maintenant vous connecter.');
                rediriger('/connexion');
            }
        }

        $this->render('compte/inscription', [
            'titre' => 'Créer un compte',
            'valeurs' => $valeurs,
            'erreurs' => $erreurs,
        ]);
    }

    // Renvoie [champ => message] ; tableau vide si tout est correct
    private function verifier(array $v, string $motDePasse, string $confirmation): array
    {
        $erreurs = [];

        if (!csrf_valide()) {
            $erreurs['formulaire'] = 'Votre session a expiré. Merci de renvoyer le formulaire.';
        }
        if ($v['nom'] === '' || mb_strlen($v['nom']) > 50) {
            $erreurs['nom'] = 'Indiquez votre nom (50 caractères maximum).';
        }
        if ($v['prenom'] === '' || mb_strlen($v['prenom']) > 50) {
            $erreurs['prenom'] = 'Indiquez votre prénom (50 caractères maximum).';
        }
        if (!filter_var($v['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($v['email']) > 255) {
            $erreurs['email'] = 'Indiquez une adresse email valide, par exemple nom@exemple.fr.';
        } elseif (Utilisateur::emailExiste($v['email'])) {
            $erreurs['email'] = 'Un compte existe déjà avec cette adresse email.';
        }
        if (!preg_match('/^(\+33|0)[1-9]\d{8}$/', $v['telephone'])) {
            $erreurs['telephone'] = 'Indiquez un numéro de téléphone français, par exemple 06 12 34 56 78.';
        }
        if ($v['adresse_postale'] === '' || mb_strlen($v['adresse_postale']) > 255) {
            $erreurs['adresse_postale'] = 'Indiquez votre adresse (numéro et rue).';
        }
        if (!preg_match('/^\d{5}$/', $v['code_postal'])) {
            $erreurs['code_postal'] = 'Le code postal comporte 5 chiffres.';
        }
        if ($v['ville'] === '' || mb_strlen($v['ville']) > 100) {
            $erreurs['ville'] = 'Indiquez votre ville.';
        }
        if ($v['pays'] === '' || mb_strlen($v['pays']) > 50) {
            $erreurs['pays'] = 'Indiquez votre pays.';
        }
        // 10 caractères minimum dont une minuscule, une majuscule, un chiffre et un caractère spécial
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).{10,}$/', $motDePasse)) {
            $erreurs['mot_de_passe'] = 'Le mot de passe ne respecte pas les règles indiquées.';
        } elseif ($motDePasse !== $confirmation) {
            $erreurs['confirmation'] = 'Les deux mots de passe ne sont pas identiques.';
        }

        return $erreurs;
    }
}
