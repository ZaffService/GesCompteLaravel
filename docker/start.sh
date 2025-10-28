
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

# Lancer les seeders pour peupler la base de données
echo "🌱 Exécution des seeders..."
php artisan db:seed --force || true

# Générer la documentation Swagger AVANT les caches
echo "📚 Génération de la documentation Swagger..."
php artisan l5-swagger:generate --no-interaction || true

# Générer les caches pour accélérer l'app (SAUF les routes pour éviter les problèmes avec les Closures)
# php artisan config:cache || true  # Désactivé temporairement pour debug
# php artisan route:cache || true  # Désactivé temporairement à cause des routes Closure
# php artisan view:cache || true   # Désactivé temporairement pour debug

echo "✅ Configuration Laravel terminée ! Démarrage des services..."

# Lancer Nginx + PHP-FPM + Queue Worker
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
