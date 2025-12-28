<?php

namespace App\Controllers;

// 必要なクラス（部品）を読み込む宣言
use App\Models\Koban;       // データベース操作担当
use App\Utils\View;         // 画面表示担当 (さっき作ったやつ)
use App\Utils\Validator;    // 入力チェック担当

class KobanController
{

    /**
     * 一覧画面を表示する (旧 opendata.php の役割)
     */
    public function index()
    {
        // 1. モデルの呼び出し
        // 「交番データ」を扱うためのクラスをインスタンス化（実体化）します
        $kobanModel = new Koban();

        $log_detail = "トップ表示";

        // ▼▼▼ ログ用詳細情報の構築 ▼▼▼
        $conditions = [];
        if (!empty($_GET['keyword'])) {
            $conditions[] = "KW: " . $_GET['keyword'];
        }
        if (!empty($_GET['search_type'])) {
            $conditions[] = "種別: " . $_GET['search_type'];
        }
        if (!empty($_GET['search_pref'])) {
            $conditions[] = "県: " . $_GET['search_pref'];
        }
        if (!empty($_GET['sort'])) {
            // ソート順も記録しておくと分析に役立ちます
            $conditions[] = "順: " . $_GET['sort'];
        }

        // 条件がなければ「トップ表示」、あれば連結して記録
        $log_detail = empty($conditions) ? "トップ表示" : "検索実行 [" . implode(", ", $conditions) . "]";

        // ログ保存
        logAction($_SESSION['login_id'] ?? 'guest', 'アクセス', $log_detail);
        // ▲▲▲ 構築終了 ▲▲▲

        // 2. データの取得
        // $_GET にはURLのパラメータが入っています (例: ?keyword=新宿&page=2)
        // モデルの search メソッドに「検索条件」を渡して、結果をもらいます
        $limit = 100;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * $limit;

        $data = $kobanModel->search($_GET, $limit, $offset, $_GET['sort'] ?? 'id_asc');
        $total_count = $kobanModel->count($_GET);
        $total_pages = ceil($total_count / $limit);

        // 3. ビューの表示
        // 'home' は views/home.php を指します
        // 第2引数の配列が、home.php 内で変数として使えるようになります
        return View::render('home', [
            'all_data'    => $data,
            'total_count' => $total_count,
            'page'        => $page,
            'total_pages' => $total_pages,
            'pref_list'   => ["北海道", "青森県", "岩手県", "宮城県", "秋田県", "山形県", "福島県", "茨城県", "栃木県", "群馬県", "埼玉県", "千葉県", "東京都", "神奈川県", "新潟県", "富山県", "石川県", "福井県", "山梨県", "長野県", "岐阜県", "静岡県", "愛知県", "三重県", "滋賀県", "京都府", "大阪府", "兵庫県", "奈良県", "和歌山県", "鳥取県", "島根県", "岡山県", "広島県", "山口県", "徳島県", "香川県", "愛媛県", "高知県", "福岡県", "佐賀県", "長崎県", "熊本県", "大分県", "宮崎県", "鹿児島県", "沖縄県"]
        ]);
    }

    /**
     * 新規作成フォームを表示する (旧 koban_form.php の初期表示)
     */
    public function create()
    {
        // 権限チェック (functions.phpの関数)
        if (!hasPermission(PERM_DATA)) {
            die('権限がありません');
        }

        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['message']);

        $phone_parts = ['', '', ''];
        $postal_parts = ['', ''];

