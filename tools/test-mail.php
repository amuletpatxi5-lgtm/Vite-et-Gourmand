<?php
// Test d'envoi de mail. À lancer en ligne de commande : 
// docker compose exec app php tools/test-mail.php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Mailer;

// Ce script ne doit jamais être appelé depuis un navigateur
if (PHP_SAPI !== 'cli') {
    exit("À lancer uniquement en ligne de commande.\n");
}

$destinataire = (string) getenv('MAIL_CONTACT');

$ok = Mailer::envoyer(
    $destinataire,
    'Test Vite & Gourmand',
    "Bonjour,\n\nCeci est un mail de test envoyé par le site Vite & Gourmand.\nAccents : éèàçô, et l'euro : €\n"
);

echo $ok ? "Mail envoyé à $destinataire\n" : "ÉCHEC de l'envoi (détail ci-dessus)\n";
exit($ok ? 0 : 1);
