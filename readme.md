# R6 交番・駐在所検索データベース
(Koban Search App)

このプロジェクトは、全国の交番および駐在所の情報を検索・管理するためのWebアプリケーションであり、PHP 8.2とDocker環境をベースにしています。DBに関する知識の学習を目的としています。

---

#### データ出典：[e-Gov データポータル - 交番・駐在所オープンデータ](https://data.e-gov.go.jp/data/ja/dataset/04b81179-879c-40e7-ab86-9f097ffac49d/resource/18ce28d1-e3b6-43dd-a3c9-6d4b767bace0?inner_span=True&activity_id=7f92bdf3-a897-4a1e-88d5-59755ea7d37b)

## 🚀 主な機能

### 🔍 検索・閲覧
* **条件検索**: ID、都道府県、種別（交番/駐在所）、キーワード（名称や住所）による絞り込み。
* **ページネーション**: 大量データの快適な閲覧（1ページ100件表示）。
* **外部連携**: 住所からGoogleマップへのダイレクトリンク機能。

### 🔐 管理者・セキュリティ
* **権限管理**: 「データ管理」「アカウント管理」「ログ閲覧」の3段階のパーミッション設定。
* **認証システム**: 安全なパスワードハッシュ（`password_hash`）とセッション管理。
* **CSRF対策**: すべてのPOSTリクエストに対するトークン検証。
* **操作ログ監査**: システム内の主要な操作を記録し、管理者パネルからリアルタイムで確認可能。

### 📊 データ管理
* **CRUD操作**: ブラウザ上での新規登録、編集、削除。
* **CSVインポート**: 外部データの一括取り込み機能（UPSERT対応）。
* **CSVエクスポート**: 検索結果または全データのバックアップ。

---

## 🛠 技術スタック

* **Language**: PHP 8.2 (Apache)
* **Architecture**: MVC（Model-View-Controller）パターン
* **Database**:
    * 本番環境: PostgreSQL (Render等での運用を想定)
    * ローカル環境: SQLite
* **Environment**: Docker / Docker Compose
* **Frontend**: Vanilla JS (Real-time Log API), CSS3 (Cyber-style Green UI)
* **Dependencies**: 
    * `phpdotenv`: 環境変数管理
    * `composer`: ライブラリ管理

---

## 📁 ディレクトリ構造

```text
.
├── Dockerfile              # Dockerイメージ定義
├── docker-compose.yml      # コンテナ構成管理
├── index.php               # エントリポイント（public/index.phpへ委譲）
├── public/                 # 公開ディレクトリ（静的ファイル、フロントコントローラ）
│   ├── css/                # スタイルシート
│   └── index.php           # ルーティング処理
├── src/                    # アプリケーションロジック
│   ├── Controllers/        # 各画面の制御（Auth, Admin, Koban）
│   ├── Models/             # データベース操作（Koban, AdminUser, AuditLog）
│   └── Utils/              # 共通ユーティリティ（Database, Cache, Validator）
├── views/                  # UIテンプレート（PHP/HTML）
│   ├── admin/              # 管理者用画面
│   ├── koban/              # データ管理画面
│   └── layouts/            # 共通ヘッダー等
├── SQL/                    # データベース関連ファイル（SQLite等）
└── storage/                # 書き込み可能ディレクトリ（Cache用）
```

## ⚡ 起動手順（ローカル開発環境）

1. 準備
Docker Desktop がインストール・起動されていることを確認してください。

2. 環境変数の設定
プロジェクトルートに .env ファイルを作成します。

コード スニペット

* ローカルでSQLiteを使用する場合は空欄、PostgreSQLを使用する場合はURLを記述

DATABASE_URL=

3. コンテナのビルド・起動
ターミナルで以下のコマンドを実行します。

```Bash
docker-compose up -d --build
```

4. ブラウザでアクセス
以下のURLを開きます。 http://localhost:8080

## 🔧 開発・運用メモ
- キャッシュ機能: src/Utils/Cache.php により、検索結果や件数取得をファイルベースでキャッシュし、DB負荷を軽減しています。

- リアルタイムログ: views/admin/log_list.php では JavaScript の fetch を用いて、ページ遷移なしで最新の操作ログを自動更新表示します。

- デプロイ: Dockerfile は Render 等の PaaS 環境（$PORT 環境変数対応）へのデプロイを考慮して設計されています。

📝 ライセンス
このプロジェクトの取り扱いについては、管理者に確認してください。