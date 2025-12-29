<?php require __DIR__ . '/../layouts/header.php'; ?>

<div style="width: 400px; margin: 50px auto; background-color: #1a1a1a; padding: 20px; border: 1px solid #333; border-radius: 10px; box-shadow: 0px 0px 20px rgba(78, 78, 78, 0.4); text-align: center; color: #1eff1a; font-family: sans-serif;">
    <h2>パスワード変更</h2>

    <p style="color: <?php echo strpos($message, '変更しました') !== false ? '#1eff1a' : '#ff4444'; ?>;">
        <?php echo h($message); ?>
    </p>

    <p style="font-size: 14px; color: #ccc;">
        対象ID: <b><?php echo h($_SESSION['login_id'] ?? '不明'); ?></b>
    </p>

    <form method="post" action="/admin/password/update" style="display: flex; flex-direction: column; gap: 15px; text-align: left;">
        <input type="hidden" name="csrf_token" value="<?php echo h($_SESSION['csrf_token']); ?>">
        <div>
            <label style="color:#ccc;">現在のパスワード:</label>
            <input type="password" name="current_pass" required style="width: 100%; padding: 8px; background:#333; color:#fff; border:1px solid #555; box-sizing: border-box;">
        </div>
        <div>
            <label style="color:#ccc;">新しいパスワード:</label>
            <input type="password" name="new_pass" required style="width: 100%; padding: 8px; background:#333; color:#fff; border:1px solid #555; box-sizing: border-box;">
        </div>
        <form method="post" action="/admin/password/update" ...>
            <?php \App\Utils\View::component('button', [
                'type'    => 'submit',
                'variant' => 'primary',
                'text'    => '変更する',
                'style'   => 'margin-top: 10px; width: 100%; padding: 10px;'
            ]); ?>
        </form>
    </form>

    <br>
    <?php \App\Utils\View::component('button', [
        'type'    => 'link',
        'variant' => 'secondary',
        'text'    => '検索画面に戻る',
        'href'    => '/'
    ]); ?>
</div>