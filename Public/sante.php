<?php
// Contrôle pour vérifier que PHP se connecte à PostgreSQL.
// Ouvrir sur http://localhost:8080/sante.php pour tester la connexion

declare(strict_types=1);

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
