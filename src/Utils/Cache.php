<?php

namespace App\Utils;

class Cache
{
    private static $cacheDir = __DIR__ . '/../../storage/cache/';
    private static $defaultTtl = 600;

    /**
     * キャッシュを取得
     * @param string $key 検索キー
     * @return mixed 内容のみを返す
     */
    public static function get($key)
    {
        $path = self::findPath($key);
        if (!$path || !file_exists($path)) return null;

        $data = unserialize(file_get_contents($path));

        // 有効期限チェック
        if (time() > $data['metadata']['expires']) {
            unlink($path);
            return null;
        }

        return $data['content'];
    }

    /**
     * キャッシュを保存（メタデータ付き）
     * @param string $key 検索キー
     * @param mixed $content 保存内容
     * @param string $tag 識別タグ（例: 'koban_list'）
     */
    public static function set($key, $content, $tag = 'default', $ttl = null)
    {
        if (!is_dir(self::$cacheDir)) mkdir(self::$cacheDir, 0755, true);

        $ttl = $ttl ?? self::$defaultTtl;
        $hash = md5($key);
        // [タグ]__[ハッシュ].cache という形式で保存
        $path = self::$cacheDir . "{$tag}__{$hash}.cache";
        $tmpPath = $path . '.' . uniqid(mt_rand(), true) . '.tmp';

        $data = [
            'metadata' => [
                'tag' => $tag,
                'expires' => time() + $ttl,
                'created_at' => date('Y-m-d H:i:s'),
                'php_version' => PHP_VERSION,
                'server' => gethostname() // Renderのインスタンス識別用
            ],
            'content' => $content
        ];

        if (file_put_contents($tmpPath, serialize($data), LOCK_EX) !== false) {
            rename($tmpPath, $path);
        }
    }

    /**
     * 特定のタグを持つキャッシュのみを削除（ピンポイント無効化）
     */
    public static function clearByTag($tag)
    {
        $files = glob(self::$cacheDir . "{$tag}__*.cache");
        foreach ($files as $file) {
            if (is_file($file)) unlink($file);
        }
    }

    /**
     * キーから既存のキャッシュファイルパスを探す
     */
    private static function findPath($key)
    {
        $hash = md5($key);
        $files = glob(self::$cacheDir . "*__{$hash}.cache");
        return !empty($files) ? $files[0] : null;
    }
}
