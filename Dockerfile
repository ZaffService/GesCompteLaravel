# Utiliser l'image PHP officielle avec FPM
FROM php:8.3-fpm

# Installer les dépendances système
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libpq-dev \
    zip \
    unzip \
    postgresql-client \
    redis-tools \
    nginx \
    supervisor \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip pdo_pgsql

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Créer l'utilisateur www-data avec le bon UID/GID
RUN usermod -u 1000 www-data && groupmod -g 1000 www-data

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier les fichiers de l'application
COPY . /var/www/html

# Copier le fichier .env AVANT tout
COPY .env.production .env

# Changer les permissions avant l'installation des dépendances
RUN chown -R www-data:www-data /var/www/html

# Installer les dépendances PHP en tant que www-data
USER www-data
RUN composer install --optimize-autoloader --no-dev --no-interaction --no-scripts

# Créer les répertoires nécessaires pour Swagger
RUN mkdir -p storage/api-docs

# Générer les clés d'application
RUN php artisan key:generate

# Générer la documentation Swagger SANS cache
RUN php artisan l5-swagger:generate

# MAINTENANT faire le cache (après Swagger)
RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

# Revenir à root pour la configuration système
USER root

# Créer les répertoires nécessaires et configurer les permissions
RUN chown -R www-data:www-data storage && \
    chmod -R 775 storage && \
    chmod -R 775 bootstrap/cache

# Créer les répertoires système nécessaires
RUN mkdir -p /var/log/supervisor /var/run /var/log/nginx /var/cache/nginx

# Copier la configuration Nginx
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/default.conf /etc/nginx/sites-available/default

# Copier la configuration Supervisor
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copier le script de démarrage
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# Définir les permissions finales
RUN chown -R www-data:www-data /var/www/html

# Exposer le port 80
EXPOSE 80

# Script de démarrage
CMD ["/usr/local/bin/start.sh"]