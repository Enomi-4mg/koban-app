<?php

namespace App\Utils;

class Cache
{
    // Render上の書き込み可能な一時ディレクトリを指定
    private static $cacheDir = __DIR__ . '/../../storage/cache/';
    private static $defaultTtl = 600; // 10分間有効

    /**
     * キャッシュを取得
     */
    public static function get($key)
    {
        $path = self::getPath($key);
        if (!file_exists($path)) return null;

        $data = unserialize(file_get_contents($path));

        // 有効期限チェック
        if (time() > $data['expires']) {
            unlink($path); // 期限切れなら削除
            return null;
        }
        return $data['content'];
    }

    /**
     * キャッシュを保存
     */
    public static function set($key, $content, $ttl = null)
    {
        if (!is_dir(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0755, true);
        }
        $ttl = $ttl ?? self::$defaultTtl;
        $data = [
            'expires' => time() + $ttl,
            'content' => $content
        ];
        // 高速化のため、排他ロック(LOCK_EX)をかけて書き込む
        file_put_contents(self::getPath($key), serialize($data), LOCK_EX);
    }

    /**
     * 全キャッシュを削除（データ更新時に使用）
     */
    public static function clear()
    {
        if (!is_dir(self::$cacheDir)) return;
        $files = glob(self::$cacheDir . '*.cache');
        foreach ($files as $file) {
            if (is_file($file)) unlink($file);
        }
    }

    /**
     * キー（検索条件）をハッシュ化してファイルパスを生成
     */
    private static function getPath($key)
    {
        // アルゴリズム的な視点：長い検索条件を固定長のファイル名にするためMD5を使用
        return self::$cacheDir . md5($key) . '.cache';
    }
}
?>