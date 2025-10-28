
#!/bin/bash
set -e

echo "🚀 Lancement du conteneur Laravel..."

# Réinitialiser tous les caches avant le démarrage
php artisan optimize:clear || true

# Générer la clé Laravel si elle n'existe pas
echo "🔑 Vérification de la clé APP_KEY..."
if [ -z "$APP_KEY" ]; then
    echo "⚙️ Génération d'une nouvelle clé Laravel..."
    php artisan key:generate --force --no-interaction || true
else
    echo "✅ APP_KEY déjà définie dans l'environnement"
fi

# Lancer les migrations si la BDD est dispo
php artisan migrate --force || true

# Installer Passport si nécessaire
echo "🔐 Installation de Passport..."
php artisan passport:install --force || true

# Lancer les seeders pour peupler la base de données
echo "🌱 Exécution des seeders..."
php artisan db:seed --force || true

# Générer la documentation Swagger AVANT les caches
echo "📚 Génération de la documentation Swagger..."
php artisan l5-swagger:generate --no-interaction || true

# Générer les clés Passport manuellement si elles n'existent pas
echo "🔐 Vérification des clés Passport..."
if [ ! -f storage/oauth-private.key ] || [ ! -f storage/oauth-public.key ]; then
    echo "🔑 Génération des clés Passport..."

    # Créer le répertoire storage s'il n'existe pas
    mkdir -p storage

    # Générer les clés Passport
    php artisan passport:keys --force || true

    # Vérifier si les clés ont été créées et ajuster les permissions
    if [ -f storage/oauth-private.key ] && [ -f storage/oauth-public.key ]; then
        echo "✅ Clés Passport générées avec succès"
        chmod 600 storage/oauth-private.key storage/oauth-public.key
        chown www-data:www-data storage/oauth-private.key storage/oauth-public.key
    else
        echo "❌ Échec de la génération des clés Passport"
    fi
else
    echo "✅ Clés Passport déjà présentes"
fi

# Générer les caches pour accélérer l'app (SAUF les routes pour éviter les problèmes avec les Closures)
# php artisan config:cache || true  # Désactivé temporairement pour debug
# php artisan route:cache || true  # Désactivé temporairement à cause des routes Closure
# php artisan view:cache || true   # Désactivé temporairement pour debug

echo "✅ Configuration Laravel terminée ! Démarrage des services..."

# Lancer Nginx + PHP-FPM + Queue Worker
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
