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

function sendSecurityHeaders() {
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

// ログ記録 (Databaseクラスを使う形に少し修正)
function logAction($userId, $actionType, $details)
{
    // 引数から $db を消し、内部で取得するように変更すると使いやすくなります
    try {
        $db = Database::connect(); // さきほど作ったクラスを利用
        $sql = "INSERT INTO audit_logs (user_id, action_type, details, ip_address, user_agent, action_time) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            $userId,
            $actionType,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? '',
            date('Y-m-d H:i:s')
        ]);
    } catch (Exception $e) {
        // ログ記録失敗はメイン処理を止めないように握りつぶすことが多い
    }
}

// 検索クエリ構築ヘルパー（元のcommon.phpからロジックを移動）
function buildSearchQuery($getParams)
{
    $where_clauses = [];
    $params = [];
    $log_parts = [];

    $keyword = $getParams['keyword'] ?? '';
    $type = $getParams['search_type'] ?? '';
    $pref = $getParams['search_pref'] ?? '';

    if ($keyword !== "") {
        $keywords = preg_split('/[\s]+/', mb_convert_kana($keyword, 's', 'UTF-8'));
        foreach ($keywords as $word) {
            if ($word === "") continue;
            $where_clauses[] = "(pref LIKE ? OR id LIKE ? OR koban_fullname LIKE ? OR addr3 LIKE ?)";
            $params = array_merge($params, array_fill(0, 4, "%" . $word . "%"));
        }
        $log_parts[] = "KW: $keyword";
    }
    if ($type !== "") {
        $where_clauses[] = "type = ?";
        $params[] = $type;
        $log_parts[] = "種別: $type";
    }
    if ($pref !== "") {
        $where_clauses[] = "pref = ?";
        $params[] = $pref;
        $log_parts[] = "県: $pref";
    }

    $where_sql = !empty($where_clauses) ? "WHERE " . implode(' AND ', $where_clauses) : "";

    return [
        'sql' => $where_sql,
        'params' => $params,
        'log_detail' => empty($log_parts) ? "全データ" : implode(' ', $log_parts)
    ];
}
