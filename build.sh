#!/usr/bin/env bash
# Script de déploiement pour Render.com
# Ce script configure et déploie l'application Laravel avec Swagger

set -o errexit  # Arrête le script en cas d'erreur

echo "════════════════════════════════════════════════════════"
echo "🚀 Starting Laravel Deployment Process"
echo "════════════════════════════════════════════════════════"

# Afficher les informations d'environnement
echo ""
echo "📊 Environment Information:"
echo "   - PHP Version: $(php -v | head -n 1)"
echo "   - Composer Version: $(composer --version)"
echo "   - Laravel Environment: ${APP_ENV:-production}"
echo ""

# ============================================
# 1. INSTALLATION DES DÉPENDANCES
# ============================================
echo "📦 Step 1/7: Installing Composer dependencies..."
composer install --no-dev \
                 --optimize-autoloader \
                 --no-interaction \
                 --prefer-dist \
                 --no-progress \
                 --no-suggest

if [ $? -eq 0 ]; then
    echo "   ✅ Dependencies installed successfully"
else
    echo "   ❌ Failed to install dependencies"
    exit 1
fi

# ============================================
# 2. CRÉATION DES DOSSIERS
# ============================================
echo ""
echo "📁 Step 2/7: Creating required directories..."

directories=(
    "storage/api-docs"
    "storage/framework/cache"
    "storage/framework/cache/data"
    "storage/framework/sessions"
    "storage/framework/views"
    "storage/logs"
    "bootstrap/cache"
)

for dir in "${directories[@]}"; do
    if [ ! -d "$dir" ]; then
        mkdir -p "$dir"
        echo "   ✅ Created: $dir"
    else
        echo "   ℹ️  Already exists: $dir"
    fi
done

# ============================================
# 3. CONFIGURATION DES PERMISSIONS
# ============================================
echo ""
echo "🔐 Step 3/7: Setting file permissions..."

chmod -R 775 storage
chmod -R 775 bootstrap/cache

echo "   ✅ Permissions set successfully"

# ============================================
# 4. GÉNÉRATION DE LA DOCUMENTATION SWAGGER
# ============================================
echo ""
echo "📝 Step 4/7: Generating Swagger/OpenAPI documentation..."

# Vérifier si la commande existe
if php artisan list | grep -q "l5-swagger:generate"; then
    php artisan l5-swagger:generate

    # Vérifier si le fichier a été généré
    if [ -f "storage/api-docs/api-docs.json" ]; then
        FILE_SIZE=$(stat -f%z "storage/api-docs/api-docs.json" 2>/dev/null || stat -c%s "storage/api-docs/api-docs.json" 2>/dev/null)
        echo "   ✅ Swagger documentation generated successfully"
        echo "   📄 File: storage/api-docs/api-docs.json (${FILE_SIZE} bytes)"

        # Afficher un aperçu du fichier
        echo "   📋 Preview:"
        head -n 5 storage/api-docs/api-docs.json | sed 's/^/      /'
    else
        echo "   ⚠️  Warning: Swagger documentation file not found!"
        echo "   ℹ️  The application will continue, but API documentation may not be available"
    fi
else
    echo "   ⚠️  l5-swagger:generate command not found"
    echo "   ℹ️  Skipping Swagger generation"
fi

# ============================================
# 5. OPTIMISATIONS LARAVEL
# ============================================
echo ""
echo "⚡ Step 5/7: Optimizing Laravel..."

echo "   - Caching configuration..."
php artisan config:cache

echo "   - Caching routes..."
php artisan route:cache

echo "   - Caching views..."
php artisan view:cache

echo "   ✅ Laravel optimizations completed"

# ============================================
# 6. VÉRIFICATIONS POST-INSTALLATION
# ============================================
echo ""
echo "🔍 Step 6/7: Running post-installation checks..."

CHECKS_PASSED=true

# Vérifier les fichiers critiques
critical_files=(
    "storage/api-docs/api-docs.json"
    "bootstrap/cache/config.php"
    "bootstrap/cache/routes-v7.php"
)

for file in "${critical_files[@]}"; do
    if [ -f "$file" ]; then
        echo "   ✅ Found: $file"
    else
        echo "   ⚠️  Missing: $file"
        CHECKS_PASSED=false
    fi
done

# Vérifier les permissions
if [ -w "storage/logs" ]; then
    echo "   ✅ storage/logs is writable"
else
    echo "   ❌ storage/logs is not writable"
    CHECKS_PASSED=false
fi

if [ -w "storage/api-docs" ]; then
    echo "   ✅ storage/api-docs is writable"
else
    echo "   ❌ storage/api-docs is not writable"
    CHECKS_PASSED=false
fi

# ============================================
# 7. RÉSUMÉ DU DÉPLOIEMENT
# ============================================
echo ""
echo "════════════════════════════════════════════════════════"
echo "📊 Step 7/7: Deployment Summary"
echo "════════════════════════════════════════════════════════"

if [ "$CHECKS_PASSED" = true ]; then
    echo "✅ Status: SUCCESS"
    echo ""
    echo "🎉 Deployment completed successfully!"
    echo ""
    echo "📍 Next Steps:"
    echo "   1. Verify your application: ${APP_URL:-https://your-app.onrender.com}"
    echo "   2. Check API documentation: ${APP_URL:-https://your-app.onrender.com}/api/documentation"
    echo "   3. Test API endpoints: ${APP_URL:-https://your-app.onrender.com}/docs/api-docs.json"
    echo ""
    echo "💡 Useful URLs:"
    echo "   - Swagger UI: /api/documentation"
    echo "   - OpenAPI JSON: /docs/api-docs.json"
    echo "   - Health Check: /api/health (if configured)"
else
    echo "⚠️  Status: COMPLETED WITH WARNINGS"
    echo ""
    echo "⚠️  Some checks failed. Please review the warnings above."
    echo "   The application may still work, but some features might be unavailable."
fi

echo ""
echo "════════════════════════════════════════════════════════"
echo "🏁 Deployment process finished at $(date)"
echo "════════════════════════════════════════════════════════"
echo ""

exit 0