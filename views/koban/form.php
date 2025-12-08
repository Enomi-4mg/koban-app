<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <div style="text-align: left; margin-bottom: 15px;">
        <a href="/" class="btn btn-secondary">← 一覧に戻る</a>
    </div>

    <?php if ($message): ?>
        <div style="color: #ff4444; margin-bottom: 15px; font-weight: bold; border: 1px solid red; padding: 10px;">
            <?php echo h($message); ?>
        </div>
    <?php endif; ?>

    <div class="box">
        <h2 style="margin-top:0; border-bottom: 1px solid #444; padding-bottom: 10px;">
            <?php echo h($page_title); ?>
        </h2>

        <form method="post" action="/koban/store" class="h-adr" style="display: flex; flex-direction: column; gap: 15px; text-align: left;">
            <input type="hidden" name="csrf_token" value="<?php echo h($_SESSION['csrf_token']); ?>">
            <input type="hidden" name="save_data" value="1">
            <span class="p-country-name" style="display:none;">Japan</span>

            <?php if (!empty($edit_data['id'])): ?>
                <input type="hidden" name="update_id" value="<?php echo h($edit_data['id']); ?>">
                <p style="color: #888; font-size: 12px;">ID: <?php echo h($edit_data['id']); ?> を編集中</p>
            <?php endif; ?>

            <div>
                <label>交番名 <span style="color: #ff4444;">*</span></label>
                <input type="text" name="new_name" placeholder="例: 明治大学前交番" required
                    value="<?php echo h($edit_data['koban_fullname'] ?? ''); ?>" style="width: 100%;">
            </div>

            <div>
                <label>種別</label><br>
                <?php $cur = $edit_data['type'] ?? '交番'; ?>
                <label><input type="radio" name="new_type" value="交番" <?php if ($cur == '交番') echo 'checked'; ?>> 交番</label>
                <label style="margin-left: 15px;"><input type="radio" name="new_type" value="駐在所" <?php if ($cur == '駐在所') echo 'checked'; ?>> 駐在所</label>
            </div>

            <div>
                <label>電話番号</label>
                <div style="display: flex; gap: 5px; align-items: center;">
                    <input type="text" name="phone_part1" value="<?php echo h($phone_parts[0] ?? ''); ?>" size="6"> -
                    <input type="text" name="phone_part2" value="<?php echo h($phone_parts[1] ?? ''); ?>" size="6"> -
                    <input type="text" name="phone_part3" value="<?php echo h($phone_parts[2] ?? ''); ?>" size="6">
                </div>
            </div>

            <div>
                <label>全国地方公共団体コード</label>
                <input type="tel" name="new_group_code" value="<?php echo h($edit_data['group_code'] ?? ''); ?>" style="width: 100%;">
            </div>

            <div>
                <label>郵便番号 <span style="font-size:12px; color:#ffff00;">(自動入力対応)</span></label>
                <div style="display: flex; gap: 5px; align-items: center;">
                    〒 <input type="text" name="postal_part1" maxlength="3" value="<?php echo h($postal_parts[0] ?? ''); ?>" class="p-postal-code" size="5"> -
                    <input type="text" name="postal_part2" maxlength="4" value="<?php echo h($postal_parts[1] ?? ''); ?>" class="p-postal-code" size="6">
                </div>
            </div>

            <div>
                <label>都道府県 / 市区町村</label>
                <div style="display: flex; gap: 10px;">
                    <input type="text" name="new_pref" placeholder="都道府県" value="<?php echo h($edit_data['pref'] ?? ''); ?>" class="p-region" style="width: 30%;">
                    <input type="text" name="new_addr3" placeholder="市区町村以下" value="<?php echo h($edit_data['addr3'] ?? ''); ?>" class="p-locality p-street-address p-extended-address" style="width: 70%;">
                </div>
            </div>

            <div style="text-align: center; margin-top: 20px;">
                <input type="submit" value="<?php echo !empty($edit_data) ? '変更を保存' : 'データベースに追加'; ?>" class="btn-primary" style="padding: 10px 40px;">
            </div>
        </form>
    </div>

    <div class="box" style="margin-top: 30px;">
        <h3 style="margin-top: 0; color: #ccc;">CSV一括インポート</h3>

        <form method="post" action="/koban/import" enctype="multipart/form-data" style="display: flex; gap: 10px; align-items: center;">

            <input type="hidden" name="csrf_token" value="<?php echo h($_SESSION['csrf_token']); ?>">

            <input type="file" name="csv_file" accept=".csv" required style="color: #fff;">
            <input type="submit" value="インポート実行" class="btn-warning">
        </form>
        <p style="font-size: 11px; color: #888;">※列順: id, 名前, 種別, 電話, コード, 〒, 都道府県, 住所</p>
    </div>

</div>
<script src="https://yubinbango.github.io/yubinbango/yubinbango.js" charset="UTF-8"></script>
</body>

</html>