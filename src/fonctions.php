<?php

declare(strict_types=1);

// A utiliser pour toute donée venant de la base ou de l'utilisateur.
function e(?string $texte): string
{
    return htmlspecialchars($texte ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function prix(string|float $montant): string
{
    return number_format((float) $montant, 2, ',', "\u{00A0}") . "\u{00A0}€";
}
