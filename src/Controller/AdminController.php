<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\Menu;
use App\Stats;
use DateTimeImmutable;
use Throwable;

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
}
