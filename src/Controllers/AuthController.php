<?php

namespace App\Controllers;

use App\Models\AdminUser;
use App\Utils\View;

class AuthController
{
    // ログイン画面の表示（もし専用画面を作るならここ。今回はトップページにフォームがあるので使いません）
    public function showLoginForm()
    {
        // トップページへリダイレクト
        header("Location: /");
        exit;
    }

    // ログイン処理 (POST)
    public function login()
    {
        verifyCsrfToken();
        $login_id = $_POST['login_id'] ?? '';
        $password = $_POST['login_pass'] ?? '';

        $adminModel = new AdminUser();
        $max_attempts = 10;

        try {
            $user = $adminModel->findById($login_id);

            if (!$user) {
                // ユーザーが存在しない場合
                $_SESSION['message'] = "IDまたはパスワードが違います。";
                logAction($login_id ?: 'unknown', 'ログイン失敗', "存在しないID: {$login_id}");
                header("Location: /");
                exit;
            }

            // 1. ロック確認
            if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
                $remaining_min = ceil((strtotime($user['locked_until']) - time()) / 60);
                $_SESSION['message'] = "アカウントロック中（残り約 {$remaining_min} 分）";
                header("Location: /");
                exit;
            }

            // 2. パスワード検証
            if (password_verify($password, $user['password_hash'])) {
                // 成功時
                session_regenerate_id(true);
                $_SESSION['logged_in'] = true;
                $_SESSION['login_id'] = $user['login_id'];
                $_SESSION['permissions'] = [
                    'data'  => $user['perm_data'],
                    'admin' => $user['perm_admin'],
                    'log'   => $user['perm_log'] ?? 0
                ];
                $adminModel->resetFailureCount($login_id);
                logAction($login_id, 'ログイン', '成功');
                header("Location: /");
                exit;
            }

            // 3. 失敗時の処理（ここがエラーの温床）
            $adminModel->incrementFailureCount($login_id);
            $updatedUser = $adminModel->findById($login_id);

            // 安全に回数を取得
            $fail_count = $updatedUser ? (int)$updatedUser['failure_count'] : 0;

            if ($fail_count >= $max_attempts) {
                $lock_time = date('Y-m-d H:i:s', strtotime('+30 minutes'));
                $adminModel->lockAccount($login_id, $lock_time);
                $_SESSION['message'] = "連続失敗により30分間ロックされました。";
                logAction($login_id, 'アカウントロック', "{$fail_count}回失敗");
            } else {
                $remaining = $max_attempts - $fail_count;
                $_SESSION['message'] = "IDまたはパスワードが違います。あと {$remaining} 回でロックされます。";
                logAction($login_id, 'ログイン失敗', "失敗回数: {$fail_count}");
            }
        } catch (\Exception $e) {
            // デバッグ用：何のエラーかログに出力する
            error_log("【LOGIN ERROR】" . $e->getMessage());
            $_SESSION['message'] = "システムエラー（DB接続またはスキーマの不備）が発生しました。";
        }

        header("Location: /");
        exit;
    }

    // ログアウト処理
    public function logout()
    {
        $user_id = $_SESSION['login_id'] ?? 'guest';
        logAction($user_id, 'ログアウト', '成功');

        // セッション破棄
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();

        header("Location: /");
        exit;
    }
}
