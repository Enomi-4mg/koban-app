<?php

namespace App\Models;

use App\Utils\Database;
use PDO;

class Koban
{
    // 検索・一覧取得メソッド
    public function search($params, $limit, $offset, $sort)
    {
        $db = Database::connect();

        // 検索条件の構築 (functions.phpの関数を利用)
        $searchData = buildSearchQuery($params);
        $sql = "SELECT * FROM koban " . $searchData['sql'];

        // ソート順の決定
        $sort_sql = match ($sort) {
            'id_desc' => "ORDER BY id DESC",
            'name_asc' => "ORDER BY koban_fullname ASC",
            default => "ORDER BY id ASC"
        };
        $sql .= " $sort_sql LIMIT $limit OFFSET $offset";

        $stmt = $db->prepare($sql);
        $stmt->execute($searchData['params']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 総件数を取得するメソッド（ページネーション用）
    public function count($params)
    {
        $db = Database::connect();
        $searchData = buildSearchQuery($params);

        $stmt = $db->prepare("SELECT COUNT(*) FROM koban " . $searchData['sql']);
        $stmt->execute($searchData['params']);
        return $stmt->fetchColumn();
    }

    // 削除メソッド
    public function delete($id)
    {
        $db = Database::connect();
        $stmt = $db->prepare("DELETE FROM koban WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // ★追加: IDで1件データを取得（編集画面用
    public function findById($id)
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM koban WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ★追加: 新規登録
    public function create($data) {
        $db = Database::connect();
        $sql = "INSERT INTO koban (koban_fullname, type, phone_number, group_code, postal_code, pref, addr3) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            $data['koban_fullname'],
            $data['type'],
            $data['phone_number'],
            $data['group_code'],
            $data['postal_code'],
            $data['pref'],
            $data['addr3']
        ]);
    }

    // ★追加: 更新
    public function update($id, $data) {
        $db = Database::connect();
        $sql = "UPDATE koban SET koban_fullname=?, type=?, phone_number=?, group_code=?, postal_code=?, pref=?, addr3=? WHERE id=?";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            $data['koban_fullname'],
            $data['type'],
            $data['phone_number'],
            $data['group_code'],
            $data['postal_code'],
            $data['pref'],
            $data['addr3'],
            $id
        ]);
    }

    // ★追加: CSV一括登録（トランザクション対応）
    public function bulkInsert($rows) {
        $db = Database::connect();
        try {
            $db->beginTransaction();
            $stmt = $db->prepare("INSERT OR REPLACE INTO koban (id, koban_fullname, type, phone_number, group_code, postal_code, pref, addr3) VALUES (?,?,?,?,?,?,?,?)");
            foreach ($rows as $row) {
                // 配列の要素数が足りない場合は空文字で埋める
                $stmt->execute(array_pad($row, 8, ''));
            }
            $db->commit();
            return true;
        } catch (\Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    // ★追加: CSV出力用に全件を1行ずつ返す (ジェネレータ)
    public function exportAll($params, $sort) {
        $db = Database::connect();
        
        // functions.php の buildSearchQuery を利用
        $searchData = buildSearchQuery($params);
        $sql = "SELECT * FROM koban " . $searchData['sql'];
        
        // ソート順
        $sort_sql = match ($sort) {
            'id_desc' => "ORDER BY id DESC",
            'name_asc' => "ORDER BY koban_fullname ASC",
            default => "ORDER BY id ASC"
        };
        $sql .= " $sort_sql"; // LIMITとOFFSETは付けない！

        // プリペアドステートメント実行
        // (バッファなしクエリの設定などはSQLiteの場合あまり気にしなくてOKですが、MySQL等の場合はここで設定します)
        $stmt = $db->prepare($sql);
        $stmt->execute($searchData['params']);

        // ★ここがポイント: fetchAllせず、1行ずつ yield で返す
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            yield $row;
        }
    }
}
