<?php
require __DIR__ . '/layouts/header.php';
?>

<div class="container">
    <?php if ($message): ?>
        <div style="color: #ff4444; font-weight: bold; margin-bottom:10px; border: 1px solid #ff4444; padding: 10px;"><?php echo h($message); ?></div>
    <?php endif; ?>

    <div class="box">
        <?php if (!isset($_SESSION['logged_in'])): ?>
            <form method="post" action="/auth/login" style="text-align: right;">
                <input type="hidden" name="csrf_token" value="<?php echo h($_SESSION['csrf_token']); ?>">
                <span style="color: #888; font-size: 14px;">管理者ログイン: </span>
                <input type="text" name="login_id" placeholder="ID" size="10">
                <input type="password" name="login_pass" placeholder="PASS" size="10">
                <input type="submit" value="ログイン" class="btn-primary">
            </form>
        <?php else: ?>
            <div style="border-bottom: 1px solid #444; margin-bottom: 10px; padding-bottom: 10px; text-align: right;">
                <span style="color: #1eff1aff;">ログイン中: <?php echo h($_SESSION['login_id']); ?></span> |

                <?php if (hasPermission('log')): ?>
                    <a href="/admin/logs" style="color: #00ccff;">[操作ログ監査]</a> |
                <?php endif; ?>

                <?php if (hasPermission('admin')): ?>
                    <a href="/admin/users" style="color: #ffff00;">[管理者登録]</a> |
                <?php endif; ?>

                <a href="/admin/password/change" style="color: #fff;">[PW変更]</a> |
                <form action="/auth/logout" method="post" style="display:inline;">
                    <button type="submit" style="background:none; border:none; color:#888; text-decoration:underline; cursor:pointer;">ログアウト</button>
                </form>
            </div>

            <div style="text-align: left;">
                <?php if (hasPermission('data')): ?>
                    <a href="/koban/create" class="btn-primary btn" style="padding: 10px 20px;">＋ 新規データ追加 / CSV登録</a>
                <?php else: ?>
                    <span style="color: #888;">※データ編集権限がありません</span>
                <?php endif; ?>
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
                <button type="submit" class="btn-secondary">全データをCSV保存</button>
            </form>
            <form action="/koban/export" method="get">
                <?php foreach (['keyword', 'search_type', 'search_pref'] as $k) echo '<input type="hidden" name="' . $k . '" value="' . h($_GET[$k] ?? '') . '">'; ?>
                <button type="submit" class="btn-primary" style="color: #000;">検索結果をCSV保存</button>
            </form>
        </div>
    </div>

    <div style="text-align: left; margin-bottom: 5px; font-size: 18px;">
        <?php if ($total_count > 0): ?>
            検索結果： <span style="font-weight: bold; font-size: 24px; color: #1eff1a;"><?php echo $total_count; ?></span> 件
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
                                <a href="/koban/edit?id=<?php echo h($row['id']); ?>" class="btn btn-warning" style="padding: 4px 8px; font-size:12px;">編集</a>
                                <form method="post" action="/koban/delete" onsubmit="return confirm('本当に削除しますか？');">
                                    <input type="hidden" name="csrf_token" value="<?php echo h($_SESSION['csrf_token']); ?>">
                                    <input type="hidden" name="delete_id" value="<?php echo h($row['id']); ?>">
                                    <input type="submit" value="削除" class="btn-danger" style="padding: 4px 8px; font-size:12px;">
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