<?php require __DIR__ . '/../layouts/header.php'; ?>
<div class="container" style="max-width: 600px; margin-top: 20px;">
    
    <div style="margin-bottom: 15px;">
        <a href="/admin/users" style="color: #888; text-decoration: none;">← 管理者一覧に戻る</a>
    </div>

    <div class="box" style="border: 1px solid #1eff1a; padding: 20px;">
        <h2 style="color: #1eff1a; border-bottom: 1px solid #1eff1a; padding-bottom: 10px; margin-bottom: 20px;">
            設定対象: <?php echo h($admin['login_id']); ?>
        </h2>

        <form method="post" action="/admin/users/update_perms" style="margin-bottom: 30px;">
            <input type="hidden" name="csrf_token" value="<?php echo h($_SESSION['csrf_token']); ?>">
            <input type="hidden" name="target_admin_id" value="<?php echo h($admin['login_id']); ?>">
            
            <h3 style="color: #ffff00; font-size: 1em;">■ 権限設定</h3>
            <div style="margin: 15px 0; padding-left: 10px;">
                <label style="display: block; margin-bottom: 8px; cursor: pointer;">
                    <input type="checkbox" name="perm_data" value="1" <?php echo $admin['perm_data'] ? 'checked' : ''; ?> <?php echo isProtectedAdmin($admin['login_id']) ? 'disabled' : ''; ?>>
                    データ管理権限
                </label>
                <label style="display: block; margin-bottom: 8px; cursor: pointer;">
                    <input type="checkbox" name="perm_admin" value="1" <?php echo $admin['perm_admin'] ? 'checked' : ''; ?> <?php echo isProtectedAdmin($admin['login_id']) ? 'disabled' : ''; ?>>
                    管理者管理権限
                </label>
                <label style="display: block; margin-bottom: 8px; cursor: pointer;">
                    <input type="checkbox" name="perm_log" value="1" <?php echo $admin['perm_log'] ? 'checked' : ''; ?> <?php echo isProtectedAdmin($admin['login_id']) ? 'disabled' : ''; ?>>
                    ログ閲覧権限
                </label>
            </div>
            
            <?php if (!isProtectedAdmin($admin['login_id'])): ?>
                <input type="submit" value="権限を更新する" class="btn-primary">
            <?php endif; ?>
        </form>

        <?php if (isCurrentSuperAdmin()): ?>
            <div style="border-top: 1px dashed #444; padding-top: 20px;">
                <h3 style="color: #ff4444; font-size: 1em;">■ パスワードの強制変更</h3>
                <form method="post" action="/admin/users/reset_pw" onsubmit="return confirm('パスワードを上書きします。よろしいですか？');">
                    <input type="hidden" name="csrf_token" value="<?php echo h($_SESSION['csrf_token']); ?>">
                    <input type="hidden" name="target_admin_id" value="<?php echo h($admin['login_id']); ?>">
                    
                    <input type="password" name="new_password" placeholder="新しいパスワードを入力" required 
                           style="background: #000; color: #ff4444; border: 1px solid #ff4444; padding: 8px; width: 100%; margin: 10px 0;">
                    <input type="submit" value="パスワードをリセット" class="btn-danger" style="width: 100%;">
                </form>
            </div>
            
            <?php if (!isProtectedAdmin($admin['login_id']) && $admin['login_id'] !== $_SESSION['login_id']): ?>
                <div style="margin-top: 30px; text-align: right;">
                    <form method="post" action="/admin/users/delete" onsubmit="return confirm('このアカウントを完全に削除しますか？');">
                        <input type="hidden" name="csrf_token" value="<?php echo h($_SESSION['csrf_token']); ?>">
                        <input type="hidden" name="delete_admin_id" value="<?php echo h($admin['login_id']); ?>">
                        <input type="submit" value="アカウントを削除" style="background:none; border:none; color:#666; cursor:pointer; font-size:0.8em; text-decoration:underline;">
                    </form>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>