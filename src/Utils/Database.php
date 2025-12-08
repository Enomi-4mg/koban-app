<?php

namespace App\Utils;

use PDO;
use PDOException;

class Database
{
    // データベース接続を返す静的メソッド
    public static function connect(): PDO
    {
        // ※パスに注意: コンテナ内の絶対パス、または相対パスを指定
        // ここではプロジェクトルートにある koban.sqlite を想定しています
        $dbPath = $_ENV['DB_PATH'] ?? __DIR__ . '/../../SQL/opendata.sqlite';

        try {
            $pdo = new PDO("sqlite:" . $dbPath);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $pdo;
        } catch (PDOException $e) {
            // 本番環境(production)ならエラー詳細を隠す
            if (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'production') {
                error_log($e->getMessage()); // サーバーのログには残す
                exit('ただいまアクセスが集中しているか、メンテナンス中です。');
            }
            // 開発環境ならエラーを表示
            exit('DB Connection Error: ' . $e->getMessage());
        }
    }
}
