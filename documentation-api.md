# Documentation Swagger API - Fichiers de Gestion

## Vue d'ensemble
Voici tous les fichiers qui gèrent la documentation Swagger/OpenAPI de l'API dans le projet Laravel, avec leurs rôles respectifs et extraits de code pertinents.

## 1. Configuration principale - `config/l5-swagger.php`

**Rôle** : Configuration complète du package L5-Swagger pour la génération et l'affichage de la documentation API.

**Extrait pertinent** :
```php
'default' => 'default',
'documentations' => [
    'default' => [
        'api' => [
            'title' => 'L5 Swagger UI',
        ],
        'routes' => [
            'api' => 'api/documentation',
            'docs' => 'docs',
        ],
        'paths' => [
            'docs_json' => 'api-docs.json',
            'docs_yaml' => 'api-docs.yaml',
            'annotations' => [
                base_path('app'),
            ],
        ],
    ],
],
```

## 2. Contrôleur personnalisé - `app/Http/Controllers/SwaggerDocsController.php`

**Rôle** : Contrôleur personnalisé pour servir la documentation JSON depuis le stockage.

**Code complet** :
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;

class SwaggerDocsController extends Controller
{
    public function getJson()
    {
        $path = storage_path('api-docs/api-docs.json');

        if (!File::exists($path)) {
            abort(404, 'API documentation not found');
        }

        $content = File::get($path);

        return response($content, 200)
            ->header('Content-Type', 'application/json')
            ->header('Access-Control-Allow-Origin', '*');
    }
}
```

## 3. Routes web - `routes/web.php`

**Rôle** : Définition des routes pour accéder à la documentation Swagger.

**Extraits pertinents** :
```php
// Route pour l'interface Swagger UI
Route::get('/docs', function () {
    return redirect('/api/documentation');
})->name('l5-swagger.default.docs');

// Route pour le fichier JSON directement
Route::get('/docs/api-docs.json', function () {
    return response()->json([
        "openapi" => "3.0.0",
        "info" => [
            "title" => "API Banque - Documentation",
            "description" => "API RESTful pour la gestion des comptes bancaires",
            "version" => "1.0.0"
        ],
        // ... documentation complète inline
    ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
})->name('l5-swagger.default.docs_json');

// Route de fallback pour capturer /docs avec paramètres de requête
Route::get('/docs', function (Illuminate\Http\Request $request) {
    if ($request->has('api-docs.json') || $request->query('api-docs.json') !== null) {
        return response()->json([
            // ... même documentation complète
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
    return redirect('/api/documentation');
})->name('l5-swagger.default.docs');
```

## 4. Routes API - `routes/api.php`

**Rôle** : Contient les annotations Swagger/OpenAPI pour documenter les endpoints API.

**Exemple d'annotation** :
```php
/**
 * @OA\Get(
 *     path="/api/v1/comptes",
 *     summary="Lister tous les comptes",
 *     description="Admin peut récupérer la liste de tous les comptes, Client peut récupérer la liste de ses comptes",
 *     operationId="getComptes",
 *     tags={"Comptes"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="page", in="query", description="Numéro de page", required=false),
 *     @OA\Response(response=200, description="Liste des comptes récupérée avec succès")
 * )
 */
Route::get('/comptes', [App\Http\Controllers\CompteController::class, 'index']);
```

## 5. Template Swagger UI - `resources/views/vendor/l5-swagger/index.blade.php`

**Rôle** : Template Blade pour l'interface Swagger UI.

**Extrait pertinent** :
```blade
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{config('l5-swagger.documentations.'.$documentation.'.api.title')}}</title>
    <link rel="stylesheet" type="text/css" href="{{ l5_swagger_asset($documentation, 'swagger-ui.css') }}">
    <!-- ... autres assets -->
</head>
<body>
<div id="swagger-ui"></div>

<script src="{{ l5_swagger_asset($documentation, 'swagger-ui-bundle.js') }}"></script>
<script>
    const ui = SwaggerUIBundle({
        dom_id: '#swagger-ui',
        url: "{{ config('l5-swagger.defaults.paths.base') }}/docs/?api-docs.json",
        // ... autres configurations
    });
</script>
</html>
```

## 6. Documentation générée - `storage/api-docs/api-docs.json`

**Rôle** : Fichier JSON généré automatiquement contenant la documentation des API basée sur les annotations.

**Structure** :
```json
{
  "openapi": "3.0.0",
  "info": {
    "title": "API Banque - Documentation",
    "description": "API RESTful pour la gestion des comptes bancaires",
    "version": "1.0.0"
  },
  "servers": [...],
  "paths": {...},
  "components": {...}
}
```

## 7. Assets Swagger UI - `public/docs/asset/`

**Rôle** : Contient tous les fichiers statiques nécessaires à l'interface Swagger UI.

**Fichiers principaux** :
- `swagger-ui.css` - Styles CSS pour l'interface
- `swagger-ui.js` - Script principal JavaScript
- `swagger-ui-bundle.js` - Bundle complet avec toutes les dépendances
- `swagger-ui-standalone-preset.js` - Preset standalone
- `favicon-16x16.png`, `favicon-32x32.png` - Icônes

## 8. Cache des vues - `storage/framework/views/`

**Rôle** : Contient les vues Blade compilées en PHP pour de meilleures performances.

**Exemple** : `6d67cc3b3cfd299705aeef2640d0425f.php` - Version compilée de `index.blade.php`

## 9. Cache des packages - `bootstrap/cache/packages.php`

**Rôle** : Cache des configurations des packages Composer, incluant L5-Swagger.

**Extrait pertinent** :
```php
'darkaonline/l5-swagger' => array (
  // Configuration mise en cache
),
```

## Architecture du système

Le système fonctionne avec une approche hybride :

1. **Routes automatiques L5-Swagger** : Générées automatiquement par le package
2. **Routes personnalisées** : Ajoutées manuellement pour gérer tous les cas d'accès à la documentation
3. **Documentation statique** : Générée inline dans les routes pour éviter les problèmes de génération dynamique
4. **Assets statiques** : Servis depuis `public/docs/asset/` pour l'interface utilisateur

Cette architecture assure une compatibilité maximale avec différents environnements et configurations Swagger.