<?php

declare(strict_types=1);

// A utiliser pour toute donée venant de la base ou de l'utilisateur.
function e(?string $texte): string
{
    return htmlspecialchars($texte ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
