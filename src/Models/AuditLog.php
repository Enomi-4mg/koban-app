<?php

namespace App\Models;

use App\Utils\Database;
use PDO;

class AuditLog
{

    // ログ検索・一覧取得
    public function search($params, $limit = 500)
    {
        $db = Database::connect();
        $query = $this->buildQuery($params);

        $sql = "SELECT * FROM audit_logs " . $query['where'] . " ORDER BY id DESC LIMIT " . (int)$limit;
        $stmt = $db->prepare($sql);
        $stmt->execute($query['params']);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 新しいログのみ取得（リアルタイム更新API用）
    public function getNewLogs($lastId, $params)
    {
        $db = Database::connect();
        $query = $this->buildQuery($params);

        // 既存の検索条件 AND id > ?
        $where = $query['where'] === "" ? "WHERE id > ?" : $query['where'] . " AND id > ?";
        $params = $query['params'];
        $params[] = $lastId;

        $stmt = $db->prepare("SELECT * FROM audit_logs " . $where . " ORDER BY id DESC");
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 操作種別（Action Type）のリスト取得（ドロップダウン用）
    public function getActionTypes()
    {
        $db = Database::connect();
        return $db->query("SELECT DISTINCT action_type FROM audit_logs ORDER BY action_type ASC")
            ->fetchAll(PDO::FETCH_COLUMN);
    }

    // 内部用: 検索クエリ構築メソッド
    private function buildQuery($params)
    {
        $conditions = [];
        $sqlParams = [];

        // ユーザーID
        if (!empty($params['filter_user'])) {
            $conditions[] = "user_id LIKE ?";
            $sqlParams[] = '%' . $params['filter_user'] . '%';
        }

        // 操作種別
        if (!empty($params['filter_action'])) {
            if ($params['filter_action'] === 'AUTH_SET') {
                $conditions[] = "(action_type LIKE ? OR action_type LIKE ? OR action_type LIKE ?)";
                $sqlParams[] = '%ログイン%';
                $sqlParams[] = '%ログアウト%';
                $sqlParams[] = '%権限%';
            } else {
                $conditions[] = "action_type = ?";
                $sqlParams[] = $params['filter_action'];
            }
        }

        // 日時範囲
        if (!empty($params['filter_date_start'])) {
            $conditions[] = "action_time >= ?";
            $sqlParams[] = $params['filter_date_start'] . ' 00:00:00';
        }
        if (!empty($params['filter_date_end'])) {
            $conditions[] = "action_time <= ?";
            $sqlParams[] = $params['filter_date_end'] . ' 23:59:59';
        }

        // キーワード
        if (!empty($params['filter_keyword'])) {
            $conditions[] = "details LIKE ?";
            $sqlParams[] = '%' . $params['filter_keyword'] . '%';
        }

        $whereSql = !empty($conditions) ? "WHERE " . implode(' AND ', $conditions) : "";

        return ['where' => $whereSql, 'params' => $sqlParams];
    }
}
