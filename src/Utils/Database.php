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
<!-- <?php ?>-->

// namespace App\Utils;

// use PDO;
// use PDOException;

// class Database
// {
//     // データベース接続を返す静的メソッド
//     public static function connect(): PDO
//     {
//         // ※パスに注意: コンテナ内の絶対パス、または相対パスを指定
//         // ここではプロジェクトルートにある koban.sqlite を想定しています
//         $dbPath = $_ENV['DB_PATH'] ?? __DIR__ . '/../../SQL/opendata.sqlite';

//         try {
//             $pdo = new PDO("sqlite:" . $dbPath);
//             $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//             $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
//             return $pdo;
//         } catch (PDOException $e) {
//             // 本番環境(production)ならエラー詳細を隠す
//             if (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'production') {
//                 error_log($e->getMessage()); // サーバーのログには残す
//                 exit('ただいまアクセスが集中しているか、メンテナンス中です。');
//             }
//             // 開発環境ならエラーを表示
//             exit('DB Connection Error: ' . $e->getMessage());
//         }
//     }
// }
