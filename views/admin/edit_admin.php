<?php require __DIR__ . '/../layouts/header.php'; ?>

<div style="width: 500px; margin: 50px auto; background-color: #1a1a1a; padding: 30px; border: 1px solid #333; border-radius: 10px; color: #1eff1a; font-family: sans-serif;">
    <h2 style="border-bottom: 1px solid #444; padding-bottom: 10px;">管理者詳細設定</h2>

    <p style="color: #ccc;">対象ID: <b style="color: #fff; font-size: 1.2em;"><?php echo h($admin['login_id']); ?></b></p>

    <form method="post" action="/admin/users/update_perms" style="margin-top: 20px;">
        <input type="hidden" name="csrf_token" value="<?php echo h($_SESSION['csrf_token']); ?>">
        <input type="hidden" name="target_admin_id" value="<?php echo h($admin['login_id']); ?>">

        <div style="background: #222; padding: 20px; border-radius: 5px; border: 1px solid #444; display: flex; flex-direction: column; gap: 15px;">
            <h3 style="margin: 0 0 10px 0; font-size: 16px; color: #fff;">権限の割り当て</h3>

            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                <input type="checkbox" name="perm_data" value="1" <?php echo $admin['perm_data'] ? 'checked' : ''; ?>>
                <span style="color: #ffff00; font-weight: bold;">データ管理 (閲覧・編集・CSV操作)</span>
            </label>

            <?php if (isProtectedAdmin($admin['login_id'])): ?>
                <p style="color: #ff4444; font-size: 12px; margin-top: 10px;">
                    ※システム管理者の権限はセキュリティ保護のため固定されています。
                </p>
                <input type="hidden" name="perm_data" value="1">
                <input type="hidden" name="perm_admin" value="1">
                <input type="hidden" name="perm_log" value="1">
            <?php endif; ?>

            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                <input type="checkbox" name="perm_admin" value="1" <?php echo $admin['perm_admin'] ? 'checked' : ''; ?>>
                <span style="color: #00ccff; font-weight: bold;">アカウント管理 (管理者登録・削除・権限変更)</span>
            </label>

            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                <input type="checkbox" name="perm_log" value="1" <?php echo $admin['perm_log'] ? 'checked' : ''; ?>>
                <span style="color: #ff88ff; font-weight: bold;">ログ閲覧 (システム操作ログの監査)</span>
            </label>
        </div>

        <div style="margin-top: 25px; display: flex; gap: 10px;">
            <input type="submit" value="設定を保存する"
                style="flex: 2; background-color: #1eff1a; color: #000; border: none; padding: 12px; cursor: pointer; font-weight: bold; border-radius: 4px;">
            <a href="/admin/users"
                style="flex: 1; background: #333; color: #fff; text-decoration: none; text-align: center; padding: 12px; border-radius: 4px; border: 1px solid #555; line-height: 1.2;">
                戻る
            </a>
        </div>
    </form>
</div>