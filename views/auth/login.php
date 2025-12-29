<?php require __DIR__ . '/../layouts/header.php'; ?>
<link rel="stylesheet" href="../../public/css/style-auth.css">

<div class="container" style="max-width: 450px; margin-top: 60px;">
    <div class="box auth-box">
        <div class="scan-line"></div>

        <h2 class="auth-title">システム認証</h2>

        <?php if ($message): ?>
            <div style="color: var(--cyber-red); border: 1px solid var(--cyber-red); padding: 10px; margin-bottom: 20px; font-size: 0.9em; background: rgba(255, 68, 68, 0.1); text-align: left;">
                [!] <?php echo h($message); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="/auth/login" style="display: flex; flex-direction: column; gap: 20px; text-align: left;">
            <input type="hidden" name="csrf_token" value="<?php echo h($_SESSION['csrf_token']); ?>">

            <div>
                <label style="color: var(--cyber-green); display: block; margin-bottom: 8px; font-size: 0.9em;">ログインID</label>
                <input type="text" name="login_id" required class="auth-input"
                    style="width: 100%; padding: 12px; background: #000; border: 1px solid #333; color: var(--cyber-green); box-sizing: border-box; font-family: monospace;"
                    placeholder="IDを入力してください">
            </div>

            <div>
                <label style="color: var(--cyber-green); display: block; margin-bottom: 8px; font-size: 0.9em;">パスワード</label>
                <input type="password" name="login_pass" required class="auth-input"
                    style="width: 100%; padding: 12px; background: #000; border: 1px solid #333; color: var(--cyber-green); box-sizing: border-box; font-family: monospace;"
                    placeholder="パスワードを入力してください">
            </div>

            <?php \App\Utils\View::component('button', [
                'type' => 'submit',
                'variant' => 'primary',
                'text' => '[ システムにログイン ]',
                'style' => 'width: 100%; padding: 15px; font-size: 1.1em;'
            ]); ?>
        </form>

        <div style="margin-top: 25px; text-align: center; border-top: 1px solid #333; padding-top: 15px;">
            <p style="color: #666; font-size: 0.8em; margin-bottom: 10px;">アカウントをお持ちでない場合</p>
            <a href="/register" style="color: var(--cyber-green); text-decoration: underline; font-size: 0.9em;">新規アカウント作成</a>
        </div>
    </div>
    <div style="text-align: center; margin-bottom: 15px;">
        <?php \App\Utils\View::component('button', [
            'type'    => 'link',
            'variant' => 'secondary',
            'text'    => '← 検索画面に戻る',
            'href'    => '/'
        ]); ?>
    </div>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>