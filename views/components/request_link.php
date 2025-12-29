<?php
$status = $_SESSION['request_status'] ?? null;
$hasDataPerm = hasPermission(PERM_DATA);
?>

<?php if (!$hasDataPerm): ?>
    <?php if ($status === 'pending'): ?>
        <span style="color: var(--cyber-yellow); opacity: 0.6; margin-right: 15px; font-family: monospace;">
            [ 申請中... ]
        </span>
    <?php elseif ($status === 'rejected'): ?>
        <form action="/auth/request_permission" method="post" style="display: inline;" onsubmit="return confirm('前回の申請は承認されませんでした。再申請しますか？');">
            <input type="hidden" name="csrf_token" value="<?php echo h($_SESSION['csrf_token']); ?>">
            <button type="submit" style="background: none; border: none; color: var(--cyber-red); cursor: pointer; text-decoration: underline; margin-right: 15px;">
                [ 再申請を行う ]
            </button>
        </form>
    <?php else: ?>
        <form action="/auth/request_permission" method="post" style="display: inline;" onsubmit="return confirm('データ編集権限を申請しますか？');">
            <input type="hidden" name="csrf_token" value="<?php echo h($_SESSION['csrf_token']); ?>">
            <input type="hidden" name="request_reason" value="システム利用による権限昇格申請">
            <button type="submit" style="background: none; border: none; color: var(--cyber-green); cursor: pointer; text-decoration: underline; margin-right: 15px;">
                [ 権限申請 ]
            </button>
        </form>
    <?php endif; ?>
<?php endif; ?>