<?php require __DIR__ . '/../layouts/header.php'; ?>
<div class="container" style="max-width: 700px; margin-top: 30px;">
    <div style="margin-bottom: 20px;">
        <a href="/admin/users" style="color: #1eff1a; text-decoration: none;">&lt;&lt; 管理パネルに戻る</a>
    </div>

    <div class="box" style="border: 1px solid #1eff1a; padding: 20px; background: #000;">
        <h2 style="color: #1eff1a; border-bottom: 1px solid #1eff1a; padding-bottom: 10px;">
            ADMIN_ID: <?php echo h($admin['login_id']); ?>
        </h2>

        <section style="margin-bottom: 40px;">
            <h3 style="color: #ffff00;">[ 権限マトリクス ]</h3>
            <form method="post" action="/admin/users/update_perms">
                <input type="hidden" name="csrf_token" value="<?php echo h($_SESSION['csrf_token']); ?>">
                <input type="hidden" name="target_admin_id" value="<?php echo h($admin['login_id']); ?>">

                <div style="display: grid; gap: 10px; margin: 20px 0;">
                    <label style="cursor: pointer;">
                        <input type="checkbox" name="perm_data" value="1" <?php echo $admin['perm_data'] ? 'checked' : ''; ?> <?php echo isProtectedAdmin($admin['login_id']) ? 'disabled' : ''; ?>>
                        DATA_MANAGE_ACCESS
                    </label>
                    <label style="cursor: pointer;">
                        <input type="checkbox" name="perm_admin" value="1" <?php echo $admin['perm_admin'] ? 'checked' : ''; ?> <?php echo isProtectedAdmin($admin['login_id']) ? 'disabled' : ''; ?>>
                        ADMIN_MANAGE_ACCESS
                    </label>
                    <label style="cursor: pointer;">
                        <input type="checkbox" name="perm_log" value="1" <?php echo $admin['perm_log'] ? 'checked' : ''; ?> <?php echo isProtectedAdmin($admin['login_id']) ? 'disabled' : ''; ?>>
                        AUDIT_LOG_ACCESS
                    </label>
                </div>

                <?php if (!isProtectedAdmin($admin['login_id'])): ?>
                    <input type="submit" value="UPDATE_PERMISSIONS" class="btn-primary" style="background: #1eff1a; color: #000; font-weight: bold;">
                <?php endif; ?>
            </form>
        </section>

        <?php if (isCurrentSuperAdmin()): ?>
            <section style="border-top: 1px dashed #444; pt: 20px;">
                <h3 style="color: #ff4444;">[ パスワードの強制上書き ]</h3>
                <form method="post" action="/admin/users/reset_pw" onsubmit="return confirm('対象のパスワードを上書きします。よろしいですか？');">
                    <input type="hidden" name="csrf_token" value="<?php echo h($_SESSION['csrf_token']); ?>">
                    <input type="hidden" name="target_admin_id" value="<?php echo h($admin['login_id']); ?>">

                    <div style="margin: 15px 0;">
                        <input type="password" name="new_password" placeholder="NEW_ENCRYPTED_KEY" required
                            style="background: #111; color: #ff4444; border: 1px solid #ff4444; padding: 10px; width: 100%;">
                    </div>
                    <input type="submit" value="EXECUTE_RESET" class="btn-danger" style="background: #ff4444; border: none; color: #fff; padding: 10px 20px; cursor: pointer;">
                </form>
            </section>
        <?php endif; ?>
    </div>
</div>