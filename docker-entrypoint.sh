#!/bin/bash
# 起動時に権限を強制修正
chown -R www-data:www-data /var/www/html/storage /var/www/html/SQL
chmod -R 777 /var/www/html/storage /var/www/html/SQL

# 元の起動コマンド（Apache）を実行
exec apache2-foreground