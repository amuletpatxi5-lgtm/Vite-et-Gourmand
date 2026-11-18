<?php

declare(strict_types=1);

namespace App\Model;

use App\Database;
use Throwable;

final class Commande
{
    // Enregistre la commande, réserve une unité du stock et ouvre le suivi, en une seule transaction.
    // $c : menu_id, utilisateur_id, date_prestation, heure_livraison, nombre_personne,
    //      prix_menu, remise, prix_livraison, prix_total (en centimes), distance_km,
    //      adresse, code_postal, ville
    // Renvoie le numéro de commande, ou null si le menu n'est plus disponible.
    public static function creer(array $c): ?string
    {
        $pdo = Database::connexion();
        $pdo->beginTransaction();

        try {
            // Réserve une unité du stock ; refusé si le menu est épuisé ou retiré entre-temps
            $requete = $pdo->prepare(
                'UPDATE menu SET quantite_restante = quantite_restante - 1
                 WHERE menu_id = :menu_id AND actif AND quantite_restante > 0'
            );
            $requete->execute(['menu_id' => $c['menu_id']]);

            if ($requete->rowCount() === 0) {
                $pdo->rollBack();
                return null;
            }

            $numero = $pdo->query(
                "SELECT 'VG-' || to_char(CURRENT_DATE, 'YYYY') || '-' || lpad(nextval('commande_numero_seq')::text, 4, '0')"
            )->fetchColumn();

            // Les montants arrivent en centimes et sont stockés en euros (DECIMAL)
            $pdo->prepare(
                'INSERT INTO commande (numero_commande, date_prestation, heure_livraison, nombre_personne,
                    prix_menu, remise, prix_livraison, prix_total, distance_km,
                    adresse_prestation, code_postal_prestation, ville_prestation, menu_id, utilisateur_id)
                 VALUES (:numero, :date_prestation, :heure_livraison, :nombre_personne,
                    :prix_menu / 100.0, :remise / 100.0, :prix_livraison / 100.0, :prix_total / 100.0, :distance_km,
                    :adresse, :code_postal, :ville, :menu_id, :utilisateur_id)'
            )->execute(['numero' => $numero] + $c);

            // Première étape du suivi, datée (énoncé : « tous les états suivis de la date et l'heure »)
            $pdo->prepare("INSERT INTO historique_statut (statut, numero_commande) VALUES ('en_attente', :numero)")
                ->execute(['numero' => $numero]);

            $pdo->commit();
            return $numero;
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
        
    // Commandes d'un client, les plus récents en premier
    public static function duClient(int$utilisateurId): array
    {
        $requete = Database::connexion()->prepare(
            'SELECT c.numero_commande, c.date_commande, c.date_prestation, c.heure_livraison, c.nombre_personne, c.prix_menu, c.remise, c.prix_livraison, c.prix_total, c.statut, c.adresse_prestation, c.code_postal_prestation, c.ville_prestation, m.titre AS menu_titre, a.note AS avis_note, a.statut AS avis_statut
            FROM commande c
            JOIN menu m ON m.menu_id = c.menu_id
            LEFT JOIN avis a ON a.numero_commande = c.numero_commande
            WHERE c.utilisateur_id = :id
            ORDER BY c.date_commande DESC'
        );
        $requete->execute(['id'=> $utilisateurId]);

        return $requete->fetchAll();
    } 

    // Suivi de toutes les commandes d'un client
    public static function suivis(int $utilisatuerId): array
    {
        $requete= Database::connexion()->prepare(
            'SELECT h.numero_commande, h.statut, h.date_changement
            FROM historique_statut h
            JOIN commande c ON c.numero_commande = h.numero_commande
            WHERE c.utilisateur_id = :id
            ORDER BY h.date_changement, h.historique_id'
        );
        $requete->execute(['id'=> $utilisatuerId]);

        $suivis =[];
        foreach ($requete->fetchAll() as $etape) {
            $suivis[$etape['numero_commande']][] = $etape;
        }

        return $suivis;
    }

    // Annulation par le client : sa commande tant que le statut est en attente.
    // Mise à jour du stock et annulation entre dans le suivi. 
    public static function annuler(string $numero, int $utilisateurId): bool
    {
    $pdo = Database::connexion();
    $pdo->beginTransaction();

    try {
        $requete =$pdo->prepare(
            "UPDATE commande SET statut = 'annulee',motif_annulation = 'Annulée par le client'
            WHERE numero_commande = :numero AND utilisateur_id = :id AND statut = 'en_attente'
            RETURNING menu_id"
        );
        $requete->execute(['numero' => $numero, 'id' => $utilisateurId]);
        $menuId = $requete->fetchColumn();
        
        if ($menuId === false) {
            $pdo->rollBack();
            return false;
        }

        $pdo->prepare('UPDATE menu SET quantite_restante = quantite_restante + 1 WHERE menu_id = :id')
            ->execute(['id' => $menuId]);
        $pdo->prepare("INSERT INTO historique_statut (statut, numero_commande) VALUES ('annulee', :numero)")
            ->execute(['numero' => $numero]);
            
        $pdo->commit();
        return true;
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
    }

    // Étapes possibles après un statut ([] : terminée ou annulée, plus rien à faire).
    // Après la livraison, l'employé indique s'il a prêté du matériel.
    public static function statutsSuivants(string $statut): array
    {
        return match ($statut) {
            'en_attente' => ['accepte'],
            'accepte' => ['en_preparation'],
            'en_preparation' => ['en_livraison'],
            'en_livraison' => ['livre'],
            'livre' => ['terminee', 'attente_retour_materiel'],
            'attente_retour_materiel' => ['terminee'],
            default => [],
        };
    }

    // Toutes les commandes (espace employé). Filtres facultatifs : statut, client (nom, prénom ou email)
    public static function toutes(?string $statut, ?string $client): array
    {
        $sql = 'SELECT c.numero_commande, c.date_prestation, c.heure_livraison, c.nombre_personne, c.prix_total,
                       c.statut, c.pret_materiel, c.ville_prestation, c.motif_annulation,
                       m.titre AS menu_titre, u.nom, u.prenom, u.email, u.telephone
                FROM commande c
                JOIN menu m ON m.menu_id = c.menu_id
                JOIN utilisateur u ON u.utilisateur_id = c.utilisateur_id
                WHERE TRUE';
        $parametres = [];

        if ($statut !== null) {
            $sql .= ' AND c.statut = :statut';
            $parametres['statut'] = $statut;
        }
        if ($client !== null) {
            // % et _ tapés par l'employé sont cherchés tels quels (pas comme jokers)
            $sql .= ' AND (u.nom ILIKE :client OR u.prenom ILIKE :client OR u.email ILIKE :client)';
            $parametres['client'] = '%' . addcslashes($client, '%_\\') . '%';
        }

        $sql .= ' ORDER BY c.date_prestation, c.heure_livraison';

        $requete = Database::connexion()->prepare($sql);
        $requete->execute($parametres);

        return $requete->fetchAll();
    }

    // Passe la commande du statut que l'employé a vu au statut choisi, si cette étape est autorisée.
    // Renvoie l'email et le prénom du client (pour le prévenir), ou null si refusé
    public static function avancer(string $numero, string $statutVu, string $cible): ?array
    {
        if (!in_array($cible, self::statutsSuivants($statutVu), true)) {
            return null;
        }

        $pdo = Database::connexion();
        $pdo->beginTransaction();

        try {
            // « AND c.statut = :vu » : si la commande a changé entre-temps, aucune ligne n'est modifiée
            $requete = $pdo->prepare(
                "UPDATE commande c
                 SET statut = :cible,
                     pret_materiel = (c.pret_materiel OR :cible::varchar = 'attente_retour_materiel')
                 FROM utilisateur u
                 WHERE u.utilisateur_id = c.utilisateur_id AND c.numero_commande = :numero AND c.statut = :vu
                 RETURNING u.email, u.prenom"
            );
            $requete->execute(['cible' => $cible, 'numero' => $numero, 'vu' => $statutVu]);
            $client = $requete->fetch();

            if ($client === false) {
                $pdo->rollBack();
                return null;
            }

            $pdo->prepare('INSERT INTO historique_statut (statut, numero_commande) VALUES (:statut, :numero)')
                ->execute(['statut' => $cible, 'numero' => $numero]);

            $pdo->commit();
            return $client;
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }


    // Annulation par l'employé après avoir contacté le client (énoncé : motif et mode de contact obligatoires).
    // Possible tant que la commande n'est pas partie en livraison ; le stock est rendu.
    public static function annulerParEmploye(string $numero, string $motif, string $modeContact): bool
    {
        $pdo = Database::connexion();
        $pdo->beginTransaction();

        try {
            $requete = $pdo->prepare(
                "UPDATE commande SET statut = 'annulee', motif_annulation = :motif, mode_contact = :mode
                 WHERE numero_commande = :numero AND statut IN ('en_attente', 'accepte', 'en_preparation')
                 RETURNING menu_id"
            );
            $requete->execute(['motif' => $motif, 'mode' => $modeContact, 'numero' => $numero]);
            $menuId = $requete->fetchColumn();

            if ($menuId === false) {
                $pdo->rollBack();
                return false;
            }

            $pdo->prepare('UPDATE menu SET quantite_restante = quantite_restante + 1 WHERE menu_id = :id')
                ->execute(['id' => $menuId]);
            $pdo->prepare("INSERT INTO historique_statut (statut, numero_commande) VALUES ('annulee', :numero)")
                ->execute(['numero' => $numero]);

            $pdo->commit();
            return true;
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
