<?php

namespace App\Utils;

class View
{
    /**
     * ページ全体のレンダリング（既存）
     */
    public static function render($viewName, $data = [])
    {
        extract($data);
        $filePath = __DIR__ . '/../../views/' . $viewName . '.php';
        if (file_exists($filePath)) {
            require $filePath;
        } else {
            echo "View file not found: " . htmlspecialchars($viewName);
        }
    }

    /**
     * UIコンポーネントのレンダリング（新規追加）
     * @param string $componentName componentsフォルダ内のファイル名
     * @param array $props コンポーネントに渡す引数
     */
    public static function component($componentName, $props = [])
    {
        extract($props);
        $filePath = __DIR__ . '/../../views/components/' . $componentName . '.php';

        if (file_exists($filePath)) {
            include $filePath; // componentは複数回呼ばれるため include
        } else {
            echo "";
        }
    }
}
?>

<?php
/**
 * @var string $type 'submit' or 'link'
 * @var string $variant 'primary' | 'danger' | 'warning' | 'secondary'
 * @var string $text ボタンの文字
 * @var string $href linkの場合の遷移先
 * @var string $onclick onclickイベント
 */
$class = "btn btn-" . ($variant ?? 'primary');
?>

<?php if (($type ?? 'submit') === 'link'): ?>
    <a href="<?php echo h($href ?? '#'); ?>" class="<?php echo $class; ?>" style="text-align: center;">
        [ <?php echo h($text); ?> ]
    </a>
<?php else: ?>
    <button type="submit" class="<?php echo $class; ?>" onclick="<?php echo h($onclick ?? ''); ?>">
        <?php echo h($text); ?>
    </button>
<?php endif; ?>