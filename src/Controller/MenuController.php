<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\Menu;

final class MenuController extends Controller
{
    public function liste(): void
    {
        $filtres = $this->filtres();

        $this->render('menus/liste', [
            'titre' => 'Nos menus',
            'scripts' => ['/js/menus.js'],
            'menus' => Menu::filtrer($filtres),
            'filtres' => $filtres,
            'themes' => Menu::themes(),
            'regimes' => Menu::regimes(),
        ]);
    }

    public function filtrer(): void
    {
        $this->partiel('menus/_cartes', ['menus' => Menu::filtrer($this->filtres())]);
    }

    private function filtres(): array
    {
        $prix = ['options' => ['min_range' => 0]];
        $entier = ['options' => ['min_range' => 1]];

        return [
            'prix_min' => filter_input(INPUT_GET, 'prix_min', FILTER_VALIDATE_FLOAT, $prix) ?: null,
            'prix_max' => filter_input(INPUT_GET, 'prix_max', FILTER_VALIDATE_FLOAT, $prix) ?: null,
            'theme' => filter_input(INPUT_GET, 'theme', FILTER_VALIDATE_INT, $entier) ?: null,
            'regime' => filter_input(INPUT_GET, 'regime', FILTER_VALIDATE_INT, $entier) ?: null,
            'personnes' => filter_input(INPUT_GET, 'personnes', FILTER_VALIDATE_INT, $entier) ?: null,
        ];
    }
}
