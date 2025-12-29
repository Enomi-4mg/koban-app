<?php require __DIR__ . '/../layouts/header.php'; ?>
<link rel="stylesheet" href="../../public/css/style-auth.css">

<div class="container" style="max-width: 450px; margin-top: 40px;">
    <div class="box auth-box">
        <div class="scan-line"></div>

        <h2 class="auth-title">新規アカウント作成</h2>

        <p style="color: #888; font-size: 0.8em; text-align: center; margin-bottom: 25px;">
            システムを利用するためのIDとパスワードを設定してください。
        </p>

        <form method="post" action="/register" style="display: flex; flex-direction: column; gap: 20px; text-align: left;">
            <input type="hidden" name="csrf_token" value="<?php echo h($_SESSION['csrf_token']); ?>">

            <div>
                <label style="color: var(--cyber-green); display: block; margin-bottom: 8px; font-size: 0.9em;">ログインID</label>
                <input type="text" name="login_id" required class="auth-input"
                    style="width: 100%; background: #000; border: 1px solid #333; color: var(--cyber-green); padding: 12px; font-family: monospace; box-sizing: border-box;"
                    placeholder="4-20文字の半角英数字">
            </div>

            <div>
                <label style="color: var(--cyber-green); display: block; margin-bottom: 8px; font-size: 0.9em;">パスワード</label>
                <input type="password" name="password" required class="auth-input"
                    style="width: 100%; background: #000; border: 1px solid #333; color: var(--cyber-green); padding: 12px; font-family: monospace; box-sizing: border-box;"
                    placeholder="8文字以上 (英大・小・数を含む)">
            </div>

            <div style="background: rgba(30, 255, 26, 0.05); border-left: 3px solid var(--cyber-yellow); padding: 10px; margin: 10px 0 20px 0;">
                <p style="color: var(--cyber-yellow); font-size: 0.75em; margin: 0; line-height: 1.4;">
                    ※登録直後の権限は「閲覧のみ」に設定されます。データの編集が必要な場合は、ログイン後に管理者に申請してください。
                </p>
            </div>

            <?php \App\Utils\View::component('button', [
                'type'    => 'submit',
                'variant' => 'primary',
                'text'    => '[ アカウントを作成する ]',
                'style'   => 'width: 100%; padding: 15px; font-size: 1.1em;'
            ]); ?>
        </form>
        <div style="margin-top: 25px; text-align: center; border-top: 1px solid #333; padding-top: 15px;">
            <p style="color: #666; font-size: 0.8em; margin-bottom: 10px;">アカウントをお持ちの場合</p>
            <a href="/auth/login" style="color: var(--cyber-green); text-decoration: underline; font-size: 0.9em;">ログインページ</a>
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