#  Utiliser l'image officielle PHP avec FPM
FROM php:8.3-fpm

# 🔧 Installer les dépendances système
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

# 🎵 Installer Composer depuis l’image officielle
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 👤 Configurer l’utilisateur www-data
RUN usermod -u 1000 www-data && groupmod -g 1000 www-data

# 📂 Définir le répertoire de travail
WORKDIR /var/www/html

# 📦 Copier les fichiers du projet
COPY . .

# 🔒 Ajuster les permissions avant installation
RUN chown -R www-data:www-data /var/www/html

# 👨‍💻 Passer à l’utilisateur www-data
USER www-data

# ⚙️ Installer les dépendances PHP sans scripts (évite erreurs Laravel)
RUN composer install --optimize-autoloader --no-dev --no-interaction --no-scripts

# 🧾 Copier le .env de production
COPY --chown=www-data:www-data .env.production .env

# 🗂️ Créer les répertoires nécessaires (Swagger, cache, storage)
RUN mkdir -p storage/api-docs bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache

# 🧼 Nettoyer toute trace de cache avant génération de clé
RUN php artisan optimize:clear || true

# 🔑 Générer la clé Laravel proprement
RUN echo "APP_KEY=base64:$(openssl rand -base64 32)" > .env.tmp && \
    cat .env >> .env.tmp && \
    mv .env.tmp .env

# 🧰 Ne PAS exécuter les commandes artisan lourdes ici
# Elles seront faites au démarrage via start.sh

# 👑 Revenir à root pour les configurations système
USER root

# 🧱 Préparer les dossiers système
RUN mkdir -p /var/log/supervisor /var/run /var/log/nginx /var/cache/nginx

# 🧩 Copier les configurations
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/default.conf /etc/nginx/sites-available/default
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# 🚀 Copier le script de démarrage
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# 🔐 Permissions finales
RUN chown -R www-data:www-data /var/www/html

# 🌐 Exposer le port 80
EXPOSE 80

# 🏁 Commande de démarrage
CMD ["/usr/local/bin/start.sh"]
