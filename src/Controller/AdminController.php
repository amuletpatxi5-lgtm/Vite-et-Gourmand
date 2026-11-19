<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\Menu;
use App\Stats;
use DateTimeImmutable;
use Throwable;
use App\Mailer;
use App\Model\Utilisateur;

// US-13 : statistiques des commandes (MongoDB), réservées à l'administrateur
final class AdminController extends Controller
{
    public function statistiques(): void
    {
        $this->exigerRole('administrateur');

        $menuId = filter_input(INPUT_GET, 'menu', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;
        $du = $this->date('du');
        $au = $this->date('au');
        $erreur = null;
        $lignes = [];

        if ($du !== null && $au !== null && $du > $au) {
            $erreur = 'La date de début doit être avant la date de fin.';
        } else {
            // Une panne de MongoDB ne doit pas faire planter la page
            try {
                $lignes = Stats::parMenu($menuId, $du, $au);
            } catch (Throwable $e) {
                error_log('Stats : ' . $e->getMessage());
                $erreur = 'Les statistiques sont momentanément indisponibles.';
            }
        }

        $this->render('admin/statistiques', [
            'titre' => 'Statistiques',
            'menus' => Menu::filtrer(array_fill_keys(['prix_min', 'prix_max', 'theme', 'regime', 'personnes'], null)),
            'lignes' => $lignes,
            'erreur' => $erreur,
            'filtres' => ['menu' => $menuId, 'du' => $du?->format('Y-m-d') ?? '', 'au' => $au?->format('Y-m-d') ?? ''],
            // Valeurs les plus hautes : la barre la plus longue fait 100 %
            'maxCommandes' => max(array_column($lignes, 'nombre_commandes') ?: [1]),
            'maxCa' => max(array_column($lignes, 'chiffre_affaires_centimes') ?: [1]),
        ]);
    }

    // Date AAAA-MM-JJ du formulaire ; null si absente ou impossible (31 février...)
    private function date(string $nom): ?DateTimeImmutable
    {
        $valeur = $_GET[$nom] ?? '';
        if (!is_string($valeur)) {
            return null;
        }
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $valeur);

        return $date !== false && $date->format('Y-m-d') === $valeur ? $date : null;
    }

        // US-12 : création des comptes employés et liste avec leur état
    public function employes(): void
    {
        $this->exigerRole('administrateur');
        $email = '';
        $erreurs = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $email = is_string($email) && mb_check_encoding($email, 'UTF-8') ? mb_strtolower(trim($email)) : '';
            $motDePasse = $_POST['mot_de_passe'] ?? '';
            $motDePasse = is_string($motDePasse) ? $motDePasse : '';

            if (!csrf_valide()) {
                $erreurs['formulaire'] = 'Votre session a expiré. Merci de renvoyer le formulaire.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $erreurs['email'] = "L'adresse email n'est pas valide.";
            } elseif (Utilisateur::emailExiste($email)) {
                $erreurs['email'] = 'Un compte existe déjà avec cette adresse.';
            }
            if (!preg_match(CompteController::REGLE_MOT_DE_PASSE, $motDePasse)) {
                $erreurs['mot_de_passe'] = 'Le mot de passe doit contenir au moins 10 caractères, dont une majuscule, '
                    . 'une minuscule, un chiffre et un caractère spécial.';
            }

            if ($erreurs === []) {
                Utilisateur::creerEmploye($email, password_hash($motDePasse, PASSWORD_DEFAULT));

                // Énoncé : l'employé est prévenu par mail, SANS le mot de passe
                Mailer::envoyer(
                    $email,
                    'Votre compte employé Vite & Gourmand',
                    "Bonjour,\n\nUn compte employé a été créé pour vous sur le site Vite & Gourmand.\n\n"
                    . "Identifiant : $email\n"
                    . "Pour connaître votre mot de passe, rapprochez-vous de l'administrateur.\n\n"
                    . 'Connexion : ' . getenv('APP_URL') . "/connexion\n\nJulie et José"
                );

                message_flash("Compte employé créé pour $email. Un mail l'en a informé (sans le mot de passe).");
                rediriger('/admin/employes');
            }
        }

        $this->render('admin/employes', [
            'titre' => 'Comptes employés',
            'employes' => Utilisateur::employes(),
            'email' => $email,
            'erreurs' => $erreurs,
            'scripts' => ['/js/confirmation.js'],
        ]);
    }

    // US-12 : désactiver ou réactiver un compte employé
    public function activerEmploye(): void
    {
        $this->exigerRole('administrateur');
        $id = filter_input(INPUT_POST, 'utilisateur_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $actif = ($_POST['actif'] ?? '') === '1';

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valide() || !$id) {
            message_flash('Action impossible : rechargez la page et réessayez.');
        } elseif (Utilisateur::changerActifEmploye($id, $actif)) {
            message_flash($actif ? 'Compte réactivé.' : "Compte désactivé : l'employé ne peut plus se connecter.");
        } else {
            message_flash("Ce compte n'est pas un compte employé.");
        }

        rediriger('/admin/employes');
    }
}
