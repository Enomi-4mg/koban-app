<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container" style="max-width: 450px; margin-top: 60px;">
    <div class="box" style="border: 2px solid #1eff1a; padding: 30px; position: relative; overflow: hidden; box-shadow: 0 0 15px rgba(30, 255, 26, 0.3);">
        <div class="scan-line"></div>

        <h2 style="color: #1eff1a; text-shadow: 0 0 10px rgba(30, 255, 26, 0.5); text-align: center; margin-top: 0; letter-spacing: 2px; font-family: 'Courier New', Courier, monospace;">
            システム認証
        </h2>

        <?php if ($message): ?>
            <div style="color: #ff4444; border: 1px solid #ff4444; padding: 10px; margin-bottom: 20px; font-size: 0.9em; background: rgba(255, 68, 68, 0.1); text-align: left;">
                [!] <?php echo h($message); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="/auth/login" style="display: flex; flex-direction: column; gap: 20px; text-align: left;">
            <input type="hidden" name="csrf_token" value="<?php echo h($_SESSION['csrf_token']); ?>">

            <div>
                <label style="color: #1eff1a; display: block; margin-bottom: 8px; font-size: 0.9em;">ログインID</label>
                <input type="text" name="login_id" required
                    style="width: 100%; padding: 12px; background: #000; border: 1px solid #333; color: #1eff1a; box-sizing: border-box; font-family: monospace;"
                    placeholder="IDを入力してください">
            </div>

            <div>
                <label style="color: #1eff1a; display: block; margin-bottom: 8px; font-size: 0.9em;">パスワード</label>
                <input type="password" name="login_pass" required
                    style="width: 100%; padding: 12px; background: #000; border: 1px solid #333; color: #1eff1a; box-sizing: border-box; font-family: monospace;"
                    placeholder="パスワードを入力してください">
            </div>

            <input type="submit" value="[ システムにログイン ]" class="btn-primary"
                style="width: 100%; padding: 15px; font-weight: bold; font-size: 1.1em; cursor: pointer; border: 1px solid #1eff1a; background: transparent; color: #1eff1a; transition: 0.3s;">
        </form>

        <div style="margin-top: 25px; text-align: center; border-top: 1px solid #333; padding-top: 15px;">
            <p style="color: #666; font-size: 0.8em; margin-bottom: 10px;">アカウントをお持ちでない場合</p>
            <a href="/register" style="color: #1eff1a; text-decoration: underline; font-size: 0.9em;">新規アカウント作成</a>
        </div>
    </div>
</div>

<style>
    .scan-line {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 2px;
        background: rgba(30, 255, 26, 0.2);
        box-shadow: 0 0 15px #1eff1a;
        animation: scan 4s linear infinite;
        pointer-events: none;
    }

    @keyframes scan {
        0% {
            top: -5%;
        }

        100% {
            top: 105%;
        }
    }

    input[type=submit]:hover {
        background: #1eff1a !important;
        color: #000 !important;
        box-shadow: 0 0 20px #1eff1a;
    }

    input:focus {
        outline: none;
        border-color: #1eff1a !important;
        box-shadow: 0 0 5px #1eff1a;
    }
</style>

<?php require __DIR__ . '/../layouts/footer.php'; ?>