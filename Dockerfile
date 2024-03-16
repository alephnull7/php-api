FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libpq-dev \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
COPY src/ /var/www/html

RUN docker-php-ext-install pdo pdo_pgsql

COPY apache.conf /etc/apache2/sites-available/000-default.conf

RUN a2enmod rewrite

EXPOSE 80
