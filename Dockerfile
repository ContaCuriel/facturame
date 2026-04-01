# ETAPA 1: El Constructor
FROM laravelsail/php83-composer:latest as builder
WORKDIR /var/www/html
COPY . .
RUN composer install --no-dev --no-interaction --optimize-autoloader --ignore-platform-reqs

# ETAPA 2: La Imagen Final de Producción
FROM php:8.3-fpm-bullseye
WORKDIR /var/www/html

# Instalar dependencias
RUN apt-get update && apt-get install -y \
        nginx libpq-dev gettext libzip-dev \
        libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql zip gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copiar configuraciones y archivos
COPY docker/php-uploads.ini /usr/local/etc/php/conf.d/99-uploads.ini    
COPY --from=builder /var/www/html .
COPY docker/nginx.conf /etc/nginx/nginx.conf

# Permisos
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Script de inicio
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

EXPOSE 80
CMD ["/usr/local/bin/start.sh"]