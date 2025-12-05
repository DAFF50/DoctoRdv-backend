FROM php:8.2-cli

WORKDIR /var/www

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    zip \
    curl \
    && docker-php-ext-install pdo pdo_pgsql

# Copier seulement les fichiers Composer d'abord
COPY composer.json composer.lock ./

# Installer les dépendances
RUN curl -sS https://getcomposer.org/installer | php && \
    php composer.phar install --no-dev --optimize-autoloader

# Puis copier le reste de l’application
COPY . .

# Donner les permissions
RUN chmod -R 777 storage bootstrap/cache

# Optimisations Laravel
RUN php artisan config:cache
RUN php artisan route:cache

# Port pour Render (informatif)
EXPOSE 10000

# Lancer Laravel sur le port de Render
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT}
