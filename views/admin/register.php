<?php require __DIR__ . '/../layouts/header.php'; ?>

<div style="width: 850px; margin: 30px auto; background-color: #1a1a1a; padding: 20px; border: 1px solid #333; border-radius: 10px; color: #1eff1a; font-family: sans-serif;">
    <h2 style="text-align: center;">管理者権限 管理パネル</h2>

    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #444; padding-bottom: 15px; margin-bottom: 20px;">
        <h3 style="color: #fff; margin: 0;">現在の管理者一覧</h3>

        <div style="display: flex; gap: 10px;">
            <?php if (isCurrentSuperAdmin()): ?>
                <a href="/admin/users/create" class="btn-primary" style="text-decoration: none; padding: 8px 15px; font-size: 14px; border-radius: 4px; display: inline-block;">
                    ＋ 新規管理者を登録
                </a>
            <?php endif; ?>
            <a href="/admin/users/export" class="btn btn-warning" style="font-size: 12px; text-decoration: none;">
                📥 CSV保存
            </a>
        </div>
    </div>

    <table border="1" style="width: 100%; border-collapse: collapse; border-color: #555;">
    </table>

    <br><a href="/" style="color: #888;">メイン画面に戻る</a>
</div>

<div>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
        <h3 style="color: #fff; margin: 0;">現在の管理者一覧</h3>
        <div style="display: flex; gap: 10px;">
            <a href="/admin/users/export" class="btn btn-warning" style="font-size: 12px; text-decoration: none;">
                📥 管理者データをCSV保存
            </a>
            <a href="/admin/password/reset" style="background: #333; color: #ffff00; text-decoration: none; padding: 5px 10px; border: 1px solid #ffff00; border-radius: 4px; font-size: 12px;">
                🔑 パスワードリセット画面へ
            </a>
        </div>
    </div>

    <table border="1" style="width: 100%; border-collapse: collapse; border-color: #555;">
        <tr style="background: #333; color: #fff;">
            <th>ID</th>
            <th>データ</th>
            <th>アカウント</th>
            <th>ログ</th>
            <th>最終更新</th>
            <th>操作</th>
        </tr>
        <?php foreach ($admin_list as $admin): ?>
            <tr>
                <td style="padding: 8px; font-weight: bold;"><?php echo h($admin['login_id']); ?></td>
                <td style="text-align: center;">
                    <?php echo ($admin['perm_data'] == 1) ? "<span style='color:#ffff00;'>●</span>" : "<span style='color:#333;'>-</span>"; ?>
                </td>
                <td style="text-align: center;">
                    <?php echo ($admin['perm_admin'] == 1) ? "<span style='color:#00ccff;'>●</span>" : "<span style='color:#333;'>-</span>"; ?>
                </td>
                <td style="text-align: center;">
                    <?php echo ($admin['perm_log'] == 1) ? "<span style='color:#ff88ff;'>●</span>" : "<span style='color:#333;'>-</span>"; ?>
                </td>
                <td style="padding: 8px; text-align: center; color: #ccc; font-size: 14px;">
                    <?php echo $admin['last_act_time'] ? date('m/d H:i', strtotime($admin['last_act_time'])) : "-"; ?>
                </td>
                <td style="padding: 8px; text-align: center;">
                    <?php if (isCurrentSuperAdmin() && $admin['login_id'] !== $_SESSION['login_id']): ?>
                        <a href="/admin/users/edit?id=<?php echo h($admin['login_id']); ?>" class="btn-detail">詳細</a>
                        <form method="post" action="/admin/users/store" style="display:inline;">
                        </form>
                    <?php elseif ($admin['login_id'] === $_SESSION['login_id']): ?>
                        <span style="color: #888;">(自分)</span>
                    <?php else: ?>
                        <span style="color: #555;">権限なし</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
<br><a href="/" style="color: #888;">メイン画面に戻る</a>
</div>