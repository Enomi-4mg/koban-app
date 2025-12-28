<?php

namespace App\Controllers;

use App\Models\AdminUser;
use App\Models\AuditLog;
use App\Utils\View;

class AdminController
{

    // 共通の権限チェックメソッド
    private function requirePermission($type)
    {
        // 文字列の代わりに定数を受け取るようにし、セッションを確認
        if (!isset($_SESSION['logged_in']) || !hasPermission($type)) {
            $_SESSION['message'] = "権限がありません。";
            header("Location: /");
            exit;
        }
    }

    /**
     * 操作ログ一覧画面 (旧 view_logs.php)
     */
    public function logs()
    {
        $this->requirePermission(PERM_LOG);

        $logModel = new AuditLog();

        // CSVダウンロード処理
        if (isset($_GET['download_csv'])) {
            $this->downloadLogsCsv($logModel);
            return;
        }

        // 画面表示用データ
        $types = $logModel->getActionTypes();
        $logs = $logModel->search($_GET, 500);
        $latest_id = count($logs) > 0 ? $logs[0]['id'] : 0;

        return View::render('admin/log_list', [
            'types' => $types,
            'logs' => $logs,
            'latest_id' => $latest_id
        ]);
    }

    /**
     * ログCSVダウンロード（logsメソッドから呼ばれる）
     */
    private function downloadLogsCsv($logModel)
    {
        $logs = $logModel->search($_GET, 10000);
        $filename = "audit_log_" . date('Ymd_His') . ".csv";

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $output = fopen('php://output', 'w');
        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, ['ID', 'ユーザーID', '日時', '操作種別', '詳細内容', 'IPアドレス', 'UserAgent']);

