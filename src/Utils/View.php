<?php

namespace App\Utils;

class View
{
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

    public static function component($componentName, $props = [])
    {
        // 渡された変数を展開。存在しない変数は null として扱う
        extract($props, EXTR_SKIP);

        $filePath = __DIR__ . '/../../views/components/' . $componentName . '.php';

        if (file_exists($filePath)) {
            include $filePath;
        }
    }
}
