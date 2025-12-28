<?php require_once __DIR__ . '/../../vendor/autoload.php'; ?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title><?php echo h($page_title ?? '交番データベース'); ?></title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>

<body>
    <h1><?php echo h($page_title ?? 'R6 交番・駐在所検索データベース'); ?></h1>

    <?php if ($flash_msg = getFlashMessage()): ?>
        <?php
        // 成功メッセージ（緑）かエラーメッセージ（赤）かを判定
        $isSuccess = preg_match('/(作成|完了|成功|アップデート|変更しました)/u', $flash_msg);
        $msgColor = $isSuccess ? '#1eff1a' : '#ff4444';
        $borderColor = $isSuccess ? '#1eff1a' : '#ff4444';
        ?>
        <div id="flash-message-container" style="width: 80%; max-width: 800px; margin: 10px auto; transition: opacity 1s;">
            <div style="background-color: #1a1a1a; color: <?php echo $msgColor; ?>; font-weight: bold; border: 1px solid <?php echo $borderColor; ?>; padding: 15px; border-radius: 5px; box-shadow: 0 0 15px rgba(30, 255, 26, 0.2); text-align: center;">
                <?php echo ($isSuccess ? '[SUCCESS] ' : '[ERROR] ') . h($flash_msg); ?>
            </div>
        </div>
        <script>
            // 5秒後にメッセージをフェードアウトさせる（任意）
            setTimeout(() => {
                const msg = document.getElementById('flash-message-container');
                if (msg) msg.style.opacity = '0';
                setTimeout(() => msg?.remove(), 1000);
            }, 30000);
        </script>
    <?php endif; ?>