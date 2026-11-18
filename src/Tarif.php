<?php

declare(strict_types=1);

namespace App;

// Prix d'une commande selon l'énoncé (p. 7). Montants en centimes.
final class Tarif
{
    public const REMISE_POURCENT = 10;      // réduction de 10 %
    public const REMISE_PERSONNES = 5;      // à partir du minimum + 5 personnes
    public const LIVRAISON_FORFAIT = 500;   // 5 € hors Bordeaux
    public const LIVRAISON_PAR_KM = 59;     // 0,59 € par kilomètre

    /** @return array{menu: int, remise: int, livraison: int, total: int} */
    public static function calculer(
        int $prixParPersonne,
        int $minimum,
        int $personnes,
        bool $dansBordeaux,
        float $distanceKm
    ): array {
        if ($personnes < $minimum) {
            throw new \InvalidArgumentException("Ce menu se commande pour $minimum personnes minimum.");
        }

        $menu = $prixParPersonne * $personnes;

        // Arrondi au centime le plus proche : (x * 10 + 50) / 100
        $remise = $personnes >= $minimum + self::REMISE_PERSONNES
            ? intdiv($menu * self::REMISE_POURCENT + 50, 100)
            : 0;

        $livraison = $dansBordeaux
            ? 0
            : self::LIVRAISON_FORFAIT + (int) round($distanceKm * self::LIVRAISON_PAR_KM);

        return [
            'menu' => $menu,
            'remise' => $remise,
            'livraison' => $livraison,
            'total' => $menu - $remise + $livraison,
        ];
    }
}