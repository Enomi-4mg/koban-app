<?php require __DIR__ . '/../layouts/header.php'; ?>
<div class="container" style="max-width: 850px; margin-top: 20px;">

    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #1eff1a; padding-bottom: 10px; margin-bottom: 20px;">
        <h2 style="color: #1eff1a; margin: 0;">管理者一覧</h2>
        <?php if (isCurrentSuperAdmin()): ?>
            <a href="/admin/users/create" class="btn-primary" style="text-decoration: none; padding: 5px 15px;">＋ 新規登録</a>
        <?php endif; ?>
    </div>

    <div class="box">
        <table style="width: 100%; border-collapse: collapse; color: #1eff1a;">
            <thead>
                <tr style="border-bottom: 1px solid #555; text-align: center;">
                    <th style="padding: 10px; text-align: left;">ログインID</th>
                    <th>データ</th>
                    <th>管理</th>
                    <th>ログ</th>
                    <th style="text-align: right;">操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($admin_list as $admin): ?>
                    <tr style="border-bottom: 1px solid #222;">
                        <td style="padding: 12px 10px;">
                            <?php echo h($admin['login_id']); ?>
                            <?php if ($admin['login_id'] === $_SESSION['login_id']) echo ' <span style="color:#888; font-size:0.8em;">(自分)</span>'; ?>
                        </td>
                        <td style="text-align: center;">
                            <span style="color: <?php echo $admin['perm_data'] ? '#1eff1a' : '#333'; ?>;">●</span>
                        </td>
                        <td style="text-align: center;">
                            <span style="color: <?php echo $admin['perm_admin'] ? '#00ccff' : '#333'; ?>;">●</span>
                        </td>
                        <td style="text-align: center;">
                            <span style="color: <?php echo $admin['perm_log'] ? '#ff00ff' : '#333'; ?>;">●</span>
                        </td>
                        <td style="text-align: right;">
                            <a href="/admin/users/edit?id=<?php echo h($admin['login_id']); ?>" class="btn-detail" style="text-decoration: none; padding: 2px 10px; font-size: 0.9em;">詳細</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <p style="color: #888; font-size: 0.8em; margin-top: 10px;">
        凡例: <span style="color: #1eff1a;">●</span>データ管理 / <span style="color: #00ccff;">●</span>管理者管理 / <span style="color: #ff00ff;">●</span>ログ閲覧
    </p>

    <div style="margin-top: 20px;">
        <a href="/" style="color: #888; text-decoration: none;">← メイン画面へ戻る</a>
    </div>
</div>