<?php
require __DIR__ . '/layouts/header.php';
use App\Utils\View;
?>
<div class="container">
    <div class="box">
        <?php if (!isset($_SESSION['logged_in'])): ?>
            <div style="display: flex; justify-content: flex-end; gap: 15px; align-items: center;">
                <div style="text-align: left; margin-right: auto;">
                    <span style="color: #1eff1a; font-weight: bold;">GUEST MODE</span>
                    <p style="margin: 0; color: #888; font-size: 0.8em;">データの閲覧が可能です。編集にはサインアップ・ログインが必要です。</p>
                </div>
                <a href="/auth/login" class="btn btn-secondary" style="min-width: 100px; text-align: center;">ログイン</a>
                <a href="/register" class="btn btn-primary" style="min-width: 100px; text-align: center;">サインアップ</a>
            </div>
        <?php else: ?>
            <div class="u-flex-column" style="gap: 15px;">
                <div class="user-info-bar u-flex-between">
                    <div style="text-align: left;">
                        <span style="color: var(--cyber-green); font-family: monospace; font-size: 1.1em;">
                            SYSTEM_USER: <strong style="color: #fff;"><?php echo h($_SESSION['login_id']); ?></strong>
                        </span>
                    </div>

                    <div style="text-align: right; font-size: 0.9em;">
                        <a href="/auth/request_permission" style="color: var(--cyber-yellow); margin-right: 15px;">[権限の申請]</a>
                        <a href="/admin/password/change" style="color: #fff; margin-right: 15px;">[PW変更]</a>

                        <form action="/auth/logout" method="post" style="display:inline;">
                            <input type="hidden" name="csrf_token" value="<?php echo h($_SESSION['csrf_token']); ?>">
                            <?php View::component('button', [
                                'type' => 'submit',
                                'variant' => 'secondary',
                                'text' => 'LOGOUT',
                                'style' => 'background:none; border:none; color:var(--cyber-text-dim); text-decoration:underline; padding:0;'
                            ]); ?>
                        </form>
                    </div>
                </div>

                <div class="u-flex-between">
                    <?php if (hasPermission(PERM_DATA)): ?>
                        <?php View::component('button', [
                            'type' => 'link',
                            'variant' => 'primary',
                            'text' => '＋ 新規データ追加',
                            'href' => '/koban/create',
                            'style' => 'padding: 10px 25px;'
                        ]); ?>
                    <?php else: ?>
                        <div style="color: var(--cyber-text-dim); font-size: 0.9em;">
                            <span style="color: var(--cyber-yellow);">[!]</span> 閲覧専用モード
                        </div>
                    <?php endif; ?>

                    <div style="display: flex; gap: 10px;">
                        <?php if (hasPermission(PERM_LOG)): ?>
                            <?php View::component('button', [
                                'type' => 'link',
                                'variant' => 'secondary',
                                'text' => 'ログ監査',
                                'href' => '/admin/logs',
                                'style' => 'color: var(--cyber-blue); border-color: var(--cyber-blue);'
                            ]); ?>
                        <?php endif; ?>

                        <?php if (hasPermission(PERM_ADMIN)): ?>
                            <div class="badge-wrapper">
                                <?php View::component('button', [
                                    'type' => 'link',
                                    'variant' => 'secondary',
                                    'text' => '管理者管理',
                                    'href' => '/admin/users',
                                    'style' => 'color: var(--cyber-yellow); border-color: var(--cyber-yellow);'
                                ]); ?>
                                <?php if (($pendingCount ?? 0) > 0): ?>
                                    <span class="notification-badge">!</span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="box" style="background-color: #282828;">
        <h2 style="margin-top: 0; border-bottom: 1px solid #444; padding-bottom: 5px; font-size: 20px;">条件検索</h2>
        <form action="" method="get">
            <div class="flex-row" style="justify-content: center;">
                <input type="number" name="search_id" placeholder="ID"
                    value="<?php echo h($_GET['search_id'] ?? ''); ?>"
                    style="width: 80px; background: #fff; color: #000;" min="1">
                <select name="search_pref">
                    <option value="">都道府県 (すべて)</option>
                    <?php foreach ($pref_list as $p) echo "<option value='{$p}' " . (($_GET['search_pref'] ?? '') == $p ? 'selected' : '') . ">{$p}</option>"; ?>
                </select>
                <select name="search_type">
                    <option value="">種別 (すべて)</option>
                    <option value="交番" <?php if (($_GET['search_type'] ?? '') == '交番') echo 'selected'; ?>>交番</option>
                    <option value="駐在所" <?php if (($_GET['search_type'] ?? '') == '駐在所') echo 'selected'; ?>>駐在所</option>
                </select>
                <input type="text" name="keyword" placeholder="キーワード (例: 新宿)" value="<?php echo h($_GET['keyword'] ?? ''); ?>" style="background: #fff; color: #000;" size="30">
                <select name="sort">
                    <option value="id_asc">ID昇順</option>
                    <option value="id_desc" <?php if (($_GET['sort'] ?? '') == 'id_desc') echo 'selected'; ?>>ID降順</option>
                    <option value="name_asc" <?php if (($_GET['sort'] ?? '') == 'name_asc') echo 'selected'; ?>>名前順</option>
                </select>
                <input type="submit" value="絞り込み検索" class="btn-primary">
            </div>
            <p style="font-size: 12px; color: #888; text-align: center; margin-bottom: 0;">※スペース区切りで複数の言葉を検索できます（例：東京都 新宿）</p>
        </form>

        <div style="margin-top: 20px; text-align: right; border-top: 1px solid #444; padding-top: 10px; display: flex; justify-content: flex-end; gap: 10px;">
            <form action="/koban/export" method="get">
                <?php \App\Utils\View::component('button', [
                    'type'    => 'submit',
                    'variant' => 'secondary',
                    'text'    => '全データをCSV保存'
                ]); ?>
            </form>
            <form action="/koban/export" method="get">
                <?php foreach (['keyword', 'search_type', 'search_pref'] as $k) echo '<input type="hidden" name="' . $k . '" value="' . h($_GET[$k] ?? '') . '">'; ?>
                <?php \App\Utils\View::component('button', [
                    'type'    => 'submit',
                    'variant' => 'primary',
                    'text'    => '検索結果をCSV保存'
                ]); ?>
            </form>
        </div>
    </div>

    <div style="text-align: left; margin-bottom: 5px; font-size: 18px;">
        <?php if ($total_count > 0): ?>
            検索結果： <span style="font-weight: bold; font-size: 24px; color: #1eff1a;"><strong><?php echo number_format($total_count); ?></strong></span> 件
        <?php else: ?>
            <span style="color: #ff4444;">条件に一致するデータは見つかりませんでした。</span>
        <?php endif; ?>
    </div>

    <table>
        <tr style="background-color: #4a4a4a;">
            <th width="5%">ID</th>
            <th width="20%">交番名</th>
            <th width="5%">種別</th>
            <th width="10%">TEL</th>
            <th width="5%">団体コード</th>
            <th width="8%">郵便番号</th>
            <th>住所</th>
            <?php if (isset($_SESSION['logged_in'])): ?><th width="10%" style="background-color: #550000;">操作</th><?php endif; ?>
        </tr>
        <?php foreach ($all_data as $row): ?>
            <tr>
                <td style="text-align: center;"><?php echo h($row['id']); ?></td>
                <td><?php echo h($row['koban_fullname']); ?></td>
                <td style="text-align: center;"><?php echo h($row['type']); ?></td>
                <td style="text-align: center;"><?php echo h($row['phone_number']); ?></td>
                <td style="text-align: center;"><?php echo h($row['group_code']); ?></td>
                <td style="text-align: center;"><?php echo h($row['postal_code']); ?></td>

                <td>
                    <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($row['pref'] . $row['addr3']); ?>"
                        target="_blank"
                        style="color: #1eff1a; text-decoration: underline;"
                        title="Googleマップで見る">
                        <?php echo h($row['pref'] . $row['addr3']); ?>
                    </a>
                </td>

                <?php if (isset($_SESSION['logged_in'])): ?>
                    <td style="text-align: center;">
                        <?php if (hasPermission('data')): ?>
                            <div style="display: flex; justify-content: center; gap: 5px;">
                                <?php \App\Utils\View::component('button', [
                                    'type'    => 'link',
                                    'variant' => 'warning',
                                    'text'    => '編集',
                                    'href'    => '/koban/edit?id=' . h($row['id']),
                                    'style'   => 'padding: 4px 8px; font-size:12px;'
                                ]); ?>
                                <form method="post" action="/koban/delete" onsubmit="return confirm('本当に削除しますか？');">
                                    <input type="hidden" name="csrf_token" value="<?php echo h($_SESSION['csrf_token']); ?>">
                                    <input type="hidden" name="delete_id" value="<?php echo h($row['id']); ?>">
                                    <?php \App\Utils\View::component('button', [
                                        'type'    => 'submit',
                                        'variant' => 'danger',
                                        'text'    => '削除',
                                        'style'   => 'padding: 4px 8px; font-size:12px;'
                                    ]); ?>
                                </form>
                            </div>
                        <?php else: ?>
                            <span style="font-size: 10px; color: #888;">操作不可</span>
                        <?php endif; ?>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
    </table>

    <div class="pagination" style="margin-top: 20px;">
        <?php
        $q = http_build_query(array_filter($_GET, fn($k) => $k != 'page', ARRAY_FILTER_USE_KEY));
        $link = "?$q&page=";
        if ($page > 1) {
            echo '<a href="' . $link . '1" class="other">&laquo; 最初</a>';
            echo '<a href="' . $link . ($page - 1) . '" class="other">&lsaquo; 前へ</a>';
        }
        for ($i = max(1, $page - 3); $i <= min($total_pages, $page + 3); $i++) {
            $class = ($i == $page) ? 'current' : 'other';
            echo '<a href="' . $link . $i . '" class="' . $class . '">' . $i . '</a>';
        }
        if ($page < $total_pages) {
            echo '<a href="' . $link . ($page + 1) . '" class="other">次へ &rsaquo;</a>';
            echo '<a href="' . $link . $total_pages . '" class="other">最後 &raquo;</a>';
        }
        ?>
    </div>
    <p style="color: #888; font-size: 12px; margin-top: 5px;">
        全 <?php echo $total_count; ?> 件中、<?php echo $page; ?> ページ目を表示
    </p>
</div>
</body>

</html>