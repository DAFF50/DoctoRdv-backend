# Base PHP CLI
FROM php:8.2-cli

# Définir le répertoire de travail
WORKDIR /var/www

# Installer dépendances système et extensions PHP
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    zip \
    curl \
    && docker-php-ext-install pdo pdo_pgsql

# Installer Composer proprement depuis l'image officielle
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copier uniquement les fichiers nécessaires pour composer
COPY composer.json composer.lock artisan ./

# Installer les dépendances Laravel
RUN composer install --no-dev --optimize-autoloader

# Copier le reste du projet
COPY . .

# Donner les permissions correctes pour Laravel
RUN chmod -R 777 storage bootstrap/cache

# Optimisations Laravel
RUN php artisan config:cache
RUN php artisan route:cache

# Exposer un port (informative)
EXPOSE 8000

# Lancer les migrations puis démarrer le serveur Laravel
CMD sh -c "php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT}"
