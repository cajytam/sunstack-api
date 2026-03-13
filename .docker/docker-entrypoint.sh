#!/bin/sh
set -e

mkdir -p /app/var/log /app/var/cache/mpdf /app/var/sessions
chown -R www-data:www-data /app/var
chmod -R 775 /app/var

exec apache2-foreground
