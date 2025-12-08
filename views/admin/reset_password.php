<?php require __DIR__ . '/../layouts/header.php'; ?>

<div style="width: 500px; margin: 50px auto; background-color: #1a1a1a; padding: 30px; border: 1px solid #333; border-radius: 10px; text-align: left; color: #1eff1a; font-family: sans-serif;">
    <h2 style="color: #fff; margin-top: 0; text-align: center; border-bottom: 1px solid #444; padding-bottom: 10px;">パスワード強制リセット</h2>

    <?php if ($message): ?>
        <p style="color: <?php echo strpos($message, 'エラー') !== false ? '#ff4444' : '#1eff1a'; ?>; font-weight: bold; text-align: center;">
            <?php echo h($message); ?>
        </p>
    <?php endif; ?>

    <p style="font-size: 13px; color: #ccc;">
        指定した管理者IDのパスワードを強制的に上書きします。<br>
        <span style="color: #ff4444;">※現在のパスワードを知らなくても変更可能です。</span>
    </p>

    <form method="post" onsubmit="return confirm('本当にパスワードを変更しますか？\nこの操作は取り消せません。');">
        <input type="hidden" name="csrf_token" value="<?php echo h($_SESSION['csrf_token']); ?>">

        <div style="margin-bottom: 15px;">
            <label style="color: #fff;">対象の管理者ID:</label>
            <input type="text" name="reset_target_id" value="<?php echo h($target_id); ?>" required placeholder="user_id" style="width: 100%; padding: 8px; margin-top: 5px; background: #333; color: #fff; border: 1px solid #555;">
        </div>

        <div style="margin-bottom: 15px;">
            <label style="color: #fff;">新しいパスワード:</label>
            <input type="password" name="reset_new_pass" required placeholder="New Password" style="width: 100%; padding: 8px; margin-top: 5px; background: #333; color: #fff; border: 1px solid #555;">
        </div>

        <input type="submit" value="変更を実行する" style="width: 100%; background-color: #ffff00; color: #000; border: none; padding: 10px; cursor: pointer; font-weight: bold; margin-top: 20px; font-size: 16px; border-radius: 4px;">
    </form>

    <div style="text-align: center; margin-top: 20px;">
        <a href="/admin/users" style="color: #888; text-decoration: none;">管理者一覧に戻る</a>
    </div>
</div>