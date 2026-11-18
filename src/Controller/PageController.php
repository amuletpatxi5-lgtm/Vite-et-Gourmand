<?php

declare(strict_types= 1);

namespace App\Controller;

final class PageController extends Controller
{
    public function mentionsLegales(): void
    {
        $this->render('pages/mentions-legales', ['titre' => 'Mentions légales']);
    }

    public function cgv(): void
    {
        $this->render('pages/cgv', ['titre' => 'Conditions générales de vente']);
    }
}
