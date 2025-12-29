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

    // ログイン失敗時にカウントを1増やす
    public function incrementFailureCount($login_id)
    {
        $db = Database::connect();
        $stmt = $db->prepare("UPDATE admin_users SET failure_count = failure_count + 1 WHERE login_id = ?");
        return $stmt->execute([$login_id]);
    }

    // アカウントをロックする
    public function lockAccount($login_id, $until_time)
    {
        $db = Database::connect();
        $stmt = $db->prepare("UPDATE admin_users SET locked_until = ? WHERE login_id = ?");
        return $stmt->execute([$until_time, $login_id]);
    }

    // ログイン成功時に失敗回数をリセット
    public function resetFailureCount($login_id)
    {
        $db = Database::connect();
        $stmt = $db->prepare("UPDATE admin_users SET failure_count = 0 WHERE login_id = ?");
        $stmt->execute([$login_id]);
    }

    // パスワード変更/リセット時にロックも同時解除するようにする
    public function updatePassword($login_id, $new_hash)
    {
        $db = Database::connect();
        // パスワード更新時に、失敗回数とロック期限もクリアする
        $stmt = $db->prepare("UPDATE admin_users SET password_hash = ?, failure_count = 0, locked_until = NULL WHERE login_id = ?");
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
    public function findAll()
    {
        $db = Database::connect();
        // 最終操作ログの日時もサブクエリで取得（元のロジックを継承）
        $sql = "SELECT u.login_id, u.perm_data, u.perm_admin, u.perm_log, u.request_status,
                (SELECT action_time FROM audit_logs l WHERE l.user_id = u.login_id ORDER BY id DESC LIMIT 1) as last_act_time,
                (SELECT action_type FROM audit_logs l WHERE l.user_id = u.login_id ORDER BY id DESC LIMIT 1) as last_act_type
                FROM admin_users u ORDER BY u.login_id ASC";
        return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // 管理者削除
    public function delete($login_id)
    {
        $db = Database::connect();
        $stmt = $db->prepare("DELETE FROM admin_users WHERE login_id = ?");
        return $stmt->execute([$login_id]);
    }

    // ID重複チェック用
    public function exists($login_id)
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT COUNT(*) FROM admin_users WHERE login_id = ?");
        $stmt->execute([$login_id]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * 特定の管理者の権限フラグのみを更新する
     */
    public function updatePermissions($login_id, $perms)
    {
        $db = Database::connect();
        $sql = "UPDATE admin_users SET perm_data = ?, perm_admin = ?, perm_log = ? WHERE login_id = ?";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            $perms['data'],
            $perms['admin'],
            $perms['log'],
            $login_id
        ]);
    }

    /**
     * 申請中(pending)のユーザー数を取得する
     */
    public function countPendingRequests()
    {
        $db = Database::connect();
        $sql = "SELECT COUNT(*) FROM admin_users WHERE request_status = 'pending'";
        return (int)$db->query($sql)->fetchColumn();
    }

    /**
     * 権限申請のステータスと権限を更新する
     */
    public function updateRequestStatus($login_id, $action)
    {
        $db = Database::connect();
        if ($action === 'approve') {
            // 承認：データ権限(perm_data)を1にし、申請ステータスをクリア
            $sql = "UPDATE admin_users SET perm_data = 1, request_status = NULL, request_message = NULL WHERE login_id = ?";
        } else {
            // 却下：ステータスを rejected に変更
            $sql = "UPDATE admin_users SET request_status = 'rejected' WHERE login_id = ?";
        }
        $stmt = $db->prepare($sql);
        return $stmt->execute([$login_id]);
    }

    /**
     * ユーザーからの詳細な権限申請を保存する
     */
    public function saveDetailedRequest($login_id, $reason, $req_perms)
    {
        $db = \App\Utils\Database::connect();
        $sql = "UPDATE admin_users SET 
                request_status = 'pending', 
                request_message = ?, 
                requested_at = ?,
                req_perm_data = ?, 
                req_perm_admin = ?, 
                req_perm_log = ? 
            WHERE login_id = ?";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            $reason,
            date('Y-m-d H:i:s'),
            $req_perms['data'] ? 1 : 0,
            $req_perms['admin'] ? 1 : 0,
            $req_perms['log'] ? 1 : 0,
            $login_id
        ]);
    }

    /**
     * 審査に基づき、選択された権限を付与して申請を完了させる
     */
    public function approveRequestWithDetails($login_id, $perms)
    {
        $db = Database::connect();
        // 承認された権限を反映し、申請中の状態(フラグ・メッセージ)をクリアする
        $sql = "UPDATE admin_users SET 
                perm_data = ?, 
                perm_admin = ?, 
                perm_log = ?, 
                request_status = NULL, 
                request_message = NULL,
                req_perm_data = 0,
                req_perm_admin = 0,
                req_perm_log = 0
            WHERE login_id = ?";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            $perms['perm_data'],
            $perms['perm_admin'],
            $perms['perm_log'],
            $login_id
        ]);
    }
}
