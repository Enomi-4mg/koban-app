<?php require __DIR__ . '/../layouts/header.php'; // まだ作成していない場合は適宜調整 
?>

<div style="width: 850px; margin: 30px auto; background-color: #1a1a1a; padding: 20px; border: 1px solid #333; border-radius: 10px; color: #1eff1a; font-family: sans-serif;">
    <h2 style="text-align: center;">管理者権限 管理パネル</h2>

    <?php if ($message): ?>
        <p style="color: #ff4444; font-weight: bold; border: 1px solid #555; padding: 10px; text-align: center;">
            <?php echo h($message); ?>
        </p>
    <?php endif; ?>

    <div style="border-bottom: 1px solid #444; padding-bottom: 20px; margin-bottom: 20px;">
        <h3 style="color: #fff; margin-top: 0;">新規管理者 登録</h3>
        <form method="post" action="/admin/users/store" style="display: flex; flex-direction: column; gap: 10px; text-align: left; width: 70%; margin: 0 auto;">
            <input type="hidden" name="csrf_token" value="<?php echo h($_SESSION['csrf_token']); ?>">
            <div>
                <label>新規ID:</label>
                <input type="text" name="new_admin_id" required style="width: 95%; background:#333; color:#fff; border:1px solid #555; padding:5px;">
            </div>
            <div>
                <label>パスワード:</label>
                <input type="password" name="new_admin_pass" required style="width: 95%; background:#333; color:#fff; border:1px solid #555; padding:5px;">
            </div>

            <div style="background: #222; padding: 10px; border-radius: 5px; border: 1px solid #444;">
                <label style="color: #ccc; font-size: 12px; display:block; margin-bottom:5px;">権限設定:</label>
                <label style="display:block; margin-bottom:3px;"><input type="checkbox" name="perm_data" value="1" checked> <span style="color: #ffff00;">データ管理</span></label>
                <label style="display:block; margin-bottom:3px;"><input type="checkbox" name="perm_admin" value="1"> <span style="color: #00ccff;">アカウント管理</span></label>
                <label style="display:block;"><input type="checkbox" name="perm_log" value="1"> <span style="color: #ff88ff;">ログ閲覧</span></label>
            </div>

            <input type="submit" value="登録する" style="background-color: #1eff1a; border: none; padding: 8px; cursor: pointer; font-weight: bold; margin-top:5px;">
        </form>
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
                        <?php if ($admin['login_id'] !== $_SESSION['login_id']): ?>
                            <div style="display: flex; gap: 5px; justify-content: center;">
                                <a href="/admin/password/reset?id=<?php echo h($admin['login_id']); ?>" style="background: #ffff00; color: #000; text-decoration: none; padding: 3px 8px; font-size: 11px; border-radius: 3px;">PW変更</a>
                                <form method="post" action="/admin/users/store" onsubmit="return confirm('本当に「<?php echo h($admin['login_id']); ?>」を削除しますか？');">
                                    <input type="hidden" name="csrf_token" value="<?php echo h($_SESSION['csrf_token']); ?>">
                                    <input type="hidden" name="delete_admin_id" value="<?php echo h($admin['login_id']); ?>">
                                    <input type="submit" value="削除" style="background: #ff4444; color: #fff; border: none; cursor: pointer; border-radius: 3px; padding: 3px 8px; font-size: 11px;">
                                </form>
                            </div>
                        <?php else: ?>
                            <span style="color: #888; font-size: 12px;">(自分)</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <br><a href="/" style="color: #888;">メイン画面に戻る</a>
</div>