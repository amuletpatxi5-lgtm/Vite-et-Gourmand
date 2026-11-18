<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\Commande;
use App\Stats;
use Throwable;
use App\Mailer;

// US-10 : espace employé (et administrateur) pour suivre et faire avancer les commandes
final class GestionController extends Controller
{
    private const ROLES = ['employe', 'administrateur'];
    private const STATUTS = ['en_attente', 'accepte', 'en_preparation', 'en_livraison', 'livre',
        'attente_retour_materiel', 'terminee', 'annulee'];
    private const ANNULABLES = ['en_attente', 'accepte', 'en_preparation'];

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
