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
        $message = "";

        try {
            // ユーザー取得
            $user = $adminModel->findById($login_id);

            // パスワード照合
            if ($user && password_verify($password, $user['password_hash'])) {
                // セッションID再生成（セッションハイジャック対策）
                session_regenerate_id(true);

                // セッション保存
                $_SESSION['logged_in'] = true;
                $_SESSION['login_id'] = $user['login_id'];
                $_SESSION['permissions'] = [
                    'data'  => $user['perm_data'],
                    'admin' => $user['perm_admin'],
                    'log'   => $user['perm_log'] ?? 0
                ];
                $_SESSION['role'] = $user['role'];

                // 失敗回数リセット
                $adminModel->resetFailureCount($login_id);

                // ログ記録 (functions.phpの関数)
                logAction($login_id, 'ログイン', '成功');

                // 成功したらトップへ
                header("Location: /");
                exit;
            } else {
                // ログイン失敗
                logAction($login_id ?: 'unknown', 'ログイン失敗', "試行ID: {$login_id} (パスワード不一致または存在しないID)");

                // エラーメッセージをセッションに入れてトップへ戻す
                $_SESSION['message'] = "IDまたはパスワードが違います。";
                header("Location: /");
                exit;
            }
        } catch (\Exception $e) {
            // システムエラー
            $_SESSION['message'] = "システムエラーが発生しました。";
            error_log($e->getMessage());
            header("Location: /");
            exit;
        }
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
