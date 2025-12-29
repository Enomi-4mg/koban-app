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
     * UIコンポーネントを安全に読み込む
     */
    public static function component($componentName, $props = [])
    {
        // EXTR_SKIP を指定することで、既存の変数（もしあれば）の上書きを防ぎつつ展開
        extract($props, EXTR_SKIP);

        $filePath = __DIR__ . '/../../views/components/' . $componentName . '.php';

        if (file_exists($filePath)) {
            include $filePath;
        } else {
            echo "";
        }
    }
}
?>

<?php
$isSuccess = preg_match('/(作成|成功|完了|変更しました)/u', $message);
$color = $isSuccess ? 'var(--cyber-green)' : 'var(--cyber-red)';
$label = $isSuccess ? '[ SUCCESS ]' : '[ ERROR ]';
?>
<div class="alert-container" style="border: 1px solid <?php echo $color; ?>; background: rgba(0,0,0,0.8); padding: 15px; margin: 10px auto; width: 80%;">
    <span style="color: <?php echo $color; ?>; font-weight: bold; text-shadow: 0 0 5px <?php echo $color; ?>;">
        <?php echo $label; ?> <?php echo h($message); ?>
    </span>
</div>