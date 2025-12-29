<?php
// $message が未定義または null の場合のデフォルト値を設定
$msgText = $message ?? '';
$isSuccess = preg_match('/(作成|成功|完了|変更しました|承認|申請)/u', $msgText);
$color = $isSuccess ? 'var(--cyber-green)' : 'var(--cyber-red)';
$label = $isSuccess ? '[ SUCCESS ]' : '[ ERROR ]';

if ($msgText === '') return; // メッセージが空なら何も表示しない
?>
<div class="alert-container" style="border: 1px solid <?php echo $color; ?>; background: rgba(0,0,0,0.8); padding: 15px; margin: 10px auto; width: 80%; box-shadow: 0 0 10px <?php echo $color; ?>;">
    <span style="color: <?php echo $color; ?>; font-weight: bold; text-shadow: 0 0 5px <?php echo $color; ?>;">
        <?php echo $label; ?> <?php echo h($msgText); ?>
    </span>
</div>

<div class="box" style="border: 1px solid var(--cyber-yellow); background: rgba(255, 255, 0, 0.05); padding: 20px; margin-top: 20px;">
    <h3 style="color: var(--cyber-yellow); margin-top: 0;">[ 権限未取得 ]</h3>
    <p style="color: #ccc; font-size: 0.9em;">
        現在、あなたのカウントは「閲覧専用」です。データの編集・追加を行うには権限昇格の申請が必要です。
    </p>
    <form method="post" action="/auth/request_permission">
        <input type="hidden" name="csrf_token" value="<?php echo h($_SESSION['csrf_token']); ?>">
        <div style="margin-bottom: 10px;">
            <input type="text" name="request_reason" placeholder="申請理由を入力（例：データ更新作業のため）"
                style="width: 100%; border-color: var(--cyber-yellow); color: var(--cyber-yellow);">
        </div>
        <button type="submit" class="btn-warning" style="width: 100%;">
            権限昇格をリクエストする
        </button>
    </form>
</div>