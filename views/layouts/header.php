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
        // 「作成」「成功」「完了」などのキーワードがあれば緑色、それ以外は赤色に設定
        $isSuccess = preg_match('/(作成|成功|完了|変更しました)/u', $flash_msg);
        $themeColor = $isSuccess ? '#1eff1a' : '#ff4444';
        ?>
        <div id="flash-message-container" style="width: 80%; max-width: 800px; margin: 10px auto;">
            <div style="background-color: #1a1a1a; color: <?php echo $themeColor; ?>; font-weight: bold; border: 1px solid <?php echo $themeColor; ?>; padding: 15px; border-radius: 5px; box-shadow: 0 0 10px <?php echo $themeColor; ?>44; text-align: center;">
                <?php echo ($isSuccess ? '[SUCCESS] ' : '[ERROR] ') . h($flash_msg); ?>
            </div>
        </div>
        <script>
            setTimeout(() => {
                const msg = document.getElementById('flash-message-container');
                if (msg) msg.style.opacity = '0';
                setTimeout(() => msg?.remove(), 1000);
            }, 30000);
        </script>
    <?php endif; ?>