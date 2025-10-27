
#!/bin/bash
set -e

echo "🚀 Lancement du conteneur Laravel..."

# Réinitialiser tous les caches avant le démarrage
php artisan optimize:clear || true

# Vérifier la clé APP_KEY (si manquante, la régénérer)
if grep -q "^APP_KEY=$" .env; then
    echo "⚙️ Génération d'une nouvelle clé Laravel..."
    php artisan key:generate --force --no-interaction || true
fi

# Lancer les migrations si la BDD est dispo
php artisan migrate --force || true

# Générer la documentation Swagger AVANT les caches
echo "📚 Génération de la documentation Swagger..."
php artisan l5-swagger:generate --no-interaction || true

# Générer les caches pour accélérer l'app
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "✅ Configuration Laravel terminée ! Démarrage des services..."

# Lancer Nginx + PHP-FPM + Queue Worker
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
