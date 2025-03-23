FROM php:8.2-apache

# Instala extensões necessárias
RUN apt-get update && apt-get install -y \
    unzip \
    libicu-dev \
    libzip-dev \
    && docker-php-ext-install intl pdo_mysql zip

# Habilita o mod_rewrite do Apache para CakePHP
RUN a2enmod rewrite

# Instala o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Define o diretório de trabalho
WORKDIR /var/www/html

# Expondo a porta 80
EXPOSE 80
