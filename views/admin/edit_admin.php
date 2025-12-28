<?php require __DIR__ . '/../layouts/header.php'; ?>
<div class="container" style="max-width: 600px; margin-top: 20px;">
    
    <div style="margin-bottom: 20px;">
        <a href="/admin/users" style="color: #1eff1a; text-decoration: none;">&lt;&lt; BACK_TO_LIST</a>
    </div>

    <div class="box" style="border: 1px solid #1eff1a; padding: 25px; background: #000;">
        <h2 style="color: #1eff1a; border-bottom: 1px solid #1eff1a; margin-bottom: 25px;">
            TARGET: <?php echo h($admin['login_id']); ?>
        </h2>

        <form method="post" action="/admin/users/update_perms" style="margin-bottom: 40px;">
            <input type="hidden" name="csrf_token" value="<?php echo h($_SESSION['csrf_token']); ?>">
            <input type="hidden" name="target_admin_id" value="<?php echo h($admin['login_id']); ?>">
            
            <h3 style="color: #ffff00; font-size: 0.9em; margin-bottom: 15px;">[ PERMISSION_FLAGS ]</h3>
            <div style="display: grid; gap: 12px; margin-bottom: 20px; padding-left: 10px;">
                <label style="cursor: pointer; display: block;">
                    <input type="checkbox" name="perm_data" value="1" <?php echo $admin['perm_data'] ? 'checked' : ''; ?> <?php echo isProtectedAdmin($admin['login_id']) ? 'disabled' : ''; ?>>
                    &nbsp;DATA_CRUD_ACCESS
                </label>
                <label style="cursor: pointer; display: block;">
                    <input type="checkbox" name="perm_admin" value="1" <?php echo $admin['perm_admin'] ? 'checked' : ''; ?> <?php echo isProtectedAdmin($admin['login_id']) ? 'disabled' : ''; ?>>
                    &nbsp;ADMIN_PRIVILEGE
                </label>
                <label style="cursor: pointer; display: block;">
                    <input type="checkbox" name="perm_log" value="1" <?php echo $admin['perm_log'] ? 'checked' : ''; ?> <?php echo isProtectedAdmin($admin['login_id']) ? 'disabled' : ''; ?>>
                    &nbsp;SYSTEM_LOG_VIEW
                </label>
            </div>
            
            <?php if (!isProtectedAdmin($admin['login_id'])): ?>
                <input type="submit" value="APPLY_CHANGES" class="btn-primary">
            <?php endif; ?>
        </form>

        <?php if (isCurrentSuperAdmin()): ?>
        <div style="border-top: 1px dashed #444; padding-top: 20px; margin-bottom: 40px;">
            <h3 style="color: #ff4444; font-size: 0.9em; margin-bottom: 15px;">[ PASSWORD_OVERWRITE ]</h3>
            <form method="post" action="/admin/users/reset_pw" onsubmit="return confirm('警告：パスワードを強制変更します。');">
                <input type="hidden" name="csrf_token" value="<?php echo h($_SESSION['csrf_token']); ?>">
                <input type="hidden" name="target_admin_id" value="<?php echo h($admin['login_id']); ?>">
                
                <input type="password" name="new_password" placeholder="NEW_SECURITY_KEY" required 
                       style="background: #000; color: #ff4444; border: 1px solid #ff4444; padding: 8px; width: 100%; margin-bottom: 10px;">
                <input type="submit" value="FORCE_RESET" class="btn-danger" style="width: 100%;">
            </form>
        </div>

        <?php if (!isProtectedAdmin($admin['login_id']) && $admin['login_id'] !== $_SESSION['login_id']): ?>
        <div style="border-top: 1px solid #444; padding-top: 20px; text-align: right;">
            <form method="post" action="/admin/users/delete" onsubmit="return confirm('重要：このアカウントを完全に削除します。');">
                <input type="hidden" name="csrf_token" value="<?php echo h($_SESSION['csrf_token']); ?>">
                <input type="hidden" name="delete_admin_id" value="<?php echo h($admin['login_id']); ?>">
                <input type="submit" value="DELETE_ACCOUNT" 
                       style="background: none; border: 1px solid #888; color: #888; cursor: pointer; font-size: 0.8em; padding: 5px 10px;">
            </form>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>