<?php
// Contrôle pour vérifier que PHP se connecte à PostgreSQL et à MongoDB.
// Ouvrir sur http://localhost:8080/sante.php pour tester la connexion

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;

header('Content-Type: text/plain; charset=utf-8');

// Les paramètres viennent du .env, transmis par docker-compose (env_file).
$dsn = sprintf(
    'pgsql:host=%s;port=%s;dbname=%s',
    getenv('DB_HOST'),
    getenv('DB_PORT'),
    getenv('DB_NAME')
);

try {
    $pdo = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASSWORD'), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    // Réglage prévu dans 01-schema.sql : heure de Bordeaux
    $pdo->exec("SET TIME ZONE 'Europe/Paris'");

    $version = $pdo->query('SHOW server_version')->fetchColumn();
    $heure = $pdo->query('SELECT CURRENT_TIMESTAMP')->fetchColumn();
    $table = $pdo->query(
        "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'public'"
    )->fetchColumn();

    echo "ok\n";
    echo 'PHP version : ' . PHP_VERSION . "\n";
    echo "PostgreSQL : $version\n";
    echo "Heure base : $heure\n";
    echo "Tables : $table\n";
} catch (PDOException $e) {
    http_response_code(500);
    echo "ERREUR : connexion à la base impossible\n";
    if (getenv('APP_ENV') === 'dev') {
        echo $e->getMessage() . "\n";
    }
}
// MongoDB : base des statistiques
try {
    // 3 secondes maximum pour trouver le serveur (30 par défaut)
    $mongo = new Client((string) getenv('MONGO_URI'), ['serverSelectionTimeoutMS' => 3000]);
    $info = $mongo->selectDatabase('admin')->command(['buildInfo' => 1])->toArray()[0];
    echo "MongoDB : {$info['version']}\n";
} catch (MongoDB\Driver\Exception\Exception $e) {
    echo "ERREUR : connexion à MongoDB impossible\n";
    if (getenv('APP_ENV') === 'dev') {
        echo $e->getMessage() . "\n";
    }
}
