<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SwaggerDocsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Routes Swagger / L5-Swagger
|--------------------------------------------------------------------------
|
| Les routes suivantes sont AUTOMATIQUEMENT générées par L5-Swagger :
|
| GET  /docs                  -> Interface Swagger UI
| GET  /docs/api-docs.json    -> Fichier JSON OpenAPI
| GET  /docs/asset/*          -> Assets Swagger UI (CSS, JS, etc.)
|
| ⚠️ NE PAS définir manuellement ces routes ici !
| Elles sont gérées par le package L5-Swagger via le service provider.
|
| Configuration : voir config/l5-swagger.php
*/

// Route pour l'interface Swagger UI
Route::get('/docs', [SwaggerDocsController::class, 'redirectToSwagger'])->name('l5-swagger.default.docs');

// Route pour le fichier JSON directement
Route::get('/docs/api-docs.json', [SwaggerDocsController::class, 'getJson'])->name('l5-swagger.default.docs_json');

/*
|--------------------------------------------------------------------------
| Routes de Debug et Maintenance Swagger (Non-production uniquement)
|--------------------------------------------------------------------------
*/

// Route de debug - Affiche les informations de configuration Swagger
// URL: /swagger/debug
// Accessible uniquement en développement
Route::get('/swagger/debug', [SwaggerDocsController::class, 'debug'])
    ->name('swagger.debug')
    ->middleware(function ($request, $next) {
        if (app()->environment('production')) {
            abort(403, 'Debug endpoint not available in production');
        }
        return $next($request);
    });

// Route pour forcer la génération de la documentation
// URL: /swagger/generate
// Utile après le déploiement ou en cas de problème
Route::get('/swagger/generate', [SwaggerDocsController::class, 'generate'])
    ->name('swagger.generate');

// Route de vérification rapide (health check)
Route::get('/swagger/health', function () {
    $path = storage_path('api-docs/api-docs.json');
    $exists = file_exists($path);

    return response()->json([
        'status' => $exists ? 'ok' : 'error',
        'swagger_json_exists' => $exists,
        'swagger_ui_url' => url('/api/documentation'),
        'json_url' => url('/docs/api-docs.json'),
        'timestamp' => now()->toIso8601String(),
    ], $exists ? 200 : 503);
})->name('swagger.health');

/*
|--------------------------------------------------------------------------
| Routes personnalisées de l'application
|--------------------------------------------------------------------------
*/

// Exemple : Page d'accueil de l'API
Route::get('/api', function () {
    return response()->json([
        'name' => 'API Banque',
        'version' => '1.0.0',
        'description' => 'API RESTful pour la gestion des comptes bancaires',
        'documentation' => url('/api/documentation'),
        'openapi_json' => url('/docs/api-docs.json'),
        'environment' => app()->environment(),
        'timestamp' => now()->toIso8601String(),
    ]);
})->name('api.index');

