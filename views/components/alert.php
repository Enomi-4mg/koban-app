<?php
$msgText = $message ?? '';
$isSuccess = preg_match('/(作成|成功|完了|変更しました|承認|申請)/u', $msgText);
$color = $isSuccess ? 'var(--cyber-green)' : 'var(--cyber-red)';
$label = $isSuccess ? '[ SUCCESS ]' : '[ ERROR ]';

if ($msgText === '') return;
?>
<div class="alert-container" style="border: 1px solid <?php echo $color; ?>; background: rgba(0,0,0,0.8); padding: 15px; margin: 10px auto; width: 80%; box-shadow: 0 0 10px <?php echo $color; ?>;">
    <span style="color: <?php echo $color; ?>; font-weight: bold; text-shadow: 0 0 5px <?php echo $color; ?>;">
        <?php echo $label; ?> <?php echo h($msgText); ?>
    </span>
</div>