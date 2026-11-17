<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/fonctions.php';

use App\Controller\AccueilController;
use App\Controller\Controller;
use App\Controller\MenuController;
use App\Controller\CompteController;

ini_set('display_errors', getenv('APP_ENV') === 'dev' ? '1' : 0);

session_start([
    'cookie_httponly' => true,
    'cookie_secure' => getenv('APP_ENV') !== 'dev',
    'cookie_samesite' => 'Lax',
    'use_strict_mode' => true,
]);

$routes = [
    '/' => [AccueilController::class, 'index'],
    '/menus' => [MenuController::class, 'liste'],
    '/menus/filtrer' => [MenuController::class, 'filtrer'],
    '/menu' => [MenuController::class, 'detail'],
    '/inscription' => [CompteController::class, 'inscription'],
    '/connexion' => [CompteController::class, 'connexion'],
    '/deconnexion' => [CompteController::class, 'deconnexion'],
];

$chemin = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
if ($chemin !== '/') {
    $chemin = rtrim($chemin, '/');
}

try {
    if (isset($routes[$chemin])) {
        [$classe, $methode] = $routes[$chemin];
        (new $classe())->$methode();
    } else {
        (new Controller())->introuvable();
    }
} catch (Throwable $e) {
    error_log((string) $e);
    http_response_code(500);
    echo 'une erreur est survenue. Merci de réessayer dans quelques instants.';
}
