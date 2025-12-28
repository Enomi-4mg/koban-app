<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container" style="max-width: 500px; margin-top: 40px;">

    <div style="margin-bottom: 20px;">
        <a href="/admin/users" style="color: #888; text-decoration: none; font-size: 0.9em;">← 管理者一覧へ戻る</a>
    </div>

    <div class="box" style="border: 2px solid #1eff1a; padding: 30px; background-color: #000; box-shadow: 0 0 20px rgba(30, 255, 26, 0.1);">
        <h2 style="color: #1eff1a; border-bottom: 1px solid #1eff1a; padding-bottom: 10px; text-align: center; margin-top: 0;">新規管理者アカウント発行</h2>

        <form method="post" action="/admin/users/register">
            <input type="hidden" name="csrf_token" value="<?php echo h($_SESSION['csrf_token']); ?>">

            <div style="margin-bottom: 20px;">
                <label style="color: #1eff1a; display: block; margin-bottom: 5px;">ログインID</label>
                <input type="text" name="new_id" required
                    style="width: 100%; background: #000; border: 1px solid #333; color: #1eff1a; padding: 10px; font-family: monospace;"
                    placeholder="IDを入力...">
            </div>

            <div style="margin-bottom: 25px;">
                <label style="color: #1eff1a; display: block; margin-bottom: 5px;">初期パスワード</label>
                <input type="password" name="new_pass" required
                    style="width: 100%; background: #000; border: 1px solid #333; color: #1eff1a; padding: 10px; font-family: monospace;"
                    placeholder="PASSWORDを入力...">
            </div>

            <h3 style="color: #ffff00; font-size: 0.85em; margin-bottom: 10px;">初期権限の設定</h3>
            <div style="display: grid; gap: 8px; margin-bottom: 30px;">
                <label class="cyber-checkbox-label">
                    <input type="checkbox" name="perm_data" value="1" checked>
                    <span class="cyber-panel">● データ管理権限</span>
                </label>
                <label class="cyber-checkbox-label">
                    <input type="checkbox" name="perm_admin" value="1">
                    <span class="cyber-panel">● 管理者管理権限</span>
                </label>
                <label class="cyber-checkbox-label">
                    <input type="checkbox" name="perm_log" value="1">
                    <span class="cyber-panel">● ログ閲覧権限</span>
                </label>
            </div>

            <div style="text-align: center;">
                <input type="submit" value="システムに登録実行" class="btn-primary"
                    style="width: 100%; padding: 12px; font-weight: bold; font-size: 1.1em; cursor: pointer;">
            </div>
        </form>
    </div>
</div>