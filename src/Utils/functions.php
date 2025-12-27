<?php
// 名前空間は定義せず、グローバル関数として扱います

use App\Utils\Database;

define('PAGE_LIMIT', 100);
date_default_timezone_set('Asia/Tokyo');
// HTMLエスケープ (頻繁に使うので短い名前のまま)
function h($str)
{
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

// CSRFトークン検証
function verifyCsrfToken()
{
    // セッションが開始されていなければ開始
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // トークン生成（なければ作る）
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    // POST送信時はチェックを行う
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            http_response_code(403);
            die('CSRF Token Verification Failed');
        }
    }
}

function sendSecurityHeaders()
{
    // クリックジャッキング対策（iframeでの表示を禁止）
    header('X-Frame-Options: DENY');

    // XSSフィルター機能の強制有効化（古いブラウザ向け）
    header('X-XSS-Protection: 1; mode=block');

    // コンテンツタイプのスニッフィング防止（画像に見せかけたスクリプト実行などを防ぐ）
    header('X-Content-Type-Options: nosniff');

    // 可能な限りHTTPSを強制（本番環境のみ推奨）
    // header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

// 権限チェックヘルパー
function hasPermission($type)
{
    if (!isset($_SESSION['permissions'])) return false;
    return !empty($_SESSION['permissions'][$type]);
}

/**
 * 接続元の真のIPアドレスを取得する
 */
function getRemoteIp() {
    // Renderやプロキシ環境では X-Forwarded-For を優先する
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // カンマ区切りで複数入ることがあるので最初の1つを取得
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]);
    }
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

// ログ記録 (Databaseクラスを使う形に少し修正)
function logAction($userId, $actionType, $details)
{
    try {
        $db = \App\Utils\Database::connect();
        $sql = "INSERT INTO audit_logs (user_id, action_type, details, ip_address, user_agent, action_time) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);

        $success = $stmt->execute([
            $userId,
            $actionType,
            $details,
            getRemoteIp(),
            $_SERVER['HTTP_USER_AGENT'] ?? '',
            date('Y-m-d H:i:s')
        ]);

        if (!$success) {
            // SQLの実行に失敗した場合、エラー内容を Render のログへ出力
            $errorInfo = $stmt->errorInfo();
            error_log("【DB ERROR】Log save failed: " . $errorInfo[2]);
        }
    } catch (\Exception $e) {
        // 接続エラーなどの致命的エラーを記録
        error_log("【LOG CRITICAL ERROR】" . $e->getMessage());
    }
}

/**
 * 検索クエリ構築ヘルパー
 */
function buildSearchQuery($getParams)
{
    $where_clauses = [];
    $params = [];
    $log_parts = [];

    // --- 1. 専用ID検索 (追加) ---
    // 完全一致で検索するため、高速かつPostgreSQLでも安全です
    if (!empty($getParams['search_id'])) {
        $where_clauses[] = "id = ?";
        $params[] = (int)$getParams['search_id'];
        $log_parts[] = "ID: " . $getParams['search_id'];
    }

    // --- 2. 都道府県・種別 (既存) ---
    if (!empty($getParams['search_pref'])) {
        $where_clauses[] = "pref = ?";
        $params[] = $getParams['search_pref'];
        $log_parts[] = "県: " . $getParams['search_pref'];
    }
    if (!empty($getParams['search_type'])) {
        $where_clauses[] = "type = ?";
        $params[] = $getParams['search_type'];
        $log_parts[] = "種別: " . $getParams['search_type'];
    }

    // --- 3. キーワード検索 (修正) ---
    if (!empty($getParams['keyword'])) {
        $keyword = $getParams['keyword'];
        $keywords = preg_split('/[\s]+/', mb_convert_kana($keyword, 's', 'UTF-8'));
        foreach ($keywords as $word) {
            if ($word === "") continue;
            // PostgreSQL対応: id を CAST(id AS TEXT) に変更して LIKE を通るようにする
            $where_clauses[] = "(pref LIKE ? OR CAST(id AS TEXT) LIKE ? OR koban_fullname LIKE ? OR addr3 LIKE ?)";
            $params = array_merge($params, array_fill(0, 4, "%" . $word . "%"));
        }
        $log_parts[] = "KW: $keyword";
    }

    $where_sql = !empty($where_clauses) ? "WHERE " . implode(' AND ', $where_clauses) : "";

    return [
        'sql' => $where_sql,
        'params' => $params,
        'log_detail' => empty($log_parts) ? "全データ" : implode(' ', $log_parts)
    ];
}
