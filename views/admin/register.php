<?php require __DIR__ . '/../layouts/header.php'; ?>
<div class="container" style="max-width: 850px; margin-top: 20px;">
    <div style="display: flex; margin-top: 20px;">
        <?php \App\Utils\View::component('button', [
            'type'    => 'link',
            'variant' => 'secondary',
            'text'    => '← メイン画面へ戻る',
            'href'    => '/'
        ]); ?>
    </div>
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid var(--cyber-green); padding-bottom: 10px; margin-bottom: 20px;">
        <h2 style="color: var(--cyber-green); margin: 0;">管理者一覧</h2>
        <div style="display: flex; gap: 10px;">
            <?php if (isCurrentSuperAdmin()): ?>
                <?php \App\Utils\View::component('button', [
                    'type' => 'link',
                    'variant' => 'secondary',
                    'text' => 'CSV出力',
                    'href' => '/admin/users/export'
                ]); ?>
                <?php \App\Utils\View::component('button', [
                    'type' => 'link',
                    'variant' => 'primary',
                    'text' => '＋ 新規登録',
                    'href' => '/admin/users/create'
                ]); ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="box">
        <table style="width: 100%; border-collapse: collapse; color: #1eff1a; background-color: #000;">
            <thead>
                <tr style="border-bottom: 2px solid #1eff1a;">
                    <th style="padding: 10px; text-align: left;">ログインID</th>
                    <th style="width: 60px;">データ</th>
                    <th style="width: 60px;">管理</th>
                    <th style="width: 60px;">ログ</th>
                    <th style="padding: 10px; text-align: center; width: 120px;">アクション</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($admin_list as $admin): ?>
                    <tr style="border-bottom: 1px solid #222;">
                        <td style="padding: 12px 10px;">
                            <?php echo h($admin['login_id']); ?>
                            <?php if ($admin['login_id'] === $_SESSION['login_id']) echo ' <span style="color:#666; font-size:0.8em;">(自分)</span>'; ?>

                            <?php if (($admin['request_status'] ?? '') === 'pending'): ?>
                                <span style="background: #ffff00; color: #000; font-size: 10px; padding: 2px 5px; border-radius: 3px; margin-left: 10px; font-weight: bold; box-shadow: 0 0 8px #ffff00;">
                                    REQUESTED
                                </span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: center;"><span style="color: <?php echo $admin['perm_data'] ? '#1eff1a' : '#333'; ?>;">●</span></td>
                        <td style="text-align: center;"><span style="color: <?php echo $admin['perm_admin'] ? '#00ccff' : '#333'; ?>;">●</span></td>
                        <td style="text-align: center;"><span style="color: <?php echo $admin['perm_log'] ? '#ff00ff' : '#333'; ?>;">●</span></td>
                        <td style="padding: 10px; text-align: center;">
                            <?php
                            $isPending = ($admin['request_status'] ?? '') === 'pending'; // 申請中か判定
                            ?>

                            <div class="badge-wrapper" style="position: relative; display: inline-block;">
                                <?php
                                \App\Utils\View::component('button', [
                                    'type'    => 'link',
                                    'variant' => $isPending ? 'warning' : 'primary', // 申請中はイエロー(warning)に変更
                                    'text'    => $isPending ? '承認審査' : '詳細編集', // 文言を動的に変更
                                    'href'    => '/admin/users/edit?id=' . h($admin['login_id']),
                                    'style'   => 'padding: 4px 12px; font-size: 0.85em;'
                                ]);
                                ?>

                                <?php if ($isPending): ?>
                                    <span class="notification-badge" style="top: -8px; right: -8px;">!</span>
                                <?php endif; ?>
                            </div>
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
        <?php \App\Utils\View::component('button', [
            'type'    => 'link',
            'variant' => 'secondary',
            'text'    => 'メイン画面へ戻る',
            'href'    => '/'
        ]); ?>
    </div>
</div>