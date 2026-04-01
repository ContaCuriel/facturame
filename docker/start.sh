#!/bin/sh

# 1. Migrar la base de datos principal (Landlord / Companies)
php artisan migrate --force

# Nota: Si usas bases de datos separadas por cliente con Spatie, 
# descomenta la siguiente línea para migrar a todos los inquilinos:
# php artisan tenants:artisan "migrate --force"

# 2. Limpiar cachés para evitar errores de vistas viejas
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 3. Iniciar PHP-FPM en segundo plano
php-fpm -D

# 4. Iniciar Nginx en primer plano (mantiene el contenedor vivo)
nginx -g "daemon off;"