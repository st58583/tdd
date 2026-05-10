FROM php:8.3-cli

# Instalace systémových závislostí a PHP rozšíření (PDO pro DB)
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo pdo_mysql zip

# Instalace a povolení Xdebug (nutné pro Code Coverage)
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Konfigurace Xdebug pro coverage
RUN echo "xdebug.mode=coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Instalace Composeru
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app