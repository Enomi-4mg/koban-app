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
        <?php \App\Utils\View::component('alert', ['message' => $flash_msg]); ?>
    <?php endif; ?>