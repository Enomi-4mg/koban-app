<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container" style="max-width: 600px; margin-top: 40px;">

    <div style="text-align: left; margin-bottom: 20px;">
        <?php \App\Utils\View::component('button', [
            'type'    => 'link',
            'variant' => 'secondary',
            'text'    => '←検索画面に戻る',
            'href'    => '/'
        ]); ?>
    </div>

    <div class="box" style="border: 2px solid var(--cyber-yellow); padding: 30px; position: relative; overflow: hidden;">
        <div class="scan-line"></div>

        <h2 style="color: var(--cyber-yellow); border-bottom: 1px solid var(--cyber-yellow); padding-bottom: 10px; margin-top: 0;">
            PERMISSION_UPGRADE / 権限昇格申請
        </h2>

        <div style="background: rgba(30, 255, 26, 0.05); border: 1px solid #333; padding: 15px; margin-bottom: 25px;">
            <h3 style="font-size: 0.9em; color: #888; margin-top: 0; margin-bottom: 10px;">[ CURRENT_STATUS / 現在の所持権限 ]</h3>
            <div style="display: flex; gap: 20px; font-family: monospace;">
                <?php
                $myPerms = $_SESSION['permissions'] ?? []; //
                $labels = [
                    'data'  => ['label' => 'データ管理', 'color' => '#1eff1a'],
                    'admin' => ['label' => '管理者管理', 'color' => '#00ccff'],
                    'log'   => ['label' => 'ログ閲覧',   'color' => '#ff00ff']
                ];

                foreach ($labels as $key => $info):
                    $hasIt = !empty($myPerms[$key]);
                ?>
                    <div style="display: flex; align-items: center; gap: 5px;">
                        <span style="color: <?php echo $hasIt ? $info['color'] : '#333'; ?>; font-size: 1.2em;">●</span>
                        <span style="color: <?php echo $hasIt ? '#fff' : '#444'; ?>; font-size: 0.85em;">
                            <?php echo $info['label']; ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <form method="post" action="/auth/request_permission">
            <input type="hidden" name="csrf_token" value="<?php echo h($_SESSION['csrf_token']); ?>">

            <p style="font-size: 0.85em; color: #ccc; margin-bottom: 15px;">申請を希望する権限を選択してください（複数可）：</p>

            <div style="display: grid; gap: 10px; margin-bottom: 25px;">
                <label class="cyber-checkbox-label">
                    <input type="checkbox" name="req_data" value="1" <?php if (!empty($myPerms['data'])) echo 'disabled'; ?>>
                    <span class="cyber-panel">
                        ● データ管理権限 <?php if (!empty($myPerms['data'])) echo '<small style="color:#666;">(取得済み)</small>'; ?>
                    </span>
                </label>

                <label class="cyber-checkbox-label">
                    <input type="checkbox" name="req_admin" value="1" <?php if (!empty($myPerms['admin'])) echo 'disabled'; ?>>
                    <span class="cyber-panel">
                        ● 管理者管理権限 <?php if (!empty($myPerms['admin'])) echo '<small style="color:#666;">(取得済み)</small>'; ?>
                    </span>
                </label>

                <label class="cyber-checkbox-label">
                    <input type="checkbox" name="req_log" value="1" <?php if (!empty($myPerms['log'])) echo 'disabled'; ?>>
                    <span class="cyber-panel">
                        ● ログ閲覧権限 <?php if (!empty($myPerms['log'])) echo '<small style="color:#666;">(取得済み)</small>'; ?>
                    </span>
                </label>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="color: var(--cyber-green); display: block; margin-bottom: 8px; font-size: 0.9em;">申請理由 / REQUEST_REASON</label>
                <textarea name="request_reason" required
                    style="width: 100%; height: 80px; background: #000; border: 1px solid #333; color: var(--cyber-green); padding: 10px; font-family: monospace; box-sizing: border-box;"
                    placeholder="例: 担当地域の交番データを更新するため"></textarea>
            </div>

            <?php \App\Utils\View::component('button', [
                'type'    => 'submit',
                'variant' => 'primary',
                'text'    => '[ 申請プロトコルを実行 ]',
                'style'   => 'width: 100%; padding: 15px; font-size: 1.1em;'
            ]); ?>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>