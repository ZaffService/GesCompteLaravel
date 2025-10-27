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

Route::get('/docs/api-docs.json', [SwaggerDocsController::class, 'getJson'])->name('swagger.json');

// Route pour gérer /docs avec ou sans paramètres de requête
Route::get('/docs{any?}', function ($any = null) {
    $query = request()->getQueryString();

    // Si c'est une requête pour api-docs.json
    if ($query === 'api-docs.json' || $any === '?api-docs.json') {
        return app(SwaggerDocsController::class)->getJson();
    }

    // Sinon, rediriger vers l'interface Swagger
    return redirect('/api/documentation');
})->where('any', '.*')->name('docs');
