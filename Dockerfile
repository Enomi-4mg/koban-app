# 1. ベースイメージの指定
FROM php:8.2-apache

# 2. 必要なライブラリとPostgreSQL/SQLite拡張をインストール
RUN apt-get update && apt-get install -y \
    unzip git libsqlite3-dev libpq-dev \
    && docker-php-ext-install pdo_sqlite pdo_pgsql

# 3. Apacheの設定変更 (403対策の肝)
# ApacheのDocumentRootを /var/www/html に設定し、.htaccessを有効化します
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# 4. Renderの動的ポート設定に対応
# Renderが割り当てる $PORT 環境変数でApacheが待ち受けるようにします
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# 5. mod_rewrite (URL書き換え) を有効化
RUN a2enmod rewrite

# 6. Composer のインストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 7. 作業ディレクトリの設定とファイルのコピー
WORKDIR /var/www/html
COPY . .

# 8. 権限の設定
# キャッシュディレクトリを作成し、所有者を www-data に変更
RUN mkdir -p /var/www/html/storage/cache \
    && chown -R www-data:www-data /var/www/html/storage \
    && chmod -R 775 /var/www/html/storage

# 9. 依存関係のインストール
RUN composer install --no-dev --optimize-autoloader

# 10. Apacheの起動
CMD ["apache2-foreground"]