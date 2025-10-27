<?php

use Illuminate\Support\Facades\Route;

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

// Route manuelle pour /docs/api-docs.json (nécessaire pour le développement)
Route::get('/docs/api-docs.json', function () {
    $filePath = storage_path('api-docs/api-docs.json');

    if (!file_exists($filePath)) {
        return response()->json(['error' => 'Documentation not found'], 404);
    }

    return response()->file($filePath, [
        'Content-Type' => 'application/json',
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'GET',
        'Access-Control-Allow-Headers' => 'Content-Type'
    ]);
})->name('swagger.json');

/*
|--------------------------------------------------------------------------
| Routes personnalisées (si nécessaire)
|--------------------------------------------------------------------------
|
| Si vous avez besoin de routes personnalisées pour votre application,
| ajoutez-les ici.
*/

// Exemple : Route pour une page d'accueil API
// Route::get('/api', function () {
//     return response()->json([
//         'message' => 'Bienvenue sur l\'API Banque',
//         'documentation' => url('/docs'),
//         'version' => '1.0.0'
//     ]);
// });