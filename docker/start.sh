#!/bin/sh

# 1. Ejecutar migraciones y caché
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 2. ¡EL TRUCO SENIOR! Devolver la propiedad de los archivos generados al usuario web
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 3. Iniciar servicios
php-fpm -D
nginx -g "daemon off;"