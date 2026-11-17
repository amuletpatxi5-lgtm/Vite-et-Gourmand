<?php

declare(strict_types=1);

namespace App\Controller;

use App\Distance;
use App\Mailer;
use App\Model\Commande;
use App\Model\Menu;
use App\Model\Utilisateur;
use App\Stats;
use App\Tarif;
use DateTimeImmutable;
use Throwable;

final class CommandeController extends Controller
{
    public function commander(): void
    {
        exiger_connexion();
        $compte = Utilisateur::trouver(utilisateur_connecte()['utilisateur_id']);

        // Au départ : le menu choisi sur la page détail et l'adresse du compte
        $valeurs = [
            'menu_id' => (string) ($_GET['menu'] ?? ''),
            'nombre_personne' => '',
            'date_prestation' => '',
            'heure_livraison' => '',
            'adresse' => $compte['adresse_postale'] ?? '',
            'code_postal' => $compte['code_postal'] ?? '',
            'ville' => $compte['ville'] ?? '',
        ];
        $erreurs = [];
        $recap = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            foreach (array_keys($valeurs) as $champ) {
                $valeurs[$champ] = trim((string) ($_POST[$champ] ?? ''));
            }
            [$erreurs, $recap] = $this->verifier($valeurs);

            if ($erreurs === [] && ($_POST['action'] ?? '') === 'valider') {
                // Le client valide le prix qu'il a vu : si la saisie a changé depuis, on réaffiche le récapitulatif
                if ((string) $recap['prix']['total'] === (string) ($_POST['total_vu'] ?? '')) {
                    $this->enregistrer($valeurs, $recap, $compte);
                }
                $erreurs['formulaire'] = 'Le prix a changé depuis le récapitulatif : vérifiez-le avant de valider.';
            }
        }

        $this->render('commande/commander', [
            'titre' => 'Commander un menu',
            'menus' => Menu::filtrer(array_fill_keys(['prix_min', 'prix_max', 'theme', 'regime', 'personnes'], null)),
            'compte' => $compte,
            'valeurs' => $valeurs,
            'erreurs' => $erreurs,
            'recap' => $recap,
        ]);
    }

    // Contrôle la saisie puis calcule la distance et le prix. Renvoie [erreurs, récapitulatif ou null]
    private function verifier(array $v): array
    {
        if (!csrf_valide()) {
            return [['formulaire' => 'Votre session a expiré. Merci de renvoyer le formulaire.'], null];
        }
        if (!mb_check_encoding($v, 'UTF-8')) {
            return [['formulaire' => 'Le formulaire contient des caractères non valides.'], null];
        }

        $erreurs = [];

        $menu = ctype_digit($v['menu_id']) ? Menu::trouver((int) $v['menu_id']) : null;
        if ($menu === null) {
            $erreurs['menu_id'] = 'Choisissez un menu.';
        } elseif (!ctype_digit($v['nombre_personne'])
            || (int) $v['nombre_personne'] < $menu['nombre_de_personne_minimum']
            || (int) $v['nombre_personne'] > 500) {
            $erreurs['nombre_personne'] = "Ce menu se commande pour {$menu['nombre_de_personne_minimum']} à 500 personnes.";
        }

        // Le format exact évite qu'un « 31 février » devienne le 3 mars
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $v['date_prestation']);
        if ($date === false || $date->format('Y-m-d') !== $v['date_prestation'] || $date < new DateTimeImmutable('tomorrow')) {
            $erreurs['date_prestation'] = 'Choisissez une date à partir de demain.';
        }
        if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $v['heure_livraison'])) {
            $erreurs['heure_livraison'] = 'Indiquez une heure de livraison, par exemple 12:30.';
        }
        if ($v['adresse'] === '' || mb_strlen($v['adresse']) > 255) {
            $erreurs['adresse'] = 'Indiquez l\'adresse de la prestation (numéro et rue).';
        }
        if (!preg_match('/^\d{5}$/', $v['code_postal'])) {
            $erreurs['code_postal'] = 'Le code postal comporte 5 chiffres.';
        }
        if ($v['ville'] === '' || mb_strlen($v['ville']) > 100) {
            $erreurs['ville'] = 'Indiquez la ville de la prestation.';
        }

        if ($erreurs !== []) {
            return [$erreurs, null];
        }

        // Appels aux API externes seulement quand le reste est correct
        $distance = Distance::calculer($v['adresse'], $v['code_postal'], $v['ville']);
        if ($distance === null) {
            return [['adresse' => 'Adresse introuvable : vérifiez le numéro, la rue et le code postal.'], null];
        }

        // Le prix est toujours calculé ici, jamais lu dans le formulaire
        $prix = Tarif::calculer(
            (int) round((float) $menu['prix_par_personne'] * 100),
            (int) $menu['nombre_de_personne_minimum'],
            (int) $v['nombre_personne'],
            $distance['dans_bordeaux'],
            $distance['distance_km']
        );

        return [[], ['prix' => $prix, 'menu' => $menu, 'distance' => $distance]];
    }

    private function enregistrer(array $v, array $recap, array $compte): never
    {
        $prix = $recap['prix'];
        $menu = $recap['menu'];

        $numero = Commande::creer([
            'menu_id' => $menu['menu_id'],
            'utilisateur_id' => utilisateur_connecte()['utilisateur_id'],
            'date_prestation' => $v['date_prestation'],
            'heure_livraison' => $v['heure_livraison'],
            'nombre_personne' => (int) $v['nombre_personne'],
            'prix_menu' => $prix['menu'],
            'remise' => $prix['remise'],
            'prix_livraison' => $prix['livraison'],
            'prix_total' => $prix['total'],
            'distance_km' => $recap['distance']['distance_km'],
            'adresse' => $v['adresse'],
            'code_postal' => $v['code_postal'],
            'ville' => $v['ville'],
        ]);

        if ($numero === null) {
            message_flash('Désolé, ce menu n\'est plus disponible. Choisissez-en un autre.');
            rediriger('/menus');
        }

        // Statistiques (MongoDB) : une panne ne doit pas annuler une commande déjà enregistrée
        try {
            Stats::enregistrerCommande($numero, (int) $menu['menu_id'], $menu['titre'], $prix['total'], new DateTimeImmutable());
        } catch (Throwable $e) {
            error_log('Stats : ' . $e->getMessage());
        }

        $date = DateTimeImmutable::createFromFormat('Y-m-d', $v['date_prestation'])->format('d/m/Y');
        Mailer::envoyer(
            $compte['email'],
            "Confirmation de votre commande $numero",
            "Bonjour {$compte['prenom']},\n\n"
            . "Nous avons bien reçu votre commande $numero :\n"
            . "- {$menu['titre']} pour {$v['nombre_personne']} personnes\n"
            . "- le $date à {$v['heure_livraison']}, {$v['adresse']}, {$v['code_postal']} {$v['ville']}\n\n"
            . 'Menu : ' . prix($prix['menu'] / 100) . "\n"
            . ($prix['remise'] > 0 ? 'Remise : -' . prix($prix['remise'] / 100) . "\n" : '')
            . 'Livraison : ' . prix($prix['livraison'] / 100) . "\n"
            . 'Total : ' . prix($prix['total'] / 100) . "\n\n"
            . "Nous vous recontacterons pour confirmer la prestation.\n\nJulie et José"
        );

        message_flash("Merci ! Votre commande $numero est enregistrée. Un mail de confirmation vous a été envoyé.");
        rediriger('/');
    }
}
