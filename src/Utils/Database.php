<?php
namespace App\Utils;
use PDO;
use PDOException;

class Database {
    public static function connect(): PDO {
        // Renderの環境変数から接続情報を取得
        $dbUrl = $_ENV['DATABASE_URL'] ?? null;

        if ($dbUrl) {
            // PostgreSQL接続 (Render本番環境)
            $dbopts = parse_url($dbUrl);
            $dsn = sprintf("pgsql:host=%s;port=%d;dbname=%s", 
                $dbopts["host"], $dbopts["port"], ltrim($dbopts["path"], "/"));
            $user = $dbopts["user"];
            $pass = $dbopts["pass"];
            return new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        } else {
            // SQLite接続 (ローカル開発環境)
            $dbPath = __DIR__ . '/../../SQL/opendata.sqlite';
            return new PDO("sqlite:" . $dbPath, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
        }
    }
}
?>
