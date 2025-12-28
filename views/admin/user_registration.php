<?php require __DIR__ . '/../layouts/header.php'; ?>
<div class="container" style="max-width: 500px; margin-top: 50px;">
    <div class="box" style="border: 2px solid #1eff1a; background: #000; padding: 30px;">
        <h2 style="color: #1eff1a; text-align: center;">NEW_ADMIN_ENTRY</h2>

        <form method="post" action="/admin/users/register">
            <input type="hidden" name="csrf_token" value="<?php echo h($_SESSION['csrf_token']); ?>">

            <p>LOGIN_ID:</p>
            <input type="text" name="new_id" required style="width: 100%; background: #111; border: 1px solid #1eff1a; color: #1eff1a; padding: 10px;">

            <p>INITIAL_PASSWORD:</p>
            <input type="password" name="new_pass" required style="width: 100%; background: #111; border: 1px solid #1eff1a; color: #1eff1a; padding: 10px;">

            <div style="margin-top: 20px; border: 1px dashed #333; padding: 15px;">
                <p style="margin-top: 0;">DEFAULT_PERMISSIONS:</p>
                <label><input type="checkbox" name="perm_data" value="1" checked> DATA</label><br>
                <label><input type="checkbox" name="perm_admin" value="1"> ADMIN</label><br>
                <label><input type="checkbox" name="perm_log" value="1"> LOG</label>
            </div>

            <div style="margin-top: 30px; display: flex; justify-content: space-between; align-items: center;">
                <a href="/admin/users" style="color: #888;">CANCEL</a>
                <input type="submit" value="CREATE_ACCOUNT" class="btn-primary" style="padding: 10px 20px;">
            </div>
        </form>
    </div>
</div>