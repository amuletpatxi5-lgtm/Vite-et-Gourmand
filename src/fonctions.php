<?php

declare(strict_types=1);
use MongoDB\Builder\Encoder\VariableEncoder;

// A utiliser pour toute donée venant de la base ou de l'utilisateur.
function e(?string $texte): string
{
    return htmlspecialchars($texte ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function prix(string|float $montant): string
{
    return number_format((float) $montant, 2, ',', "\u{00A0}") . "\u{00A0}€";
}

function extrait(string $texte, int $longueur = 140): string
{
    if (mb_strlen($texte) <= $longueur) {
        return $texte;
    }
    return rtrim(mb_substr($texte, 0, $longueur)) . '…';
}

// Jeton CSRF : un par session, placé dans chaque formulaire envoyé en POST
function csrf_jeton(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] =bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf'];
}

function csrf_valide(): bool
{
    return hash_equals(csrf_jeton(), (string) ($_POST['csrf'] ??''));
}

// Après un POST réussi : le rechargement de la page ne renvoie pas le formulaire
function rediriger(string $url): never
{
    header('Location: ' . $url, true, 303);
    exit;
}

// Message affiché une seule fois, sur la page suivante
function message_flash(string $texte): void
{
    $_SESSION['flash'] = $texte;
}

function lire_message_flash(): ?string
{
    $texte = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);

    return $texte;
}

// Compte connecté ou null pour un visiteur
function utilisateur_connecte(): ?array
{
    return $_SESSION['utilisateur'] ?? null;
}

function exiger_connexion(): void
{
    if (utilisateur_connecte() === null) {
        $_SESSION['retour'] = '/' . ltrim($_SERVER['REQUEST_URI'], '/\\');
        message_flash('Connectez-vous pour continuer.');
        rediriger('/connexion');
    }
}
