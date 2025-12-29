<?php require __DIR__ . '/../layouts/header.php'; ?>
<style>
    /* サイバーチェックボックスのスタイル */
    .cyber-checkbox-label {
        display: block;
        cursor: pointer;
    }

    .cyber-checkbox-label input {
        display: none;
    }

    /* デフォルトのチェックボックスを隠す */

    .cyber-panel {
        display: block;
        padding: 12px;
        border: 1px solid #333;
        background: #111;
        color: #555;
        /* デフォルトは暗い色 */
        transition: all 0.3s;
    }

    .cyber-panel .status-dot {
        margin-right: 10px;
    }

    /* チェック時のスタイル */
    .cyber-checkbox-label input:checked+.cyber-panel {
        border-color: #1eff1a;
        color: #1eff1a;
        background: rgba(30, 255, 26, 0.1);
        box-shadow: 0 0 10px rgba(30, 255, 26, 0.2) inset;
    }

    /* ホバー時 */
    .cyber-panel:hover {
        border-color: #666;
    }

    /* 無効時（特権アカウント） */
    .cyber-checkbox-label input:disabled+.cyber-panel {
        opacity: 0.5;
        cursor: not-allowed;
        border-style: dotted;
    }
</style>
<div class="container" style="max-width: 600px; margin-top: 20px;">

    <?php if (($admin['request_status'] ?? '') === 'pending'): ?>
        <div class="box" style="border: 2px solid var(--cyber-yellow); margin-bottom: 30px;">
            <h3 style="color: var(--cyber-yellow);">申請内容の審査</h3>
            <p style="font-size: 0.9em;">理由: <?php echo h($admin['request_message']); ?></p>

            <form method="post" action="/admin/users/handle_request">
                <div style="margin-bottom: 15px;">
                    <label>
                        <input type="checkbox" name="grant_data" value="1" <?php echo $admin['req_perm_data'] ? 'checked' : ''; ?>>
                        データ管理権限 <?php echo $admin['req_perm_data'] ? '<span style="color:var(--cyber-yellow)">[!] 申請中</span>' : ''; ?>
                    </label><br>
                </div>

                <button name="request_action" value="approve" class="btn btn-primary">選択した権限を付与して承認</button>
                <button name="request_action" value="reject" class="btn btn-danger">申請を却下</button>
            </form>
        </div>
    <?php endif; ?>

    <div style="margin-bottom: 15px;">
        <a href="/admin/users" style="color: #888; text-decoration: none;">← 管理者一覧に戻る</a>
    </div>

    <div class="box" style="border: 1px solid #1eff1a; padding: 20px;">
        <h2 style="color: #1eff1a; border-bottom: 1px solid #1eff1a; padding-bottom: 10px; margin-bottom: 20px;">
            設定対象: <?php echo h($admin['login_id']); ?>
        </h2>

        <form method="post" action="/admin/users/update_perms" style="margin-bottom: 30px;">
            <input type="hidden" name="csrf_token" value="<?php echo h($_SESSION['csrf_token']); ?>">
            <input type="hidden" name="target_admin_id" value="<?php echo h($admin['login_id']); ?>">

            <h3 style="color: #ffff00; font-size: 0.9em; margin-bottom: 15px;">■ 権限割り当て (クリックで切替)</h3>
            <div style="display: grid; gap: 10px; margin-bottom: 25px;">

                <label class="cyber-checkbox-label">
                    <input type="checkbox" name="perm_data" value="1" <?php echo $admin['perm_data'] ? 'checked' : ''; ?> <?php echo isProtectedAdmin($admin['login_id']) ? 'disabled' : ''; ?>>
                    <span class="cyber-panel">
                        <span class="status-dot">●</span> データ管理権限
                    </span>
                </label>

                <label class="cyber-checkbox-label">
                    <input type="checkbox" name="perm_admin" value="1" <?php echo $admin['perm_admin'] ? 'checked' : ''; ?> <?php echo isProtectedAdmin($admin['login_id']) ? 'disabled' : ''; ?>>
                    <span class="cyber-panel">
                        <span class="status-dot">●</span> 管理者管理権限
                    </span>
                </label>

                <label class="cyber-checkbox-label">
                    <input type="checkbox" name="perm_log" value="1" <?php echo $admin['perm_log'] ? 'checked' : ''; ?> <?php echo isProtectedAdmin($admin['login_id']) ? 'disabled' : ''; ?>>
                    <span class="cyber-panel">
                        <span class="status-dot">●</span> ログ閲覧権限
                    </span>
                </label>
            </div>

            <?php if (!isProtectedAdmin($admin['login_id'])): ?>
                <?php \App\Utils\View::component('button', [
                    'type'    => 'submit',
                    'variant' => 'primary',
                    'text'    => '権限を更新する'
                ]); ?>
            <?php endif; ?>
        </form>

        <?php if (isCurrentSuperAdmin()): ?>
            <div style="border-top: 1px dashed #444; padding-top: 20px;">
                <h3 style="color: #ff4444; font-size: 1em;">■ パスワードの強制変更</h3>
                <form method="post" action="/admin/users/reset_pw" onsubmit="return confirm('パスワードを上書きします。よろしいですか？');">
                    <input type="hidden" name="csrf_token" value="<?php echo h($_SESSION['csrf_token']); ?>">
                    <input type="hidden" name="target_admin_id" value="<?php echo h($admin['login_id']); ?>">

                    <input type="password" name="new_password" placeholder="新しいパスワードを入力" required
                        style="background: #000; color: #ff4444; border: 1px solid #ff4444; padding: 8px; width: 100%; margin: 10px 0;">

                    <?php \App\Utils\View::component('button', [
                        'type'    => 'submit',
                        'variant' => 'danger',
                        'text'    => 'パスワードをリセット',
                        'style'   => 'width: 100%;'
                    ]); ?>
                </form>
            </div>

            <?php if (!isProtectedAdmin($admin['login_id']) && $admin['login_id'] !== $_SESSION['login_id']): ?>
                <div style="margin-top: 30px; text-align: right;">
                    <form method="post" action="/admin/users/delete" onsubmit="return confirm('このアカウントを完全に削除しますか？');">
                        <input type="hidden" name="csrf_token" value="<?php echo h($_SESSION['csrf_token']); ?>">
                        <input type="hidden" name="delete_admin_id" value="<?php echo h($admin['login_id']); ?>">
                        <input type="submit" value="アカウントを削除" style="background:none; border:none; color:#666; cursor:pointer; font-size:0.8em; text-decoration:underline;">
                    </form>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>