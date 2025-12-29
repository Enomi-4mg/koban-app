<?php

namespace App\Controllers;

use App\Models\AdminUser;
use App\Utils\View;

class AuthController
{
    // ログイン画面の表示（もし専用画面を作るならここ。今回はトップページにフォームがあるので使いません）

    public function login()
    {
        verifyCsrfToken();
        $login_id = $_POST['login_id'] ?? '';
        $password = $_POST['login_pass'] ?? '';
        $adminModel = new AdminUser();

        // ユーザーに返す共通エラーメッセージ
        $common_error = "IDまたはパスワードが正しくありません。";

        try {
            $user = $adminModel->findById($login_id);

            // IDが存在しない場合でも、わざと「パスワード検証」に相当する時間待機させるか、
            // すぐに返さず一律のメッセージをセットする
            if (!$user) {
                $_SESSION['message'] = $common_error;
                logAction($login_id ?: 'unknown', 'ログイン失敗', "存在しないIDでの試行");
                header("Location: /");
                exit;
            }

            // 1. ロック確認（ロックされていても、メッセージは変えない）
            if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
                $_SESSION['message'] = $common_error; // 「ロック中」とは教えない
                logAction($login_id, 'ログイン拒否', "ロック中のIDへのアクセス");
                header("Location: /");
                exit;
            }

            // 2. パスワード検証
            if (password_verify($password, $user['password_hash'])) {
                // 成功時の処理（既存通り）
                session_regenerate_id(true);
                $_SESSION['logged_in'] = true;
                $_SESSION['login_id'] = $user['login_id'];
                $_SESSION['request_status'] = $user['request_status'] ?? null;
                $_SESSION['permissions'] = [
                    'data'  => $user['perm_data'],
                    'admin' => $user['perm_admin'],
                    'log'   => $user['perm_log'] ?? 0
                ]; // DBに保存されている申請ステータスをセッションに同期
                $adminModel->resetFailureCount($login_id);
                logAction($login_id, 'ログイン', '成功');
                header("Location: /");
                exit;
            }

            // 3. 失敗時：回数カウントやロック処理は裏で行う
            $adminModel->incrementFailureCount($login_id);
            $updatedUser = $adminModel->findById($login_id);
            $fail_count = $updatedUser ? (int)$updatedUser['failure_count'] : 0;

            if ($fail_count >= 10) {
                $lock_time = date('Y-m-d H:i:s', strtotime('+30 minutes'));
                $adminModel->lockAccount($login_id, $lock_time);
                logAction($login_id, 'アカウントロック', "自動ロック実行");
            }

            $_SESSION['message'] = $common_error; // 失敗回数も教えない
            logAction($login_id, 'ログイン失敗', "パスワード不一致（累計: {$fail_count}回）");
        } catch (\Exception $e) {
            error_log($e->getMessage());
            $_SESSION['message'] = "システムエラーが発生しました。";
            logAction($login_id, 'ログインエラー', "ログインにてエラーが発生しました");
        }

        header("Location: /auth/login");
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

    /**
     * 新規登録画面の表示
     */
    public function showRegisterForm()
    {
        // ログイン済みの場合はトップへ
        if (isset($_SESSION['logged_in'])) {
            header("Location: /");
            exit;
        }

        return \App\Utils\View::render('auth/register', [
            'page_title' => 'SIGN UP - 新規アカウント作成',
            'message' => getFlashMessage()
        ]);
    }

    /**
     * 登録処理の実行
     */
    public function register()
    {
        verifyCsrfToken();

        $login_id = $_POST['login_id'] ?? '';
        $password = $_POST['password'] ?? '';

        // 1. バリデーション (Validatorクラスの再利用)
        list($idValid, $idMsg) = \App\Utils\Validator::validateLoginId($login_id);
        if (!$idValid) {
            $_SESSION['message'] = $idMsg;
            header("Location: /register");
            exit;
        }

        list($pwValid, $pwMsg) = \App\Utils\Validator::validatePassword($password);
        if (!$pwValid) {
            $_SESSION['message'] = $pwMsg;
            header("Location: /register");
            exit;
        }

        $adminModel = new AdminUser();

        // 2. ID重複チェック
        if ($adminModel->exists($login_id)) {
            $_SESSION['message'] = "このIDは既に使用されています。";
            header("Location: /register");
            exit;
        }

        // 3. 保存 (一般ユーザーは全権限 0 で登録)
        $perms = [
            'data'  => 0,
            'admin' => 0,
            'log'   => 0
        ];

        if ($adminModel->create($login_id, $password, $perms)) {
            logAction($login_id, '自己登録', "新規アカウント作成（一般ユーザー）");
            $_SESSION['message'] = "アカウントを作成しました。ログインしてください。";
            header("Location: /"); // トップページのログインフォームへ
        } else {
            $_SESSION['message'] = "登録中にエラーが発生しました。";
            header("Location: /register");
        }
        exit;
    }

    /**
     * ログイン専用画面の表示
     */
    public function showLoginForm()
    {
        if (isset($_SESSION['logged_in'])) {
            header("Location: /");
            exit;
        }

        return \App\Utils\View::render('auth/login', [
            'page_title' => 'ACCESS GRANTED - ログイン',
            'message' => getFlashMessage() // functions.php の関数
        ]);
    }

    /**
     * 権限昇格の申請を実行
     */
    public function submitRequest()
    {
        verifyCsrfToken();
        if (!isset($_SESSION['logged_in'])) header("Location: /");

        $userId = $_SESSION['login_id'];
        $message = $_POST['request_reason'] ?? '';

        $db = \App\Utils\Database::connect();
        $stmt = $db->prepare("UPDATE admin_users SET request_status = 'pending', request_message = ?, requested_at = ? WHERE login_id = ?");
        $stmt->execute([$message, date('Y-m-d H:i:s'), $userId]);

        $_SESSION['request_status'] = 'pending';

        logAction($userId, '権限申請', "理由: $message");
        $_SESSION['message'] = "権限昇格を申請しました。管理者の承認をお待ちください。";
        header("Location: /");
        exit;
    }
}
