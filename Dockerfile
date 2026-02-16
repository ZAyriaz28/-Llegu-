FROM php:8.2-apache

# Instalar dependencias
RUN apt-get update && apt-get install -y git unzip

# Instalar extensiones PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar proyecto
COPY . /var/www/html/

WORKDIR /var/www/html

# Instalar PHPMailer
RUN composer require phpmailer/phpmailer

# Permisos
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80