        // フォームを表示。新規なのでデータは空っぽです。
        return View::render('koban/form', [
            'page_title' => '新規データ登録',
            'edit_data'  => [],
            'message'    => $message,
            'phone_parts' => $phone_parts,   // ビューへ渡す
            'postal_parts' => $postal_parts, // ビューへ渡す
        ]);
    }

    /**
     * 編集フォームを表示する
     */
    public function edit()
    {
        // 1. 権限チェックとID取得 (変更なし)
        if (!hasPermission(PERM_DATA)) {
            die('権限がありません');
        }

        $id = $_GET['id'] ?? null;
        if (!$id) {
            die('IDが指定されていません');
        }

        $kobanModel = new Koban();
        $data = $kobanModel->findById($id);

        if (!$data) {
            die('データが見つかりません');
        }

        // セッションからメッセージを取得して消去（インポートエラー等の表示用）
        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['message']);


        // ▼▼▼ 修正: 「無し」データを空欄に変換するロジックを追加 ▼▼▼
        $keys_to_clean = ['phone_number', 'postal_code', 'group_code'];
        foreach ($keys_to_clean as $key) {
            if (isset($data[$key]) && $data[$key] === '無し') {
                $data[$key] = '';
            }
        }
        // ▲▲▲ 変換ロジックここまで ▲▲▲

        // 2. フォーム初期値の計算ロジック
        // phone_number と postal_code が空文字列になったため、以下のロジックが正しく動作します

        // 電話番号の分割
        $phone_parts = explode('-', $data['phone_number'] ?? '') + ['', '', ''];

        // 郵便番号の分割
        $p = $data['postal_code'] ?? '';
        $postal_parts = (strpos($p, '-') !== false) ? explode('-', $p) : [substr($p, 0, 3), substr($p, 3)];

        // 3. View にデータを渡して表示 (変更なし)
        return View::render('koban/form', [
            'page_title' => 'データ編集 (ID: ' . $data['id'] . ')',
            'edit_data'  => $data, // 変換済みの$dataを渡す
            'message'    => $message,
            'phone_parts' => $phone_parts,
            'postal_parts' => $postal_parts,
        ]);
    }

    /**
     * データの保存処理を行う (新規・更新 共通)
     */
    public function store()
    {
        // CSRFトークンチェック (セキュリティ対策)
        verifyCsrfToken();

        if (!hasPermission(PERM_DATA)) {
            die('権限がありません');
        }

        // 1. 入力値のチェック (Validatorクラスを利用)
        $validator = new Validator();

        // データの整形（バリデーション前に一部実行）
        $raw_phone_parts = [$_POST['phone_part1'] ?? '', $_POST['phone_part2'] ?? '', $_POST['phone_part3'] ?? ''];
        $raw_postal = ($_POST['postal_part1'] ?? '') . ($_POST['postal_part2'] ?? '');

        if (!$validator->validateKoban($_POST)) {
            // エラーがあった場合、フォーム画面に戻す
            $errors = $validator->getErrors();

            // ★修正: エラー時も電話番号・郵便番号のパーツを渡すことで入力値を保持する
            return View::render('koban/form', [
                'page_title' => '入力エラー',
                'edit_data'  => $_POST,
                'message'    => implode('<br>', $errors),
                // 電話番号パーツはフォームからの入力をそのまま返す
                'phone_parts' => $raw_phone_parts,
                // 郵便番号パーツはフォームからの入力をそのまま返す
                'postal_parts' => [$_POST['postal_part1'] ?? '', $_POST['postal_part2'] ?? '']
            ]);
        }

        // 2. データの整形
        $kobanModel = new Koban();

        $group_code = $_POST['new_group_code'] ?? '';
        // 【修正】数値型カラムには '無し' ではなく null を入れる
        $group_code = (empty($group_code) || $group_code === '無し') ? null : $group_code;

        // 電話番号や郵便番号が文字列型(VARCHAR/TEXT)なら '無し' でも通りますが、
        // 念のため統一して null または空文字を検討してください
        $phone = implode('-', array_filter($raw_phone_parts, fn($v) => $v !== ''));
        $phone = empty($phone) ? null : $phone; // nullを推奨

        $postal = empty($raw_postal) ? null : $raw_postal; // nullを推奨


        // ▼▼▼ 住所結合処理（元のkoban_form.phpにあったロジックを移植） ▼▼▼
        // 全角数字の半角化、丁目/番地などをハイフンに置換
        $addr3 = str_replace(['丁目', '番地', '番', '号'], '-', mb_convert_kana($_POST['new_addr3'] ?? '', 'n', 'UTF-8'));
        // 連続するハイフンを一つにまとめ、末尾のハイフンを削除
        $addr3 = rtrim(preg_replace('/-+/', '-', $addr3), '-');


        // データベース保存用データ
        $saveData = [
            'koban_fullname' => $_POST['new_name'],
            'type'           => $_POST['new_type'],
            'phone_number'   => $phone,         // ★「無し」判定後の値
            'group_code'     => $group_code,
            'postal_code'    => $postal,        // ★「無し」判定後の値
            'pref'           => $_POST['new_pref'],
            'addr3'          => $addr3          // ★整形後の値
        ];

        // 3. DBへの保存
        if (!empty($_POST['update_id'])) {
            // IDがあるなら更新
            $kobanModel->update($_POST['update_id'], $saveData);
            $_SESSION['message'] = "ID: {$_POST['update_id']} を更新しました。";
            logAction($_SESSION['login_id'], 'データ更新', "ID: {$_POST['update_id']} を更新");
        } else {
            // IDがないなら新規作成
            $kobanModel->create($saveData);
            $_SESSION['message'] = "新規データを登録しました！";
            logAction($_SESSION['login_id'], 'データ登録', "新規: {$_POST['new_name']} を追加");
        }

        // 4. 一覧画面へリダイレクト (index.phpへ戻る)
        header("Location: /");
        exit;
    }

    /**
     * 削除処理
     */
    public function delete()
    {
        verifyCsrfToken();
        if (!hasPermission(PERM_DATA)) {
            die('権限がありません');
        }

        $id = $_POST['delete_id'] ?? null;
        if ($id) {
            $kobanModel = new Koban();
            $kobanModel->delete($id);
            $_SESSION['message'] = "ID: {$id} を削除しました。";
            logAction($_SESSION['login_id'], 'データ削除', "ID: {$id} を削除しました。");
        }

        header("Location: /");
        exit;
    }

    /**
     * CSV一括インポート処理
     */
    public function importCsv()
    {
        verifyCsrfToken();
        // 権限チェック：'data' ではなく 'admin'（管理者管理ロール）に変更
        if (!isset($_SESSION['logged_in']) || !hasPermission(PERM_ADMIN)) {
            $_SESSION['message'] = "エラー：CSVインポートには管理者管理権限が必要です。";
            logAction($_SESSION['login_id'] ?? 'guest', '権限拒否', 'CSVインポート試行（権限不足）');
            header("Location: /koban/create");
            exit;
        }

        $kobanModel = new Koban();

        // 処理の本体 (旧koban_form.phpのインポート部分)
        if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == 0) {
            try {
                $handle = fopen($_FILES['csv_file']['tmp_name'], "r");
                if ($handle) {
                    $head = fgets($handle);
                    // 文字コード変換
                    if (mb_detect_encoding($head, ['UTF-8', 'SJIS-win'], true) !== 'UTF-8') {
                        rewind($handle);
                        stream_filter_append($handle, 'convert.iconv.cp932/utf-8');
                    } else {
                        rewind($handle);
                    }

                    fgetcsv($handle); // ヘッダスキップ
                    $rows = [];
                    while (($d = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        if (!isset($d[0]) || !is_numeric($d[0])) continue;
                        $rows[] = $d;
                    }
                    fclose($handle);

                    // Modelで一括登録
                    $kobanModel->bulkInsert($rows);

                    $count = count($rows);
                    $_SESSION['message'] = "インポート完了：{$count}件のデータを処理しました。";
                    logAction($_SESSION['login_id'], 'CSVインポート', "ファイル名: {$_FILES['csv_file']['name']} / 処理件数: {$count}件");
                }
            } catch (\Exception $e) {
                $_SESSION['message'] = "インポートエラー: " . $e->getMessage();
                logAction($_SESSION['login_id'], 'CSVインポート失敗', $e->getMessage());
            }
        } else {
            $_SESSION['message'] = "ファイルがアップロードされていません。";
        }

        // 処理完了後、フォーム画面に戻る
        header("Location: /koban/create");
        exit;
    }


    /**
     * 交番データを移行用カスタムCSVとしてエクスポートする
     */
    public function export()
    {
        // タイムアウト対策
        set_time_limit(0);
        if (ob_get_level()) ob_end_clean();

        try {
            $kobanModel = new \App\Models\Koban();

            // ログ記録
            logAction($_SESSION['login_id'] ?? 'guest', 'CSVエクスポート', '移行用カスタム形式で出力');

            // 全件取得 (ジェネレータを使用)
            $rows = $kobanModel->exportAll($_GET, $_GET['sort'] ?? 'id_asc');

            $filename = "koban_migration_" . date('Ymd_His') . ".csv";

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF"); // Excel対応用BOM

            // 指定された順序とカラム名でヘッダーを出力 (使用していないカラムは除外)
            fputcsv($output, [
                'id',
                'pref',
                'type',
                'koban_fullname',
                'phone_number',
                'postal_code',
                'group_code',
                'addr3'
            ]);

            foreach ($rows as $row) {
                // データベースのカラム名とCSVのヘッダーを正確にマッピング
                fputcsv($output, [
                    $row['id'],
                    $row['pref'],
                    $row['type'],
                    $row['koban_fullname'],
                    $row['phone_number'],
                    $row['postal_code'],
                    $row['group_code'],
                    $row['addr3']
                ]);
            }

            fclose($output);
            exit;
        } catch (\Exception $e) {
            error_log($e->getMessage());
            header("Location: /");
            exit;
        }
    }
}
