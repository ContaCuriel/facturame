FROM php:8.2-apache

# 1. Instalar dependencias del sistema (Cambiamos -n por -y que es el correcto)
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_pgsql gd zip

# 2. Habilitar mod_rewrite de Apache
RUN a2enmod rewrite

# 3. Directorio de trabajo
WORKDIR /var/www/html

# 4. Copiar archivos del proyecto
COPY . .

# 5. Instalar Composer de forma profesional
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# 6. Permisos correctos para Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 7. Configurar Apache para que apunte a /public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 8. Puerto
EXPOSE 80