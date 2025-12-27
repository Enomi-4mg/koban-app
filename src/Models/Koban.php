<?php

namespace App\Models;

use App\Utils\Database;
use App\Utils\Cache;
use PDO;

class Koban
{
    // 検索・一覧取得メソッド
    public function search($params, $limit, $offset, $sort)
    {
        $cacheKey = "koban_search_" . json_encode($params) . "_{$limit}_{$offset}_{$sort}";

        // 'koban_list' というタグを付けて保存
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return $cached;

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
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 結果をキャッシュに保存
        Cache::set($cacheKey, $result, 'koban_list');
        return $result;
    }

    // 総件数を取得するメソッド（ページネーション用）
    public function count($params)
    {
        $cacheKey = "koban_count_" . json_encode($params);
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return $cached;

        $db = Database::connect();
        $searchData = buildSearchQuery($params);

        $stmt = $db->prepare("SELECT COUNT(*) FROM koban " . $searchData['sql']);
        $stmt->execute($searchData['params']);

        // 【修正】executeの結果ではなく、fetchColumn()で実際の数値を取得する
        $result = (int)$stmt->fetchColumn();

        Cache::set($cacheKey, $result);
        return $result;
    }

    // 削除メソッド
    public function delete($id)
    {
        Cache::clearByTag('koban_list');
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
    public function create($data)
    {
        Cache::clearByTag('koban_list');
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
    public function update($id, $data)
    {
        Cache::clearByTag('koban_list');
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
    public function bulkInsert($rows)
    {
        $db = Database::connect();
        try {
            $db->beginTransaction();
            // ★修正前 (SQLite専用)
            // $stmt = $db->prepare("INSERT OR REPLACE INTO koban (id, koban_fullname, type, phone_number, group_code, postal_code, pref, addr3) VALUES (?,?,?,?,?,?,?,?)");

            // ★修正後 (PostgreSQL / SQLite 両対応の書き方)
            // idが重複した際に更新（UPSERT）する構文に変更します
            $sql = "INSERT INTO koban (id, koban_fullname, type, phone_number, group_code, postal_code, pref, addr3) 
                    VALUES (?,?,?,?,?,?,?,?) 
                    ON CONFLICT (id) DO UPDATE SET 
                    koban_fullname=EXCLUDED.koban_fullname, type=EXCLUDED.type, phone_number=EXCLUDED.phone_number, 
                    group_code=EXCLUDED.group_code, postal_code=EXCLUDED.postal_code, pref=EXCLUDED.pref, addr3=EXCLUDED.addr3";
            $stmt = $db->prepare($sql);
            foreach ($rows as $row) {
                // 配列の要素数が足りない場合は空文字で埋める
                $stmt->execute(array_pad($row, 8, ''));
                // 「無し」や空文字の場合は、PostgreSQLが受け入れ可能な null に置き換える
                if ($params[4] === '無し' || $params[4] === '') {
                    $params[4] = null;
                }
            }

            foreach ([3, 5] as $idx) {
                if ($params[$idx] === '無し' || $params[$idx] === '') {
                    $params[$idx] = null;
                }
            }
            
            Cache::clearByTag('koban_list');
            $db->commit();
            return true;
        } catch (\Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    // ★追加: CSV出力用に全件を1行ずつ返す (ジェネレータ)
    public function exportAll($params, $sort)
    {
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
