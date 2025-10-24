# Dockerfile para Algorix (PHP-FPM)
FROM php:8.2-fpm

# Instalar dependencias del sistema y extensiones necesarias
RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libonig-dev libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql \
    && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Configurar directorio de trabajo
WORKDIR /var/www/html

# Copiar manifiestos de Composer para cache de dependencias
COPY composer.json ./

# Instalar dependencias (producción, autoload optimizado)
RUN composer install --no-interaction --prefer-dist --no-dev --optimize-autoloader

# Copiar el resto del proyecto
COPY . /var/www/html

# Ajustes de permisos (opcional)
RUN chown -R www-data:www-data /var/www/html

# Exponer el puerto FPM
EXPOSE 9000

CMD ["php-fpm"]