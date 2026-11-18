<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\Avis;

final class AccueilController extends Controller
{
    public function index(): void
    {
        $this->render('accueil', ['titre' => 'Accueil', 'avis' => Avis::valides()]);
    }
}
