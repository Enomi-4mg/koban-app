<?php

namespace App\Models;

use App\Utils\Database;
use PDO;

class AdminUser
{
    // IDでユーザー情報を取得
    public function findById($login_id)
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM admin_users WHERE login_id = ?");
        $stmt->execute([$login_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ログイン成功時に失敗回数をリセット
    public function resetFailureCount($login_id)
    {
        $db = Database::connect();
        $stmt = $db->prepare("UPDATE admin_users SET failure_count = 0 WHERE login_id = ?");
        $stmt->execute([$login_id]);
    }

    // パスワード変更（change_password.php等で使用）
    public function updatePassword($login_id, $new_hash)
    {
        $db = Database::connect();
        $stmt = $db->prepare("UPDATE admin_users SET password_hash = ? WHERE login_id = ?");
        return $stmt->execute([$new_hash, $login_id]);
    }

    // 管理者作成（初期設定用）
    public function create($login_id, $password, $perms = [])
    {
        $db = Database::connect();
        $hash = password_hash($password, PASSWORD_DEFAULT);

        // 権限設定のデフォルト
        $p_data  = $perms['data'] ?? 1;
        $p_admin = $perms['admin'] ?? 1;
        $p_log   = $perms['log'] ?? 1;

        $sql = "INSERT INTO admin_users (login_id, password_hash, role, perm_data, perm_admin, perm_log) VALUES (?, ?, 1, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        return $stmt->execute([$login_id, $hash, $p_data, $p_admin, $p_log]);
    }
    
    // ★追加: 全管理者を取得（一覧画面用）
    public function findAll() {
        $db = Database::connect();
        // 最終操作ログの日時もサブクエリで取得（元のロジックを継承）
        $sql = "SELECT u.login_id, u.perm_data, u.perm_admin, u.perm_log,
                (SELECT action_time FROM audit_logs l WHERE l.user_id = u.login_id ORDER BY id DESC LIMIT 1) as last_act_time,
                (SELECT action_type FROM audit_logs l WHERE l.user_id = u.login_id ORDER BY id DESC LIMIT 1) as last_act_type
                FROM admin_users u ORDER BY u.login_id ASC";
        return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // ★追加: 管理者削除
    public function delete($login_id) {
        $db = Database::connect();
        $stmt = $db->prepare("DELETE FROM admin_users WHERE login_id = ?");
        return $stmt->execute([$login_id]);
    }
    
    // ★追加: ID重複チェック用
    public function exists($login_id) {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT COUNT(*) FROM admin_users WHERE login_id = ?");
        $stmt->execute([$login_id]);
        return $stmt->fetchColumn() > 0;
    }
}
