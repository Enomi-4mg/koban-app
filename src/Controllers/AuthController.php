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
        verifyCsrfToken(); // CSRF対策

        $login_id = $_POST['login_id'] ?? '';
        $password = $_POST['login_pass'] ?? '';

        $adminModel = new AdminUser();
        $max_attempts = 10; // 最大試行回数

        try {
            $user = $adminModel->findById($login_id);

            if ($user) {
                // 1. ロック状態の確認
                if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
                    $remaining_sec = strtotime($user['locked_until']) - time();
                    $remaining_min = ceil($remaining_sec / 60);
                    $_SESSION['message'] = "アカウントがロックされています。あと約 {$remaining_min} 分お待ちください。";
                    header("Location: /");
                    exit;
                }

                // 2. パスワード照合
                if (password_verify($password, $user['password_hash'])) {
                    // ログイン成功処理
                    session_regenerate_id(true);
                    $_SESSION['logged_in'] = true;
                    $_SESSION['login_id'] = $user['login_id'];
                    $_SESSION['permissions'] = [
                        'data'  => $user['perm_data'],
                        'admin' => $user['perm_admin'],
                        'log'   => $user['perm_log'] ?? 0
                    ];

                    $adminModel->resetFailureCount($login_id); // 失敗回数をリセット
                    logAction($login_id, 'ログイン', '成功');
                    
                    header("Location: /");
                    exit;
                } else {
                    // 3. ログイン失敗処理
                    $adminModel->incrementFailureCount($login_id);
                    // 最新の失敗回数を取得するため再取得
                    $updatedUser = $adminModel->findById($login_id);
                    $fail_count = $updatedUser['failure_count'];
                    
                    if ($fail_count >= $max_attempts) {
                        $lock_time = date('Y-m-d H:i:s', strtotime('+30 minutes'));
                        $adminModel->lockAccount($login_id, $lock_time);
                        $_SESSION['message'] = "失敗回数が上限に達しました。セキュリティのため30分間ロックします。";
                        logAction($login_id, 'アカウントロック', "連続{$max_attempts}回失敗によるロック実行");
                    } else {
                        $remaining = $max_attempts - $fail_count;
                        $_SESSION['message'] = "IDまたはパスワードが違います。あと {$remaining} 回でアカウントがロックされます。";
                        logAction($login_id ?: 'unknown', 'ログイン失敗', "試行ID: {$login_id} (失敗回数: {$fail_count})");
                    }
                }
            } else {
                $_SESSION['message'] = "IDまたはパスワードが違います。";
                header("Location: /");
                logAction($login_id ?: 'unknown', 'ID・PW不一致', "試行ID: {$login_id} : {$_SESSION['message']}");
                exit;
            }
        } catch (\Exception $e) {
            $_SESSION['message'] = "システムエラーが発生しました。";
            error_log($e->getMessage());
            header("Location: /");
            logAction($login_id ?: 'unknown', 'ログインエラー', "試行ID: {$login_id} : {$_SESSION['message']}");
            exit;
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
