<?php
namespace App\Utils;
use PDO;

class Database {
    private static $instance = null;

    public static function connect(): PDO {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $dbUrl = $_ENV['DATABASE_URL'] ?? null;

        if ($dbUrl) {
            $dbopts = parse_url($dbUrl);
            $dsn = sprintf("pgsql:host=%s;port=%d;dbname=%s", 
                $dbopts["host"], $dbopts["port"], ltrim($dbopts["path"], "/"));
            
            self::$instance = new PDO($dsn, $dbopts["user"], $dbopts["pass"], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                // ▼ 重要：永続接続を有効にする
                PDO::ATTR_PERSISTENT => true,
                // トランザクションモードのプーラーと相性の良いタイムアウト設定
                PDO::ATTR_TIMEOUT => 5,
            ]);
        } else {
            // SQLite (開発用)
            $dbPath = __DIR__ . '/../../SQL/opendata.sqlite';
            self::$instance = new PDO("sqlite:" . $dbPath, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
        }

        return self::$instance;
    }
}
?>