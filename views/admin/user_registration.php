<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container" style="max-width: 600px; margin-top: 50px;">
    <div style="margin-bottom: 20px;">
        <a href="/admin/users" class="btn btn-secondary">← 管理者一覧に戻る</a>
    </div>

    <div class="box">
        <h2 style="border-bottom: 1px solid #444; padding-bottom: 10px;">新規管理者アカウント発行</h2>

        <form method="post" action="/admin/users/register">
            <input type="hidden" name="csrf_token" value="<?php echo h($_SESSION['csrf_token']); ?>">

            <div style="margin-bottom: 15px;">
                <label>ログインID <span style="color: #ff4444;">*</span></label><br>
                <input type="text" name="new_id" required style="width: 100%;" placeholder="4文字以上の英数字">
            </div>

            <div style="margin-bottom: 15px;">
                <label>初期パスワード <span style="color: #ff4444;">*</span></label><br>
                <input type="password" name="new_pass" required style="width: 100%;" placeholder="8文字以上の強力なパスワード">
            </div>

            <div style="background: #222; padding: 15px; border-radius: 5px; border: 1px solid #444; margin-bottom: 20px;">
                <p style="margin-top: 0; font-weight: bold; color: #fff;">権限の割り当て</p>

                <label style="display: block; margin-bottom: 10px; cursor: pointer;">
                    <input type="checkbox" name="perm_data" value="1" checked>
                    <span style="color: #ffff00;">データ管理権限</span> (交番データの編集・削除・CSV操作)
                </label>

                <label style="display: block; margin-bottom: 10px; cursor: pointer;">
                    <input type="checkbox" name="perm_admin" value="1">
                    <span style="color: #00ccff;">アカウント管理権限</span> (他管理者の編集・リセット)
                </label>

                <label style="display: block; cursor: pointer;">
                    <input type="checkbox" name="perm_log" value="1">
                    <span style="color: #ff88ff;">ログ閲覧権限</span> (操作ログ監査のアクセス)
                </label>
            </div>

            <div style="text-align: center;">
                <input type="submit" value="この内容でアカウントを作成" class="btn-primary" style="padding: 12px 30px; font-size: 16px;">
            </div>
        </form>
    </div>
</div>