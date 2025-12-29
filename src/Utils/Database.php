<?php
namespace App\Utils;
use PDO;
use Exception;

class Database {
    // インスタンス保持用の静的変数（シングルトン）
    private static $instance = null;

    public static function connect(): PDO {
        // すでに接続済みの場合は、既存のインスタンスを即座に返す
        if (self::$instance !== null) {
            return self::$instance;
        }

        // --- 計測開始 ---
        $startTime = microtime(true);

        $dbUrl = $_ENV['DATABASE_URL'] ?? null;

        try {
            if ($dbUrl) {
                // PostgreSQL接続 (Render本番環境)
                $dbopts = parse_url($dbUrl);
                $dsn = sprintf("pgsql:host=%s;port=%d;dbname=%s", 
                    $dbopts["host"], $dbopts["port"], ltrim($dbopts["path"], "/"));
                $user = $dbopts["user"];
                $pass = $dbopts["pass"];
                
                self::$instance = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    // 接続が安定するまで一旦 false に
                    PDO::ATTR_PERSISTENT => false,
                    // タイムアウト設定（応答がない場合に5秒で切り上げる）
                    PDO::ATTR_TIMEOUT => 5,
                ]);
            } else {
                // SQLite接続 (ローカル開発環境)
                $dbPath = __DIR__ . '/../../SQL/backup.sql';
                self::$instance = new PDO("sqlite:" . $dbPath, null, null, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ]);
            }

            // --- 計測終了とログ出力 ---
            $endTime = microtime(true);
            $duration = round(($endTime - $startTime) * 1000, 2); // ミリ秒単位に変換
            
            // Renderのログに表示されるよう出力
            error_log("【PERF】DB Connected in {$duration}ms (Host: " . ($dbopts['host'] ?? 'sqlite') . ")");

        } catch (Exception $e) {
            error_log("【ERROR】DB Connection Failed: " . $e->getMessage());
            throw $e;
        }

        return self::$instance;
    }
}
?>