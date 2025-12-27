FROM php:8.2-apache

# 必要なライブラリと PostgreSQL 用の拡張をインストール
RUN apt-get update && apt-get install -y \
    unzip git libsqlite3-dev libpq-dev \
    && docker-php-ext-install pdo_sqlite pdo_pgsql

RUN a2enmod rewrite
# Renderのポート対応
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .
RUN composer install --no-dev --optimize-autoloader

CMD ["apache2-foreground"]