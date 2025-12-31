<?php

namespace App\Utils;

use PDO;
use Exception;

class Database
{
    // インスタンス保持用の静的変数（シングルトン）
    private static $instance = null;

    public static function connect(): PDO
    {
        // すでに接続済みの場合は、既存のインスタンスを即座に返す
        if (self::$instance !== null) {
            return self::$instance;
        }

        // --- 計測開始 ---
        $startTime = microtime(true);

        $dbUrl = $_ENV['DATABASE_URL'] ?? null;

        try {
            if (!empty($dbUrl)) {
                // --- 本番環境: PostgreSQL (Supavisor経由) ---
                $dbopts = parse_url($dbUrl);
                $user = isset($dbopts["user"]) ? urldecode($dbopts["user"]) : null;
                $pass = isset($dbopts["pass"]) ? urldecode($dbopts["pass"]) : null;
                $host = $dbopts["host"];
                // ポートをプーラー(6543)に固定してIPv6エラーを回避
                $port = ($dbopts["port"] == 5432 || !isset($dbopts["port"])) ? 6543 : $dbopts["port"];
                $path = ltrim($dbopts["path"], "/");

                $dsn = sprintf("pgsql:host=%s;port=%d;dbname=%s;sslmode=require", $host, $port, $path);
                self::$instance = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_TIMEOUT => 10,
                ]);
                error_log("【SUCCESS】Connected to Supabase via Pooler (IPv4/Port 6543)");
            } else {
                // --- ローカル環境: SQLite ---
                $dbPath = __DIR__ . '/../../SQL/backup.sqlite'; // 先ほど作成したバイナリを指定
                self::$instance = new PDO("sqlite:" . $dbPath, null, null, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
                error_log("【SUCCESS】Connected to Local SQLite");
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
