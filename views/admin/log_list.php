<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>操作ログ監査</title>
    <link rel="stylesheet" href="../../public/css/style-log.css">
</head>

<body>

    <h2>システム操作ログ監査 <span id="live-indicator" title="リアルタイム更新待機中..."></span></h2>
    <div style="width: 90%; margin: 0 auto; text-align: left;">
        <a href="/" class="btn btn-back">← 検索画面に戻る</a>
    </div>

    <form method="get" class="search-box" id="searchForm">
        <div>
            <label>ユーザーID:</label>
            <input type="text" name="filter_user" value="<?php echo h($_GET['filter_user'] ?? ''); ?>" size="10" placeholder="部分一致">
        </div>
        <div>
            <label>操作:</label>
            <select name="filter_action">
                <option value="">(すべて)</option>
                <option value="AUTH_SET" style="color: #00ccff; font-weight: bold;" <?php if (($_GET['filter_action'] ?? '') === 'AUTH_SET') echo 'selected'; ?>>
                    【 認証・セッション関連 】
                </option>
                <option disabled>----------------</option>
                <?php foreach ($types as $t): ?>
                    <option value="<?php echo h($t); ?>" <?php if (($_GET['filter_action'] ?? '') === $t) echo 'selected'; ?>>
                        <?php echo h($t); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-search">検索</button>
        <a href="/admin/logs" class="btn-reset">リセット</a>
        <button type="submit" name="download_csv" value="1" class="btn btn-csv">結果をCSV保存</button>
    </form>

    <div style="width: 95%; margin: 0 auto; text-align: left; margin-bottom: 5px;">
        該当件数: <span style="font-weight: bold; font-size: 1.2em;" id="count-display"><?php echo count($logs); ?></span> 件 (最大500件)
    </div>

    <table id="logTable">
        <thead>
            <tr>
                <th width="5%">No.</th>
                <th width="5%">DB-ID</th>
                <th width="15%">日時</th>
                <th width="10%">ユーザー</th>
                <th width="10%">操作</th>
                <th width="40%">詳細</th>
                <th width="15%">環境情報</th>
            </tr>
        </thead>
        <tbody id="logTableBody">
            <?php if (empty($logs)): ?>
                <tr id="no-logs-row">
                    <td colspan="7" style="text-align: center; padding: 20px; color: #888;">ログが見つかりません</td>
                </tr>
            <?php else: ?>
                <?php $row_num = count($logs); ?>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td class="num-col"><?php echo $row_num--; ?></td>
                        <td style="text-align:center; color:#666; font-size:0.9em;"><?php echo h($log['id']); ?></td>
                        <td><?php echo h($log['action_time']); ?></td>
                        <td><?php echo h($log['user_id']); ?></td>
                        <td style="font-weight: bold; text-align: center;">
                            <?php
                            $act = h($log['action_type']);
                            if (strpos($act, '削除') !== false || strpos($act, '失敗') !== false || strpos($act, 'エラー') !== false || strpos($act, '拒否') !== false) {
                                echo "<span class='danger'>$act</span>";
                            } elseif (strpos($act, 'ログイン') !== false) {
                                echo "<span class='login'>$act</span>";
                            } else {
                                echo $act;
                            }
                            ?>
                        </td>
                        <td><?php echo h($log['details']); ?></td>
                        <td style="font-size: 12px; color: #888;">
                            <div>IP: <?php echo h($log['ip_address']); ?></div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <script>
        // PHPから渡された最新のIDを初期値としてセット
        let latestId = <?php echo (int)$latest_id; ?>;

        // 1.5秒ごとに新しいログを取りに行くタイマーを開始
        setInterval(fetchNewLogs, 1500);

        function fetchNewLogs() {
            // 検索フォームに入力されている条件を取得
            const formData = new FormData(document.getElementById('searchForm'));
            const params = new URLSearchParams(formData);

            // 「今の最新IDより後のデータ」をください、というパラメータを追加
            params.append('last_id', latestId);

            // ★修正ポイント: URLを新しいAPIエンドポイントに変更
            fetch('/admin/api/logs?' + params.toString())
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    // エラーが返ってきた場合はログに出して終了
                    if (data.error) {
                        console.error('API Error:', data.error);
                        return;
                    }

                    // 新しいログがあれば画面に追加
                    if (data.length > 0) {
                        updateTable(data);
                    }

                    // 動作確認用の「緑の点」をピカッとさせる
                    flashIndicator();
                })
                .catch(error => console.error('Fetch error:', error));
        }

        // テーブルを更新する関数
        function updateTable(newLogs) {
            const tbody = document.getElementById('logTableBody');
            const noLogsRow = document.getElementById('no-logs-row');
            const countDisplay = document.getElementById('count-display');

            // 「ログが見つかりません」の表示があれば消す
            if (noLogsRow) {
                noLogsRow.remove();
            }

            // 受け取ったログ配列（降順）を逆転させて、古い順にする
            // これにより、forEachで順番に「先頭へ挿入」していくと、最終的に正しい順序（最新が一番上）になる
            newLogs.slice().reverse().forEach(log => {
                // 最新IDを更新（次のリクエスト用）
                if (parseInt(log.id) > latestId) {
                    latestId = parseInt(log.id);
                }

                // HTMLの行を作成
                const row = document.createElement('tr');
                row.className = 'new-log'; // CSSアニメーション（黄色く光る）を適用

                // 操作種別の色分け（PHP側と同じロジック）
                let actionHtml = log.action_type;
                if (log.action_type.match(/削除|失敗|エラー|拒否/)) {
                    actionHtml = `<span class='danger'>${log.action_type}</span>`;
                } else if (log.action_type.match(/ログイン/)) {
                    actionHtml = `<span class='login'>${log.action_type}</span>`;
                }

                // ▼▼▼ 修正箇所: 1行目の td を "NEW" に変更 ▼▼▼
                row.innerHTML = `
                    <td class="num-col" style="color: #ffff00; font-weight: bold; font-size: 0.8em;">NEW</td>
                    <td style="text-align:center; color:#666; font-size:0.9em;">${log.id}</td>
                    <td>${log.action_time}</td>
                    <td>${log.user_id}</td>
                    <td style="font-weight: bold; text-align: center;">${actionHtml}</td>
                    <td>${log.details}</td>
                    <td style="font-size: 12px; color: #888;">
                        <div>IP: ${log.ip_address}</div>
                    </td>
                `;
                // ▲▲▲ 修正ここまで ▲▲▲

                // テーブルの一番上に追加
                tbody.insertAdjacentElement('afterbegin', row);
            });

            // 件数表示を更新
            if (countDisplay) {
                countDisplay.textContent = tbody.rows.length;
            }
        }

        // 通信インジケーターのアニメーション
        function flashIndicator() {
            const indicator = document.getElementById('live-indicator');
            if (indicator) {
                indicator.classList.add('active');
                setTimeout(() => indicator.classList.remove('active'), 500);
            }
        }
    </script>
</body>

</html>