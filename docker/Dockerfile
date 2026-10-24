# Ambiente de desenvolvimento do dotProject BR: PHP 8.3 + Apache (igual à hospedagem).
FROM php:8.3-apache
RUN apt-get update && apt-get install -y --no-install-recommends libpng-dev libjpeg-dev libfreetype6-dev libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install mysqli gd zip \
    && rm -rf /var/lib/apt/lists/*
RUN { echo 'error_reporting = E_ALL'; echo 'display_errors = Off'; echo 'log_errors = On'; echo 'error_log = /var/log/php/erros.log'; echo 'date.timezone = America/Sao_Paulo'; echo 'memory_limit = 256M'; } > /usr/local/etc/php/conf.d/dotproject.ini \
    && mkdir -p /var/log/php && chown www-data /var/log/php
