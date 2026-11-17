<?php

declare(strict_types=1);

namespace App\Controller;

use App\Mailer;
use App\Model\Utilisateur;
use App\Model\Reinitialisation;

final class CompteController extends Controller
{
    // Empreinte d'un mot de passe aléatoire jamais utilisé : sert de leurre quand l'email est inconnu
    private const EMPREINTE_LEURRE = '$2y$10$wyhym0YwysRgKVTVcfuObOtvDIGcv.F91U6fSwEbaszuf.tv.4BFq';

    // 10 caractères minimum dont une minuscule, une majuscule, un chiffre et un caractère spécial
    private const REGLE_MOT_DE_PASSE = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).{10,}$/';

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

    public function connexion(): void
    {
        if (utilisateur_connecte() !== null) {
            rediriger('/');
        }

        $email = '';
        $erreur = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim((string) ($_POST['email'] ?? ''));
            // Refus de la requête par PostgreSQL : octets non UTF-8)
            $email = mb_check_encoding($email, 'UTF-8') ? mb_strtolower($email) : '';
            $motDePasse = (string) ($_POST['mot_de_passe'] ?? '');

            if (!csrf_valide()) {
                $erreur = 'Votre session a expirée. Merci de renvoyer le formulaire.';
            } else {
                $compte = Utilisateur::trouverParEmail($email);
                // Aucun compte n'est révélé
                $valide = password_verify($motDePasse, $compte['mot_de_passe'] ?? self::EMPREINTE_LEURRE);

                if ($compte === null || !$valide) {
                    $erreur = 'Adresse email ou mot de passe incorrect.';
                } elseif (!$compte['actif']) {
                    $erreur = 'Ce compte est désactivé. Contactez-nous pour en savoir plus.';
                } else {
                    // Nouvel identifiant de session : empêche la fixation de session
                    session_regenerate_id(true);
                    $_SESSION['utilisateur'] = [
                        'utilisateur_id' => $compte['utilisateur_id'],
                        'prenom' => $compte['prenom'],
                        'role' => $compte['role'],
                    ];
                    $retour = $_SESSION['retour'] ?? '/';
                    unset($_SESSION['retour']);
                    message_flash('Bonjour ' . $compte['prenom'] . ', vous êtes connecté.');
                    rediriger($retour);
                }
            }
        }

        $this->render('compte/connexion', [
            'titre' => 'Connexion',
            'email' => $email,
            'erreur' => $erreur,
        ]);
    }

    // En POST avec jeton CSRF : un simple lien ou une image piégée ne peut pas déconnecter
    public function deconnexion(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_valide()) {
            $_SESSION = [];
            session_regenerate_id(true);
            message_flash('Vous êtes déconnecté. À bientôt !');
        }
        rediriger('/');
    }

    public function motDePasseOublie(): void
    {
        $email = '';
        $erreur = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim((string) ($_POST['email'] ?? ''));
            $email = mb_check_encoding($email, 'UTF-8') ? mb_strtolower($email) : '';

            if (!csrf_valide()) {
                $erreur = 'Votre session a expiré. Merci de renvoyer le formulaire.';
            } else {
                $compte = Utilisateur::trouverParEmail($email);

                if ($compte !== null && $compte['actif']) {
                    // Le jeton part dans le mail ; la base ne garde que son empreinte
                    $jeton = bin2hex(random_bytes(32));
                    Reinitialisation::creer($compte['utilisateur_id'], hash('sha256', $jeton));

                    Mailer::envoyer(
                        $email,
                        'Réinitialisation de votre mot de passe',
                        "Bonjour,\n\n"
                        . "Pour choisir un nouveau mot de passe, ouvrez ce lien (valable 1 heure, une seule fois) :\n"
                        . getenv('APP_URL') . '/reinitialiser?jeton=' . $jeton . "\n\n"
                        . "Si vous n'êtes pas à l'origine de cette demande, ignorez ce message : "
                        . "votre mot de passe reste inchangé.\n\n"
                        . "Julie et José"
                    );
                }

                // Même réponse que le compte existe ou non
                message_flash('Si un compte correspond à cette adresse, un lien de réinitialisation vient de lui être envoyé.');
                rediriger('/connexion');
            }
        }

        $this->render('compte/mot-de-passe-oublie', [
            'titre' => 'Mot de passe oublié',
            'email' => $email,
            'erreur' => $erreur,
        ]);
    }

    public function reinitialiser(): void
    {
        // Le jeton est dans l'URL : il ne doit pas fuiter vers un autre site via l'en-tête Referer
        header('Referrer-Policy: no-referrer');

        $jeton = (string) ($_POST['jeton'] ?? $_GET['jeton'] ?? '');

        // Format attendu : 64 caractères hexadécimaux, sinon inutile d'interroger la base
        if (!preg_match('/^[0-9a-f]{64}$/', $jeton) || !Reinitialisation::estValide(hash('sha256', $jeton))) {
            $this->render('compte/reinitialiser', ['titre' => 'Lien expiré', 'jeton' => null, 'erreurs' => []]);
            return;
        }

        $erreurs = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $motDePasse = (string) ($_POST['mot_de_passe'] ?? '');

            if (!csrf_valide()) {
                $erreurs['formulaire'] = 'Votre session a expiré. Merci de renvoyer le formulaire.';
            } elseif (!preg_match(self::REGLE_MOT_DE_PASSE, $motDePasse)) {
                $erreurs['mot_de_passe'] = 'Le mot de passe ne respecte pas les règles indiquées.';
            } elseif ($motDePasse !== (string) ($_POST['confirmation'] ?? '')) {
                $erreurs['confirmation'] = 'Les deux mots de passe ne sont pas identiques.';
            }

            if ($erreurs === []) {
                $change = Reinitialisation::utiliser(hash('sha256', $jeton), password_hash($motDePasse, PASSWORD_DEFAULT));
                message_flash($change
                    ? 'Votre mot de passe est modifié. Vous pouvez maintenant vous connecter.'
                    : 'Ce lien n\'est plus valable. Merci de refaire une demande.');
                rediriger('/connexion');
            }
        }

        $this->render('compte/reinitialiser', [
            'titre' => 'Nouveau mot de passe',
            'jeton' => $jeton,
            'erreurs' => $erreurs,
        ]);
    }

    // Renvoie [champ => message] ; tableau vide si tout est correct
    private function verifier(array $v, string $motDePasse, string $confirmation): array
    {
        // Octets non UTF-8 : PostgreSQL refuserait l'INSERT (erreur 500)
        if (!mb_check_encoding($v, 'UTF-8')) {
            return ['formulaire' => 'Le formulaire contient des caractères non valides.'];
        }

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
        if (!preg_match(self::REGLE_MOT_DE_PASSE, $motDePasse)) {
            $erreurs['mot_de_passe'] = 'Le mot de passe ne respecte pas les règles indiquées.';
        } 
        elseif ($motDePasse !== $confirmation) {
            $erreurs['confirmation'] = 'Les deux mots de passe ne sont pas identiques.';
        }

        return $erreurs;
    }
}
