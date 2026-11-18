<?php
// Statistiques des commandes (base NoSQL MongoDB)
// Chaque commande validée est copiée sous forme de document simple

declare(strict_types=1);

namespace App;

use MongoDB\BSON\UTCDateTime;
use MongoDB\Client;
use MongoDB\Collection;

final class Stats
{
    private static ?Collection $collection = null;

    // Une seule connexion par requête PHP
    private static function collection(): Collection
    {
        if (self::$collection === null) {
            // 3 secondes maximum pour trouver le serveur (30 par défaut)
            $client = new Client((string) getenv('MONGO_URI'), ['serverSelectionTimeoutMS' => 3000]);
            self::$collection = $client
                ->selectDatabase((string) getenv('MONGO_DB'))
                ->selectCollection('commandes');
        }
        return self::$collection;
    }

    // Copie une commande dans les statistiques (en centimes)
    public static function enregistrerCommande(
        string $numeroCommande,
        int $menuId,
        string $menuTitre,
        int $montantCentimes,
        \DateTimeInterface $date
    ): void {
        self::collection()->insertOne([
            'numero_commande' => $numeroCommande,
            'menu_id' => $menuId,
            'menu_titre' => $menuTitre,
            'montant_centimes' => $montantCentimes,
            'date' => new UTCDateTime($date),
        ]);
    }

    // Commande annulée ne compte plus dans les statistiques
    public static function retirerCommande(string $numeroCommande): void
    {
        self::collection()->deleteOne(['numero_commande' => $numeroCommande]);
    }

    // Nombre de commandes et chiffre d'affaires par menu.
    // Filtres facultatifs : un menu, une période (du et au inclus).
    public static function parMenu(
        ?int $menuId = null,
        ?\DateTimeInterface $du = null,
        ?\DateTimeInterface $au = null
    ): array {
        $filtre = [];
        if ($menuId !== null) {
            $filtre['menu_id'] = $menuId;
        }
        if ($du !== null) {
            $filtre['date']['$gte'] = new UTCDateTime($du);
        }
        if ($au !== null) {
            // Jour inclus : strictement avant le lendemain à 00h00
            $lendemain = \DateTimeImmutable::createFromInterface($au)->setTime(0, 0)->modify('+1 day');
            $filtre['date']['$lt'] = new UTCDateTime($lendemain);
        }

        return self::collection()->aggregate(
            [
                ['$match' => (object) $filtre],
                ['$group' => [
                    '_id' => '$menu_id',
                    'menu_titre' => ['$first' => '$menu_titre'],
                    'nombre_commandes' => ['$sum' => 1],
                    'chiffre_affaires_centimes' => ['$sum' => '$montant_centimes'],
                ]],
                ['$sort' => ['nombre_commandes' => -1]],
            ],
            // Résultats sous forme de tableaux PHP plutôt qu'en objets BSON
            ['typeMap' => ['root' => 'array', 'document' => 'array', 'array' => 'array']]
        )->toArray();
    }
}
