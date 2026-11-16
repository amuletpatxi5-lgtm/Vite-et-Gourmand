<?php
// Test des statistiques MongoDB, à lancer dans le terminal :
// docker compose exec app php tools/test-stats.php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Stats;
use MongoDB\Client;

// Sécurité : ce script ne doit jamais être appelé depuis un navigateur
if (PHP_SAPI !== 'cli') {
    exit("À lancer en ligne de commande.\n");
}

// Affiche un tableau de statistiques, montants convertis en euros
function afficher(string $titre, array $lignes): void
{
    echo "--- $titre\n";
    foreach ($lignes as $ligne) {
        printf(
            "%s : %d commande(s), %.2f €\n",
            $ligne['menu_titre'],
            $ligne['nombre_commandes'],
            $ligne['chiffre_affaires_centimes'] / 100
        );
    }
}

// 1. Trois commandes fictives, dont une le dernier jour du mois à 18h
Stats::enregistrerCommande('TEST-001', 1, 'Menu de Noël', 4500, new DateTimeImmutable('2026-11-10 12:00'));
Stats::enregistrerCommande('TEST-002', 1, 'Menu de Noël', 4500, new DateTimeImmutable('2026-11-30 18:00'));
Stats::enregistrerCommande('TEST-003', 2, 'Menu Végan', 3000, new DateTimeImmutable('2026-10-05 19:30'));

// 2. Lecture : sans filtre, puis sur novembre (le 30 doit être inclus)
afficher('Toutes les commandes', Stats::parMenu());
afficher('Novembre 2026', Stats::parMenu(
    null,
    new DateTimeImmutable('2026-11-01'),
    new DateTimeImmutable('2026-11-30')
));

// 3. Nettoyage : suppression des commandes de test (numéros TEST-...)
$resultat = (new Client((string) getenv('MONGO_URI')))
    ->selectDatabase((string) getenv('MONGO_DB'))
    ->selectCollection('commandes')
    ->deleteMany(['numero_commande' => ['$regex' => '^TEST-']]);

echo "--- Nettoyage : {$resultat->getDeletedCount()} commande(s) de test supprimée(s)\n";
