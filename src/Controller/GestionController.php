<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\Commande;
use App\Stats;
use Throwable;
use App\Mailer;
use App\Model\Avis;
use App\Model\Horaire;
use App\Model\Menu;

// US-10 : espace employé (et administrateur) pour suivre et faire avancer les commandes
final class GestionController extends Controller
{
    private const ROLES = ['employe', 'administrateur'];
    private const STATUTS = ['en_attente', 'accepte', 'en_preparation', 'en_livraison', 'livre',
        'attente_retour_materiel', 'terminee', 'annulee'];
    private const ANNULABLES = ['en_attente', 'accepte', 'en_preparation'];

    private const JOURS = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];

    public function commandes(): void
    {
        $this->exigerRole(...self::ROLES);

        $statut = $_GET['statut'] ?? '';
        $statut = in_array($statut, self::STATUTS, true) ? $statut : null;
        $client = $_GET['client'] ?? '';
        $client = is_string($client) && mb_check_encoding($client, 'UTF-8') ? trim($client) : '';

        // Pour chaque commande : l'étape suivante possible et si l'employé peut encore l'annuler
        $commandes = array_map(
            fn(array $c) => $c + [
                'suivants' => Commande::statutsSuivants($c['statut']),
                'annulable' => in_array($c['statut'], self::ANNULABLES, true),
            ],
            Commande::toutes($statut, $client === '' ? null : $client)
        );

        $this->render('gestion/commandes', [
            'titre' => 'Gestion des commandes',
            'commandes' => $commandes,
            'statuts' => self::STATUTS,
            'statut' => $statut,
            'client' => $client,
            'filtres' => $this->filtres(),
            'scripts' => ['/js/confirmation.js'],
        ]);
    }

    // Fait avancer une commande d'une étape et prévient le client quand l'énoncé l'exige
    public function avancer(): void
    {
        $this->exigerRole(...self::ROLES);
        $numero = $this->numeroPoste();
        $cible = $this->champ('statut_cible');

        $client = Commande::avancer($numero, $this->champ('statut_vu'), $cible);
        if ($client === null) {
            message_flash("La commande $numero a été modifiée entre-temps : vérifiez son statut.");
        } else {
            $this->prevenirClient($numero, $cible, $client);
            message_flash("Commande $numero : " . libelle_statut($cible) . '.');
        }

        rediriger('/gestion/commandes' . $this->filtres());
    }

    // Mails exigés par l'énoncé : commande terminée (invitation à donner son avis)
    // et attente du retour de matériel (frais de 600 € après 10 jours ouvrés)
    private function prevenirClient(string $numero, string $statut, array $client): void
    {
        $bonjour = 'Bonjour' . ($client['prenom'] ? ' ' . $client['prenom'] : '') . ",\n\n";
        $texte = match ($statut) {
            'terminee' => $bonjour
                . "Votre commande $numero est terminée. Merci pour votre confiance !\n\n"
                . "Votre avis nous aide beaucoup : vous pouvez noter la prestation depuis votre espace client :\n"
                . getenv('APP_URL') . "/mes-commandes\n\nJulie et José",
            'attente_retour_materiel' => $bonjour
                . "Votre commande $numero a été livrée avec du matériel prêté (plateaux, chafing dishes, nappage...).\n\n"
                . "Merci de nous le restituer sous 10 jours ouvrés. Passé ce délai, des frais de 600 € vous seront "
                . "facturés, conformément à nos conditions générales de vente.\n\n"
                . "Pour organiser le retour, contactez-nous : " . getenv('APP_URL') . "/contact\n\nJulie et José",
            default => null,
        };

        if ($texte !== null) {
            Mailer::envoyer($client['email'], "Votre commande $numero : " . libelle_statut($statut), $texte);
        }
    }


    // Annulation après avoir contacté le client : mode de contact et motif obligatoires
    public function annuler(): void
    {
        $this->exigerRole(...self::ROLES);
        $numero = $this->numeroPoste();
        $motif = $this->champ('motif');
        $mode = $this->champ('mode_contact');

        if ($motif === '' || mb_strlen($motif) > 500 || !in_array($mode, ['telephone', 'email'], true)) {
            message_flash("Commande $numero non annulée : indiquez le mode de contact et le motif (500 caractères maximum).");
        } elseif (Commande::annulerParEmploye($numero, $motif, $mode)) {
            try {
                Stats::retirerCommande($numero);
            } catch (Throwable $e) {
                error_log('Stats : ' . $e->getMessage());
            }
            message_flash("Commande $numero annulée.");
        } else {
            message_flash("La commande $numero ne peut plus être annulée.");
        }

        rediriger('/gestion/commandes' . $this->filtres());
    }

        // US-11 : avis en attente de modération
    public function avis(): void
    {
        $this->exigerRole(...self::ROLES);
        $this->render('gestion/avis', ['titre' => 'Avis à valider', 'avis' => Avis::enAttente()]);
    }

    // US-11 : publier ou refuser un avis
    public function modererAvis(): void
    {
        $this->exigerRole(...self::ROLES);
        $id = filter_input(INPUT_POST, 'avis_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $decision = $this->champ('decision');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valide() || !$id || !in_array($decision, ['valide', 'refuse'], true)) {
            message_flash('Action impossible : rechargez la page et réessayez.');
        } elseif (Avis::moderer($id, $decision)) {
            message_flash($decision === 'valide' ? "Avis publié sur la page d'accueil." : 'Avis refusé : il ne sera pas publié.');
        } else {
            message_flash('Cet avis a déjà été traité.');
        }
        
        rediriger('/gestion/avis');
    }

    // US-09 : menus (prix, stock, mise en vente) et horaires sur une seule page
    public function menus(): void
    {
        $this->exigerRole(...self::ROLES);
        $this->render('gestion/menus', [
            'titre' => 'Menus et horaires',
            'menus' => Menu::tousPourGestion(),
            'horaires' => Horaire::tous(),
        ]);
    }

    public function modifierMenu(): void
    {
        $this->exigerRole(...self::ROLES);
        $id = filter_input(INPUT_POST, 'menu_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $stock = filter_input(INPUT_POST, 'quantite_restante', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 9999]]);
        $prix = str_replace(',', '.', $this->champ('prix_par_personne'));
        $actif = ($_POST['actif'] ?? '') === '1';

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valide() || !$id || !is_int($stock)
            || !preg_match('/^\d{1,4}(\.\d{1,2})?$/', $prix) || (float) $prix <= 0) {
            message_flash('Menu non modifié : prix entre 0,01 et 9999,99 €, stock entier positif ou nul.');
        } elseif (Menu::modifierVente($id, $prix, $stock, $actif)) {
            message_flash('Menu mis à jour.');
        } else {
            message_flash('Menu introuvable.');
        }

        rediriger('/gestion/menus');
    }

    public function modifierHoraires(): void
    {
        $this->exigerRole(...self::ROLES);
        $heure = '/^([01]\d|2[0-3]):[0-5]\d$/';
        $horaires = [];

        foreach (self::JOURS as $jour) {
            $ouverture = $this->champ("ouverture_$jour");
            $fermeture = $this->champ("fermeture_$jour");

            if ($ouverture === '' && $fermeture === '') {
                $horaires[$jour] = [null, null];   // fermé ce jour-là
            } elseif (preg_match($heure, $ouverture) && preg_match($heure, $fermeture) && $ouverture < $fermeture) {
                $horaires[$jour] = [$ouverture, $fermeture];
            } else {
                message_flash("Horaires non enregistrés : le $jour, indiquez l'ouverture et la fermeture (dans cet ordre), ou laissez les deux vides si vous êtes fermés.");
                rediriger('/gestion/menus#horaires');
            }
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valide()) {
            message_flash('Action impossible : rechargez la page et réessayez.');
        } else {
            Horaire::modifier($horaires);
            message_flash('Horaires enregistrés : ils sont affichés en bas de chaque page.');
        }

        rediriger('/gestion/menus#horaires');
    }



    // Numéro de commande d'un formulaire POST protégé ; sinon retour à la liste
    private function numeroPoste(): string
    {
        $numero = $this->champ('numero');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valide() || !preg_match('/^VG-\d{4}-\d{4,}$/', $numero)) {
            message_flash('Action impossible : rechargez la page et réessayez.');
            rediriger('/gestion/commandes' . $this->filtres());
        }
        return $numero;
    }

    // Champ texte d'un formulaire POST ('' si absent, tableau ou UTF-8 invalide)
    private function champ(string $nom): string
    {
        $valeur = $_POST[$nom] ?? '';
        return is_string($valeur) && mb_check_encoding($valeur, 'UTF-8') ? trim($valeur) : '';
    }

    // Filtres actuels sous forme de « ?statut=...&client=... » pour revenir sur la même liste
    private function filtres(): string
    {
        $filtres = array_filter([
            'statut' => is_string($_GET['statut'] ?? null) ? $_GET['statut'] : '',
            'client' => is_string($_GET['client'] ?? null) ? $_GET['client'] : '',
        ]);
        return $filtres === [] ? '' : '?' . http_build_query($filtres);
    }
}
