<?php

namespace App\Core;

use App\Support\Env;
use PDO;

final class Database
{
    private static ?PDO $pdo = null;

    public static function connect(): PDO
    {
        if (self::$pdo) {
            return self::$pdo;
        }
        $driver = Env::get('DB_CONNECTION', 'sqlite');
        if ($driver === 'mysql') {
            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', Env::get('DB_HOST', '127.0.0.1'), Env::get('DB_PORT', '3306'), Env::get('DB_DATABASE', 'shreeaxar_erp'));
            self::$pdo = new PDO($dsn, Env::get('DB_USERNAME', 'root'), Env::get('DB_PASSWORD', ''), self::options());
        } else {
            $database = Env::get('DB_DATABASE', dirname(__DIR__, 2).'/storage/database.sqlite');
            if ($database !== ':memory:') {
                $dir = dirname($database);
                if (! is_dir($dir)) mkdir($dir, 0775, true);
                if (! is_file($database)) touch($database);
            }
            self::$pdo = new PDO('sqlite:'.$database, null, null, self::options());
            self::$pdo->exec('PRAGMA foreign_keys = ON');
        }
        return self::$pdo;
    }

    public static function reset(?PDO $pdo = null): void
    {
        self::$pdo = $pdo;
    }

    /** @return array<int, mixed> */
    private static function options(): array
    {
        return [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false];
    }
}
