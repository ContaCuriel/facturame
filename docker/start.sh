#!/bin/sh

# 1. Migraciones y caché (Le quitamos el caché de configuración por ahora para evitar problemas con las variables .env)
php artisan migrate --force
php artisan route:cache
php artisan view:cache

# 2. Permisos
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 3. MODO DEBUG: Crear el archivo de log si no existe y enviarlo a la consola de Render
touch /var/www/html/storage/logs/laravel.log
chmod 777 /var/www/html/storage/logs/laravel.log
tail -f /var/www/html/storage/logs/laravel.log &

# 4. Iniciar servicios
php-fpm -D
nginx -g "daemon off;"