<?php

namespace App\Utils;

class View
{
    /**
     * ビューファイルを読み込んで表示する
     * * @param string $viewName ビュー名 (例: 'home' や 'koban/form')
     * @param array $data ビューに渡したいデータの配列
     */
    public static function render($viewName, $data = [])
    {
        // 配列のキーを変数名として展開 (例: ['user' => 'Mike'] → $user = 'Mike')
        extract($data);

        // ビューファイルのパスを構築 (viewsフォルダ配下を探す)
        $filePath = __DIR__ . '/../../views/' . $viewName . '.php';

        if (file_exists($filePath)) {
            require $filePath;
        } else {
            // ファイルがない場合のエラー処理（開発用）
            echo "View file not found: " . htmlspecialchars($viewName);
        }
    }
}
