# 1️⃣ Base PHP
FROM php:8.2-cli

# 2️⃣ Définir le dossier de travail
WORKDIR /var/www

# 3️⃣ Installer dépendances système et extensions PHP nécessaires
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    zip \
    curl \
    && docker-php-ext-install pdo pdo_pgsql

# 4️⃣ Copier tout le projet
COPY . .

# 5️⃣ Installer Composer
RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

# 6️⃣ Installer les dépendances Laravel
RUN composer install --no-dev --optimize-autoloader

# 7️⃣ Permissions correctes pour Laravel
RUN chmod -R 777 storage bootstrap/cache

# 8️⃣ Commande de démarrage
# 👉 Lance les migrations
# 👉 Démarre le serveur Laravel avec php -S (RECOMMANDÉ SUR RENDER)
CMD php artisan migrate --force && php -S 0.0.0.0:$PORT -t public/