        foreach ($logs as $row) {
            fputcsv($output, [
                $row['id'],
                $row['user_id'],
                $row['action_time'],
                $row['action_type'],
                $row['details'],
                $row['ip_address'],
                $row['user_agent'] ?? ''
            ]);
        }
        fclose($output);
        exit;
    }

    /**
     * ログAPI (旧 api_get_logs.php)
     * JavaScriptから非同期で呼ばれるJSON返却用メソッド
     */
    public function apiLogs()
    {
        // APIでもしっかり権限チェック
        if (!isset($_SESSION['logged_in']) || !hasPermission(PERM_LOG)) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Forbidden']);
            exit;
        }

        $logModel = new AuditLog();
        $last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;
        $newLogs = $logModel->getNewLogs($last_id, $_GET);

        // XSS対策をしてJSONで返す
        $clean_logs = array_map(function ($log) {
            return [
                'id' => h($log['id']),
                'action_time' => h($log['action_time']),
                'user_id' => h($log['user_id']),
                'action_type' => h($log['action_type']),
                'details' => h($log['details']),
                'ip_address' => h($log['ip_address']),
                'user_agent' => h($log['user_agent'] ?? '')
            ];
        }, $newLogs);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($clean_logs);
        exit;
    }

    /**
     * 自分のパスワード変更画面 (旧 change_password.php)
     */
    public function changePassword()
    {
        if (!isset($_SESSION['logged_in'])) {
            header("Location: /");
            exit;
        }
        return View::render('admin/change_password', ['message' => $_SESSION['message'] ?? '']);
    }

    /**
     * パスワード変更処理 (POST)
     */
    public function updatePassword()
    {
        verifyCsrfToken();
        if (!isset($_SESSION['logged_in'])) header("Location: /");

        $adminModel = new AdminUser();
        $user_id = $_SESSION['login_id'];

        try {
            $user = $adminModel->findById($user_id);
            if ($user && password_verify($_POST['current_pass'], $user['password_hash'])) {
                $new_hash = password_hash($_POST['new_pass'], PASSWORD_DEFAULT);
                $adminModel->updatePassword($user_id, $new_hash);

                $_SESSION['message'] = "パスワードを変更しました！";
                logAction($user_id, 'パスワード変更', '成功');
            } else {
                $_SESSION['message'] = "現在のパスワードが間違っています。";
                logAction($user_id, 'パスワード変更失敗', '入力ミス');
            }
        } catch (\Exception $e) {
            $_SESSION['message'] = "エラー: " . $e->getMessage();
        }

        // フォームへリダイレクト
        header("Location: /admin/password/change");
        exit;
    }

    /**
     * 管理者一覧・登録・削除画面 (旧 register_admin.php)
     */
    public function admins()
    {
        $this->requirePermission(PERM_ADMIN);

        $adminModel = new AdminUser();

        $all_admins = $adminModel->findAll();

        // ▼▼▼ 追加：特権アカウントの隠蔽ロジック ▼▼▼
        $current_user = $_SESSION['login_id'];

        $filtered_list = array_filter($all_admins, function ($admin) use ($current_user) {
            // 1. 自分が特権アカウントなら、すべて（自分を含む）を表示
            if (isProtectedAdmin($current_user)) {
                return true;
            }
            // 2. 自分以外が閲覧している場合、特権アカウント（SUPER_ADMIN_ID）を除外
            return !isProtectedAdmin($admin['login_id']);
        });
        // ▲▲▲ 追加ここまで ▲▲▲

        return View::render('admin/register', [
            'admin_list' => $filtered_list,
        ]);
    }

    /**
     * 管理者保存・削除処理 (POST)
     */
    public function storeAdmin()
    {
        $this->requirePermission(PERM_ADMIN);
        verifyCsrfToken();

        $adminModel = new AdminUser();

        // 削除処理
        if (isset($_POST['delete_admin_id'])) {
            if (!isCurrentSuperAdmin()) {
                $_SESSION['message'] = "エラー：管理者の削除権限は最高管理者に限定されています。";
                header("Location: /admin/users");
                exit;
            }
            $target = $_POST['delete_admin_id'];

            // 特権アカウントの削除をブロック
            if (isProtectedAdmin($target)) {
                $_SESSION['message'] = "エラー：システム管理者は削除できません。";
            } elseif ($_POST['delete_admin_id'] === $_SESSION['login_id']) {
                $_SESSION['message'] = "エラー：自分自身は削除できません。";
            } else {
                $adminModel->delete($_POST['delete_admin_id']);
                $_SESSION['message'] = "管理者ID: {$_POST['delete_admin_id']} を削除しました。";
                logAction($_SESSION['login_id'], '管理者削除', "対象: {$_POST['delete_admin_id']}");
            }
        }

        // 新規登録処理
        if (isset($_POST['new_admin_id'])) {
            if ($adminModel->exists($_POST['new_admin_id'])) {
                $_SESSION['message'] = "エラー：そのIDは既に登録されています。";
            } else {
                $perms = [
                    'data'  => isset($_POST['perm_data']) ? 1 : 0,
                    'admin' => isset($_POST['perm_admin']) ? 1 : 0,
                    'log'   => isset($_POST['perm_log']) ? 1 : 0
                ];
                $adminModel->create($_POST['new_admin_id'], $_POST['new_admin_pass'], $perms);
                $_SESSION['message'] = "管理者「{$_POST['new_admin_id']}」を登録しました。";
                logAction($_SESSION['login_id'], '管理者登録', "新規: {$_POST['new_admin_id']}");
            }
        }

        header("Location: /admin/users");
        exit;
    }
    /**
     * 新規ユーザー登録画面の表示
     */
    public function showRegisterForm()
    {
        // ログインチェックと最高管理者権限の確認
        if (!isCurrentSuperAdmin()) {
            $_SESSION['message'] = "エラー：ユーザー登録は最高管理者のみ許可されています。";
            header("Location: /admin/users");
            exit;
        }

        return View::render('admin/user_registration', [
            'page_title' => '新規管理者登録',
            'message' => getFlashMessage()
        ]);
    }

    /**
     * ユーザー登録処理の実行
     */
    public function registerUser()
    {
        verifyCsrfToken(); // CSRF対策

        if (!isCurrentSuperAdmin()) {
            die("Unauthorized");
        }

        $login_id = $_POST['new_id'] ?? '';
        $password = $_POST['new_pass'] ?? '';

        // バリデーション (Validator.phpを拡張して使うのが理想的です)
        if (strlen($login_id) < 4 || strlen($password) < 8) {
            $_SESSION['message'] = "エラー：IDは4文字以上、パスワードは8文字以上必要です。";
            header("Location: /admin/users/create");
            exit;
        }

        $adminModel = new \App\Models\AdminUser();

        // 重複チェック
        if ($adminModel->exists($login_id)) {
            $_SESSION['message'] = "エラー：そのIDは既に使用されています。";
            header("Location: /admin/users/create");
            exit;
        }

        // 権限設定の取得
        $perms = [
            'data'  => isset($_POST['perm_data']) ? 1 : 0,
            'admin' => isset($_POST['perm_admin']) ? 1 : 0,
            'log'   => isset($_POST['perm_log']) ? 1 : 0
        ];

        // 保存実行
        if ($adminModel->create($login_id, $password, $perms)) {
            logAction($_SESSION['login_id'], '管理者登録', "新規ユーザー: $login_id");
            $_SESSION['message'] = "管理者「{$login_id}」を正常に登録しました。";
            header("Location: /admin/users");
        } else {
            $_SESSION['message'] = "エラー：登録に失敗しました。";
            header("Location: /admin/users/create");
        }
        exit;
    }
    /**
     * 管理者一覧をCSVエクスポートする
     */
    public function exportAdmins()
    {
        // 「admin」権限（アカウント管理）があるかチェックします
        $this->requirePermission(PERM_ADMIN);

        // Supabase移行に必要なすべてのカラムを直接取得します
        $db = \App\Utils\Database::connect(); //
        $sql = "SELECT login_id, password_hash, role, failure_count, locked_until, perm_data, perm_admin, perm_log FROM admin_users";
        $stmt = $db->query($sql);
        $admins = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $filename = "admin_users_migration_" . date('Ymd_His') . ".csv";

        // 出力バッファをクリアし、余計な文字が混入するのを防ぎます
        if (ob_get_level()) ob_end_clean();

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        // Excelで開いても文字化けしないようBOMを付与します
        fwrite($output, "\xEF\xBB\xBF");

        // 指定されたヘッダーを出力します
        fputcsv($output, ['login_id', 'password_hash', 'role', 'failure_count', 'locked_until', 'perm_data', 'perm_admin', 'perm_log']);

        foreach ($admins as $row) {
            fputcsv($output, [
                $row['login_id'],
                $row['password_hash'],
                $row['role'] ?? 1,
                $row['failure_count'] ?? 0,
                $row['locked_until'] ?? '',
                $row['perm_data'] ?? 0,
                $row['perm_admin'] ?? 0,
                $row['perm_log'] ?? 0
            ]);
        }
        fclose($output);
        exit; // ビューを表示せずに終了します
    }
    /**
     * パスワード強制リセット画面 (旧 reset_admin_pass.php)
     */
    public function resetPasswordForm()
    {
        if (!isCurrentSuperAdmin()) {
            $_SESSION['message'] = "エラー：この操作には最高管理者権限が必要です。";
            header("Location: /admin/users");
            exit;
        }
        $this->requirePermission(PERM_ADMIN);
        return View::render('admin/reset_password', [
            'target_id' => $_GET['id'] ?? '',
            'message' => $_SESSION['message'] ?? ''
        ]);
    }

    /**
     * パスワード強制リセット処理 (POST)
     */
    public function resetPasswordExec()
    {
        $this->requirePermission(PERM_ADMIN);
        verifyCsrfToken();

        $adminModel = new AdminUser();
        $target = $_POST['reset_target_id'];

        if (!$adminModel->exists($target)) {
            $_SESSION['message'] = "エラー：ID「{$target}」は見つかりません。";
        } else {
            $hash = password_hash($_POST['reset_new_pass'], PASSWORD_DEFAULT);
            $adminModel->updatePassword($target, $hash);
            $_SESSION['message'] = "管理者「{$target}」のパスワードをリセットしました。";
            logAction($_SESSION['login_id'], 'PWリセット', "対象: $target");
        }

        header("Location: /admin/password/reset"); // 画面更新
        exit;
    }

    /**
     * 管理者詳細・権限編集画面の表示
     */
    public function editAdmin()
    {
        $this->requirePermission(PERM_ADMIN);

        $login_id = $_GET['id'] ?? '';
        $adminModel = new AdminUser();
        $targetAdmin = $adminModel->findById($login_id); //

        if (!$targetAdmin) {
            $_SESSION['message'] = "エラー：対象の管理者が見つかりません。";
            header("Location: /admin/users");
            exit;
        }

        return View::render('admin/edit_admin', [
            'admin' => $targetAdmin,
            'message' => $_SESSION['message'] ?? ''
        ]);
    }

    /**
     * 管理者権限の更新実行 (POST)
     */
    public function updateAdminPerms()
    {
        $this->requirePermission(PERM_ADMIN);
        verifyCsrfToken();
        $login_id = $_POST['target_admin_id'] ?? '';

        // 特権アカウントの権限変更をブロック
        if (isProtectedAdmin($login_id)) {
            $_SESSION['message'] = "エラー：システム管理者の権限は変更できません。";
            header("Location: /admin/users");
            exit;
        }

        $perms = [
            'data'  => isset($_POST['perm_data']) ? 1 : 0,
            'admin' => isset($_POST['perm_admin']) ? 1 : 0,
            'log'   => isset($_POST['perm_log']) ? 1 : 0
        ];

        $adminModel = new AdminUser();
        if ($adminModel->updatePermissions($login_id, $perms)) {
            $_SESSION['message'] = "管理者「{$login_id}」の権限を更新しました。";
            logAction($_SESSION['login_id'], '権限変更', "対象: {$login_id} / Data:{$perms['data']}, Admin:{$perms['admin']}, Log:{$perms['log']}"); //
        } else {
            $_SESSION['message'] = "エラー：更新に失敗しました。";
        }

        header("Location: /admin/users");
        exit;
    }
}
