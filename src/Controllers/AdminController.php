<?php
namespace App\Controllers;

use App\Models\AdminUser;
use App\Models\AuditLog;
use App\Utils\View;

class AdminController {

    // 共通の権限チェックメソッド
    private function requirePermission($type) {
        if (!isset($_SESSION['logged_in']) || !hasPermission($type)) {
            // 権限がない場合はトップへ強制送還（あるいは403画面）
            $_SESSION['message'] = "権限がありません。";
            header("Location: /");
            exit;
        }
    }

    /**
     * 操作ログ一覧画面 (旧 view_logs.php)
     */
    public function logs() {
        $this->requirePermission('log');

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
    private function downloadLogsCsv($logModel) {
        $logs = $logModel->search($_GET, 10000);
        $filename = "audit_log_" . date('Ymd_His') . ".csv";
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $output = fopen('php://output', 'w');
        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, ['ID', 'ユーザーID', '日時', '操作種別', '詳細内容', 'IPアドレス', 'UserAgent']);

        foreach ($logs as $row) {
            fputcsv($output, [
                $row['id'], $row['user_id'], $row['action_time'], $row['action_type'],
                $row['details'], $row['ip_address'], $row['user_agent'] ?? ''
            ]);
        }
        fclose($output);
        exit;
    }

    /**
     * ログAPI (旧 api_get_logs.php)
     * JavaScriptから非同期で呼ばれるJSON返却用メソッド
     */
    public function apiLogs() {
        // APIでもしっかり権限チェック
        if (!isset($_SESSION['logged_in']) || !hasPermission('log')) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Forbidden']);
            exit;
        }

        $logModel = new AuditLog();
        $last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;
        $newLogs = $logModel->getNewLogs($last_id, $_GET);

        // XSS対策をしてJSONで返す
        $clean_logs = array_map(function($log) {
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
    public function changePassword() {
        if (!isset($_SESSION['logged_in'])) {
            header("Location: /");
            exit;
        }
        return View::render('admin/change_password', ['message' => $_SESSION['message'] ?? '']);
    }

    /**
     * パスワード変更処理 (POST)
     */
    public function updatePassword() {
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
    public function admins() {
        $this->requirePermission('admin');
        
        $adminModel = new AdminUser();
        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['message']); // 一度表示したら消す

        $admin_list = $adminModel->findAll();

        return View::render('admin/register', [
            'admin_list' => $admin_list,
            'message' => $message
        ]);
    }

    /**
     * 管理者保存・削除処理 (POST)
     */
    public function storeAdmin() {
        $this->requirePermission('admin');
        verifyCsrfToken();

        $adminModel = new AdminUser();

        // 削除処理
        if (isset($_POST['delete_admin_id'])) {
            if ($_POST['delete_admin_id'] === $_SESSION['login_id']) {
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
     * パスワード強制リセット画面 (旧 reset_admin_pass.php)
     */
    public function resetPasswordForm() {
        $this->requirePermission('admin');
        return View::render('admin/reset_password', [
            'target_id' => $_GET['id'] ?? '',
            'message' => $_SESSION['message'] ?? ''
        ]);
    }

    /**
     * パスワード強制リセット処理 (POST)
     */
    public function resetPasswordExec() {
        $this->requirePermission('admin');
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
}