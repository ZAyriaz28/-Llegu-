FROM php:8.2-apache

# Instalar dependencias necesarias
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    libzip-dev \
    && docker-php-ext-install mysqli pdo pdo_mysql zip

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar proyecto
COPY . /var/www/html/

# Entrar al proyecto e instalar PHPMailer
WORKDIR /var/www/html
RUN composer require phpmailer/phpmailer

# Permisos
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80