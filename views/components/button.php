<?php

/**
 * @var string $type 'submit' | 'link' | 'button'
 * @var string $variant 'primary' | 'secondary' | 'warning' | 'danger'
 * @var string $text 表示文字
 * @var string $href linkの場合の遷移先
 * @var string $onclick JSイベント
 * @var string $style 追加のインラインスタイル
 */
$variant = $variant ?? 'primary';
$class = "btn btn-{$variant}";
$customStyle = $style ?? '';
?>

<?php if (($type ?? 'submit') === 'link'): ?>
    <a href="<?php echo h($href ?? '#'); ?>" class="<?php echo $class; ?>" style="<?php echo $customStyle; ?>">
        [ <?php echo h($text); ?> ]
    </a>
<?php else: ?>
    <button type="<?php echo h($type ?? 'submit'); ?>"
        class="<?php echo $class; ?>"
        onclick="<?php echo h($onclick ?? ''); ?>"
        style="<?php echo $customStyle; ?>">
        <?php echo h($text); ?>
    </button>
<?php endif; ?>