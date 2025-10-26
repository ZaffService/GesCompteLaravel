# 🚀 Guide de Déploiement - API Banque Laravel 10

## 📋 Prérequis

- **Serveur Linux** (Ubuntu/Debian recommandé)
- **PHP 8.1+** avec extensions nécessaires
- **Composer** pour les dépendances PHP
- **PostgreSQL** ou **MySQL**
- **Redis** (optionnel, pour le cache)
- **Nginx** ou **Apache**
- **SSL Certificate** (Let's Encrypt recommandé)
- **Git** pour le versioning

---

## 🐳 Dockerisation de l'Application

### 1. Créer le Dockerfile

```dockerfile
# Utiliser l'image PHP officielle avec Apache
FROM php:8.1-apache

# Installer les dépendances système
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    postgresql-client \
    libpq-dev \
    redis-tools \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip pdo_pgsql

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier les fichiers de l'application
COPY . /var/www/html

# Installer les dépendances PHP
RUN composer install --optimize-autoloader --no-dev

# Définir les permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Copier la configuration Apache
COPY <<EOF /etc/apache2/sites-available/000-default.conf
<VirtualHost *:80>
    ServerName api.moustapha.seck.com
    DocumentRoot /var/www/html/public

    <Directory /var/www/html/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/api_error.log
    CustomLog \${APACHE_LOG_DIR}/api_access.log combined
</VirtualHost>
EOF

# Activer mod_rewrite
RUN a2enmod rewrite

# Exposer le port 80
EXPOSE 80

# Script de démarrage
COPY <<EOF /usr/local/bin/start.sh
#!/bin/bash
# Attendre que la base de données soit prête
until pg_isready -h db -p 5432; do
  echo "Waiting for database..."
  sleep 2
done

# Exécuter les migrations et seeders
php artisan migrate --force
php artisan db:seed --force

# Générer la documentation Swagger
php artisan l5-swagger:generate

# Démarrer Apache
apache2-foreground
EOF

RUN chmod +x /usr/local/bin/start.sh

CMD ["/usr/local/bin/start.sh"]
```

### 2. Créer le docker-compose.yml

```yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: api-banque-app
    restart: unless-stopped
    ports:
      - "80:80"
    environment:
      - APP_NAME="API Banque"
      - APP_ENV=production
      - APP_KEY=${APP_KEY}
      - APP_DEBUG=false
      - APP_URL=https://api.moustapha.seck.com

      - DB_CONNECTION=pgsql
      - DB_HOST=db
      - DB_PORT=5432
      - DB_DATABASE=banque_api
      - DB_USERNAME=${DB_USERNAME}
      - DB_PASSWORD=${DB_PASSWORD}

      - CACHE_DRIVER=redis
      - REDIS_HOST=redis
      - REDIS_PASSWORD=${REDIS_PASSWORD}
      - REDIS_PORT=6379

      - L5_SWAGGER_CONST_HOST=https://api.moustapha.seck.com/api/v1
    depends_on:
      - db
      - redis
    volumes:
      - ./storage:/var/www/html/storage
    networks:
      - app-network

  db:
    image: postgres:14
    container_name: api-banque-db
    restart: unless-stopped
    environment:
      - POSTGRES_DB=banque_api
      - POSTGRES_USER=${DB_USERNAME}
      - POSTGRES_PASSWORD=${DB_PASSWORD}
    volumes:
      - db_data:/var/lib/postgresql/data
    ports:
      - "5432:5432"
    networks:
      - app-network

  redis:
    image: redis:7-alpine
    container_name: api-banque-redis
    restart: unless-stopped
    command: redis-server --requirepass ${REDIS_PASSWORD}
    ports:
      - "6379:6379"
    networks:
      - app-network

volumes:
  db_data:

networks:
  app-network:
    driver: bridge
```

### 3. Créer le fichier .env.production

```env
APP_NAME="API Banque"
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://api.moustapha.seck.com

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=banque_api
DB_USERNAME=banque_user
DB_PASSWORD=your_secure_password

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=redis
REDIS_PASSWORD=your_redis_password
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"

# Swagger Configuration
L5_SWAGGER_GENERATE_ALWAYS=true
L5_SWAGGER_CONST_HOST=https://api.moustapha.seck.com/api/v1
```

---

## 🌐 Déploiement sur Render

### 1. Préparation du Projet

#### Générer la clé d'application
```bash
php artisan key:generate
```

#### Générer la documentation Swagger
```bash
php artisan l5-swagger:generate
```

#### Optimiser l'application
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 2. Configuration Render

#### Créer un nouveau service Web
1. Aller sur [Render Dashboard](https://dashboard.render.com)
2. Cliquer sur "New +" → "Web Service"
3. Connecter votre repository GitHub
4. Sélectionner la branche `main` ou `production`

#### Configuration du Service
```
Name: api-banque-laravel
Environment: Docker
Region: Frankfurt (Europe) # Pour proximité avec l'Afrique
Branch: main
Build Command: docker build -t api-banque .
Start Command: docker run -p $PORT:80 api-banque
```

#### Variables d'Environnement
Ajouter ces variables dans Render :

```
APP_NAME=API Banque
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://api-banque-laravel.onrender.com

DB_CONNECTION=postgresql
DATABASE_URL=postgresql://user:password@host:5432/database

CACHE_DRIVER=redis
REDIS_URL=redis://user:password@host:port

L5_SWAGGER_CONST_HOST=https://api-banque-laravel.onrender.com/api/v1
```

### 3. Configuration de la Base de Données

#### Créer une base PostgreSQL sur Render
1. Aller dans "New +" → "PostgreSQL"
2. Nommer : `api-banque-db`
3. Région : Frankfurt (Europe)
4. Copier l'URL de connexion

#### Mettre à jour les variables d'environnement
```
DATABASE_URL=postgresql://api_banque_db_user:secure_password@host:5432/api_banque_db
```

### 4. Configuration Redis (Optionnel)

#### Créer un service Redis sur Render
1. Aller dans "New +" → "Redis"
2. Nommer : `api-banque-redis`
3. Région : Frankfurt (Europe)

#### Ajouter l'URL Redis
```
REDIS_URL=redis://user:password@host:port
```

### 5. Configuration du Domaine

#### Acheter un domaine
- Aller sur un registrar (Namecheap, GoDaddy, etc.)
- Acheter `api.moustapha.seck.com`

#### Configurer DNS
Ajouter un enregistrement CNAME :
```
Type: CNAME
Name: api
Value: api-banque-laravel.onrender.com
```

#### Configurer le domaine personnalisé dans Render
1. Aller dans votre service Web
2. Settings → Custom Domain
3. Ajouter : `api.moustapha.seck.com`

### 6. Configuration SSL

Render fournit automatiquement un certificat SSL Let's Encrypt pour les domaines personnalisés.

### 7. Script de Build pour Render

Créer un fichier `render-build.sh` à la racine :

```bash
#!/bin/bash

# Installer les dépendances
composer install --optimize-autoloader --no-dev

# Copier le fichier .env
cp .env.production .env

# Générer la clé d'application
php artisan key:generate

# Exécuter les migrations
php artisan migrate --force

# Exécuter les seeders
php artisan db:seed --force

# Générer la documentation Swagger
php artisan l5-swagger:generate

# Optimiser l'application
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 8. Configuration Nginx (si nécessaire)

Si vous utilisez Nginx au lieu d'Apache, voici la configuration :

```nginx
server {
    listen 80;
    server_name api.moustapha.seck.com;
    root /var/www/html/public;

    index index.php;

    # Ajout des en-têtes de sécurité
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Content-Type-Options "nosniff";

    # Configuration charset
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        # Ajout des timeouts
        fastcgi_read_timeout 300;
        fastcgi_connect_timeout 300;
        fastcgi_send_timeout 300;
        include fastcgi_params;
    }

    # Empêcher l'accès aux fichiers cachés
    location ~ /\. {
        deny all;
        access_log off;
        log_not_found off;
    }

    error_log /var/log/nginx/api_error.log debug;
    access_log /var/log/nginx/api_access.log;
}
```

---

## 🔧 Commandes de Déploiement

### Déploiement Local avec Docker

```bash
# Construire et démarrer les conteneurs
docker-compose up -d --build

# Voir les logs
docker-compose logs -f app

# Exécuter des commandes dans le conteneur
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
```

### Déploiement sur Render

```bash
# Pousser les changements
git add .
git commit -m "feat: préparation déploiement production"
git push origin main

# Render détectera automatiquement les changements et redéploiera
```

### Vérifications Post-Déploiement

#### Tester l'API
```bash
# Tester l'endpoint de santé
curl -X GET "https://api.moustapha.seck.com/api/v1/comptes" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Accept: application/json"
```

#### Tester Swagger UI
Aller sur : `https://api.moustapha.seck.com/api/documentation`

#### Vérifier les logs
```bash
# Logs Render
# Aller dans le dashboard Render → Service → Logs
```

---

## 🔒 Sécurité

### Variables d'Environnement Sensibles
- `APP_KEY` : Générer avec `php artisan key:generate`
- `DB_PASSWORD` : Mot de passe fort pour PostgreSQL
- `REDIS_PASSWORD` : Mot de passe pour Redis
- `JWT_SECRET` : Clé secrète pour les tokens JWT

### Configuration CORS
```php
// config/cors.php
'allowed_origins' => ['https://front.moustapha.seck.com'],
'allowed_headers' => ['*'],
'allowed_methods' => ['*'],
'supports_credentials' => true,
```

### Rate Limiting
```php
// Dans les routes
Route::middleware(['throttle:api'])->group(function () {
    // Routes API
});
```

---

## 📊 Monitoring et Maintenance

### Métriques à Surveiller
- **Temps de réponse API**
- **Taux d'erreur**
- **Utilisation CPU/Mémoire**
- **Connexions base de données**
- **Cache hit/miss ratio**

### Tâches de Maintenance
```bash
# Nettoyer le cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimiser la base de données
php artisan db:monitor
```

### Backups
- Render gère automatiquement les backups PostgreSQL
- Configurer des exports réguliers si nécessaire

---

## 🚨 Dépannage

### Erreurs Courantes

#### 1. Erreur 500 - Internal Server Error
```bash
# Vérifier les logs
docker-compose logs app
# Ou dans Render Dashboard → Logs
```

#### 2. Erreur de Connexion Base de Données
- Vérifier les variables d'environnement `DATABASE_URL`
- S'assurer que PostgreSQL est accessible

#### 3. Swagger UI Blanc
```bash
# Régénérer la documentation
php artisan l5-swagger:generate
```

#### 4. Erreur de Permissions
```bash
# Corriger les permissions
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
```

---

## 📞 Support

Pour toute question ou problème :
- Vérifier les logs d'application
- Consulter la documentation Render
- Tester localement avec Docker avant le déploiement

---

**🎉 Déploiement réussi ! Votre API est maintenant accessible sur `https://api.moustapha.seck.com/api/v1`**
