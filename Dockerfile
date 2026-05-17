# --- Fáze 1: Základní vrstva (Base) ---
FROM php:8.3-cli AS base
# Instalace základních závislostí a rozšíření pro databázi
RUN apt-get update && apt-get install -y unzip curl \
    && docker-php-ext-install pdo pdo_mysql
WORKDIR /app

# DEV
FROM base AS dev
# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
# Xdebug pro měření Code Coverage
RUN pecl install xdebug && docker-php-ext-enable xdebug
# Ve vývoji nepotřebujeme omezovat práva (zůstává root)
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]

# PRODUKCE
FROM base AS builder
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock* ./
# Instalace pouze produkčních závislostí (bez PHPUnit atd.), zrychlí a zmenší to image
RUN composer install --no-dev --optimize-autoloader --no-interaction
COPY . .

# --- Fáze 4: Finální produkční obraz (Prod - čistý a bezpečný) ---
FROM base AS prod
# Vytvoření bezpečného ne-root uživatele (bod ze zadání)
RUN useradd -m appuser

# Zkopírování hotové čisté aplikace z builderu
COPY --from=builder /app /app

# Změna práv na ne-root uživatele
RUN chown -R appuser:appuser /app
USER appuser

# Healthcheck: K8s nebo Docker se sem zeptá, jestli server žije (bod ze zadání)
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
  CMD curl -f http://localhost:8000/ || exit 1

EXPOSE 8000
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]