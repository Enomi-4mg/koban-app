# PHP 8.2 と Apache (Webサーバー) の公式イメージを使う
FROM php:8.2-apache

# SQLiteに必要なライブラリを追加し、pdo_sqliteをインストール
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    libsqlite3-dev \
    && docker-php-ext-install pdo_sqlite

# URLをきれいにする機能（mod_rewrite）を有効化
RUN a2enmod rewrite

# Composer（ライブラリ管理ツール）も最初から入れておく
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer