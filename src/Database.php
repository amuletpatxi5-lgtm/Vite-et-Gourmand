<?php

declare(strict_types=1);

namespace App;

use PDO;

// Connexion unique à PostgreSQL, partagée par tous les modèles
final class Database
{
    private static ?PDO $pdo = null;

    public static function connexion(): PDO
    {
        if (self::$pdo === null) {
            $dsn = sprintf(
             'pgsql:host=%s;port=%s;dbname=%s',
            getenv('DB_HOST'),
            getenv('DB_PORT'),
            getenv('DB_NAME')
            );
            self::$pdo = new PDO($dsn, (string) getenv('DB_USER'), (string) getenv('DB_PASSWORD'), [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,   // vraies requêtes préparées (injection SQL)
            ]);
            self::$pdo->exec("SET TIME ZONE 'Europe/Paris'");
        }

        return self::$pdo;
    }
}
