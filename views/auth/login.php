<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container" style="max-width: 400px; margin-top: 80px;">
    <div class="box" style="border: 2px solid #1eff1a; box-shadow: 0 0 15px rgba(30, 255, 26, 0.3);">
        <h2 style="color: #1eff1a; text-align: center; font-family: 'Courier New', Courier, monospace;">
            IDENTIFICATION REQUIRED
        </h2>

        <?php if ($message): ?>
            <div style="color: #ff4444; border: 1px solid #ff4444; padding: 10px; margin-bottom: 20px; font-size: 0.9em; background: rgba(255, 68, 68, 0.1);">
                [!] <?php echo h($message); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="/auth/login" style="display: flex; flex-direction: column; gap: 20px;">
            <input type="hidden" name="csrf_token" value="<?php echo h($_SESSION['csrf_token']); ?>">

            <div>
                <label style="color: #1eff1a; font-size: 0.8em;">USER ID</label>
                <input type="text" name="login_id" required
                    style="width: 100%; padding: 10px; background: #000; border: 1px solid #333; color: #1eff1a; box-sizing: border-box;">
            </div>

            <div>
                <label style="color: #1eff1a; font-size: 0.8em;">PASSWORD</label>
                <input type="password" name="login_pass" required
                    style="width: 100%; padding: 10px; background: #000; border: 1px solid #333; color: #1eff1a; box-sizing: border-box;">
            </div>

            <button type="submit" class="btn-primary" style="width: 100%; padding: 12px; font-size: 1.1em; letter-spacing: 2px;">
                ENTER SYSTEM
            </button>
        </form>

        <div style="margin-top: 20px; text-align: center; border-top: 1px solid #333; padding-top: 15px;">
            <p style="color: #666; font-size: 0.8em;">No account?</p>
            <a href="/register" style="color: #1eff1a; text-decoration: underline;">Create New Identity</a>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>