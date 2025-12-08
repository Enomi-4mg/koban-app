<?php
// 1. オートロードの読み込み
require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\KobanController;
use App\Controllers\AuthController; // 後で作る予定ならコメントアウトでもOK
use App\Controllers\AdminController; // まだない場合はコメントアウト

// 2. 環境変数の読み込み (.env)
try {
    // プロジェクトルート(一つ上の階層)の .env を探す
    $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
} catch (\Exception $e) {
    // .envがなくても動作を止めない（本番環境の環境変数を使う場合など）
}

// 3. セッション開始 (全ページ共通)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

sendSecurityHeaders();

// 4. ルーティング処理 (修正版)
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// スクリプトが置かれているディレクトリを取得 (例: /public)
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);

// リクエストパスの先頭にディレクトリパスが含まれていれば削除する
// これにより /public/koban/edit へのアクセスを /koban/edit として扱えます
if ($scriptDir !== '/' && strpos($requestPath, $scriptDir) === 0) {
    $requestPath = substr($requestPath, strlen($scriptDir));
}

// さらに /index.php が付いていたら削除する
$path = str_replace('/index.php', '', $requestPath);

// 空文字になった場合はルート(/)とみなす
if ($path === '') {
    $path = '/';
}

// 確認用デバッグ (解決したら消してください)
// echo "Resolved Path: " . htmlspecialchars($path);

// ルート定義
$routes = [
    'GET' => [
        '/'             => [KobanController::class, 'index'],
        '/opendata'     => [KobanController::class, 'index'],
        '/koban/create' => [KobanController::class, 'create'],
        '/koban/edit'   => [KobanController::class, 'edit'],
        '/admin/login'  => [AuthController::class, 'showLoginForm'],
        '/koban/export' => [KobanController::class, 'export'],

        // ▼▼▼ 管理者用ルート (追加) ▼▼▼
        '/admin/logs'            => [AdminController::class, 'logs'],            // ログ一覧
        '/admin/api/logs'        => [AdminController::class, 'apiLogs'],         // ログAPI (JS用)
        '/admin/password/change' => [AdminController::class, 'changePassword'],  // PW変更画面
        '/admin/users'           => [AdminController::class, 'admins'],          // 管理者一覧
        '/admin/password/reset'  => [AdminController::class, 'resetPasswordForm'] // PW強制リセット
    ],
    'POST' => [
        '/koban/store'  => [KobanController::class, 'store'],
        '/koban/delete' => [KobanController::class, 'delete'],
        '/auth/login'   => [AuthController::class, 'login'],
        '/auth/logout'  => [AuthController::class, 'logout'],
        '/koban/import' => [KobanController::class, 'importCsv'],

        // ▼▼▼ 管理者用ルート (追加) ▼▼▼
        '/admin/password/update' => [AdminController::class, 'updatePassword'],  // PW変更実行
        '/admin/users/store'     => [AdminController::class, 'storeAdmin'],      // 管理者登録・削除
        '/admin/password/reset'  => [AdminController::class, 'resetPasswordExec'], // PW強制リセット実行
    ]
];

// 5. ディスパッチ (コントローラー呼び出し)
if (isset($routes[$method][$path])) {
    // 対応するコントローラーとメソッドを取り出す
    [$controllerClass, $action] = $routes[$method][$path];

    // クラスが存在するかチェック
    if (class_exists($controllerClass)) {
        $controller = new $controllerClass();
        if (method_exists($controller, $action)) {
            // メソッド実行！
            $controller->$action();
        } else {
            http_response_code(500);
            echo "Error: Method '$action' not found in $controllerClass";
        }
    } else {
        http_response_code(500);
        echo "Error: Class '$controllerClass' not found";
    }
} else {
    // 定義されていないURLの場合
    http_response_code(404);
    echo "<h1>404 Not Found</h1>";
    echo "<p>ページが見つかりません。</p>";
    echo "<a href='/'>トップページへ戻る</a>";
}
