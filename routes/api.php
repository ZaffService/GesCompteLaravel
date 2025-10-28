<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});





// Routes API versionnées
Route::prefix('v1')->group(function () {
    // Route de santé pour diagnostiquer les problèmes
    Route::get('/health', function () {
        try {
            $dbConnected = \DB::connection()->getPdo() ? true : false;
        } catch (\Exception $e) {
            $dbConnected = false;
        }

        return response()->json([
            'status' => 'ok',
            'timestamp' => now(),
            'database' => $dbConnected ? 'connected' : 'disconnected',
            'passport_keys' => [
                'private' => file_exists(storage_path('oauth-private.key')),
                'public' => file_exists(storage_path('oauth-public.key'))
            ],
            'admins_count' => $dbConnected ? \App\Models\Admin::count() : 0,
            'clients_count' => $dbConnected ? \App\Models\Client::count() : 0,
            'comptes_count' => $dbConnected ? \App\Models\Compte::count() : 0
        ]);
    });

    // Routes publiques (pas d'authentification requise)
    Route::post('/auth/login', [App\Http\Controllers\AuthController::class, 'login']);
    Route::post('/auth/logout', [App\Http\Controllers\AuthController::class, 'logout']);

    // Routes protégées (nécessitent un token valide)
    Route::middleware('auth:api')->group(function () {
        Route::post('/auth/refresh', [App\Http\Controllers\AuthController::class, 'refresh']);
    });

    // Routes protégées
    Route::middleware(['auth:api', 'App\Http\Middleware\RateLimitMiddleware', 'App\Http\Middleware\RoleMiddleware:admin|client'])->group(function () {
        /**
         * @OA\Get(
         *     path="/api/v1/comptes",
         *     summary="Lister tous les comptes",
         *     description="Admin peut récupérer la liste de tous les comptes, Client peut récupérer la liste de ses comptes",
         *     operationId="getComptes",
         *     tags={"Comptes"},
         *     security={{"bearerAuth":{}}},
         *     @OA\Parameter(
         *         name="page",
         *         in="query",
         *         description="Numéro de page",
         *         required=false,
         *         @OA\Schema(type="integer", default=1)
         *     ),
         *     @OA\Parameter(
         *         name="limit",
         *         in="query",
         *         description="Nombre d'éléments par page",
         *         required=false,
         *         @OA\Schema(type="integer", default=10, maximum=100)
         *     ),
         *     @OA\Parameter(
         *         name="type",
         *         in="query",
         *         description="Filtrer par type",
         *         required=false,
         *         @OA\Schema(type="string", enum={"epargne", "cheque"})
         *     ),
         *     @OA\Parameter(
         *         name="statut",
         *         in="query",
         *         description="Filtrer par statut",
         *         required=false,
         *         @OA\Schema(type="string", enum={"actif", "bloque", "ferme"})
         *     ),
         *     @OA\Parameter(
         *         name="search",
         *         in="query",
         *         description="Recherche par titulaire ou numéro",
         *         required=false,
         *         @OA\Schema(type="string")
         *     ),
         *     @OA\Parameter(
         *         name="sort",
         *         in="query",
         *         description="Tri",
         *         required=false,
         *         @OA\Schema(type="string", enum={"dateCreation", "solde", "titulaire"})
         *     ),
         *     @OA\Parameter(
         *         name="order",
         *         in="query",
         *         description="Ordre",
         *         required=false,
         *         @OA\Schema(type="string", enum={"asc", "desc"})
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="Liste des comptes récupérée avec succès",
         *         @OA\JsonContent(
         *             @OA\Property(property="success", type="boolean", example=true),
         *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Compte")),
         *             @OA\Property(property="pagination", ref="#/components/schemas/Pagination"),
         *             @OA\Property(property="links", ref="#/components/schemas/Links")
         *         )
         *     ),
         *     @OA\Response(response=401, description="Non autorisé")
         * )
         */
        Route::get('/comptes', [App\Http\Controllers\CompteController::class, 'index']);

        /**
         * @OA\Get(
         *     path="/api/v1/comptes/{compteId}",
         *     summary="Récupérer un compte spécifique",
         *     description="Admin peut récupérer un compte par ID, Client peut récupérer un de ses comptes par ID",
         *     operationId="getCompte",
         *     tags={"Comptes"},
         *     security={{"bearerAuth":{}}},
         *     @OA\Parameter(
         *         name="compteId",
         *         in="path",
         *         required=true,
         *         @OA\Schema(type="string", format="uuid")
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="Compte récupéré avec succès",
         *         @OA\JsonContent(
         *             @OA\Property(property="success", type="boolean", example=true),
         *             @OA\Property(property="data", ref="#/components/schemas/Compte")
         *         )
         *     ),
         *     @OA\Response(response=404, description="Compte non trouvé"),
         *     @OA\Response(response=401, description="Non autorisé")
         * )
         */
        Route::get('/comptes/{compte}', [App\Http\Controllers\CompteController::class, 'show']);

        /**
         * @OA\Post(
         *     path="/api/v1/comptes",
         *     summary="Créer un nouveau compte",
         *     description="Créer un nouveau compte bancaire avec génération automatique de numéro et client",
         *     operationId="createCompte",
         *     tags={"Comptes"},
         *     security={{"bearerAuth":{}}},
         *     @OA\RequestBody(
         *         required=true,
         *         @OA\JsonContent(
         *             @OA\Property(property="type", type="string", enum={"cheque", "epargne"}, example="cheque"),
         *             @OA\Property(property="soldeInitial", type="number", example=500000),
         *             @OA\Property(property="devise", type="string", example="FCFA"),
         *             @OA\Property(property="client", type="object",
         *                 @OA\Property(property="id", type="integer", nullable=true),
         *                 @OA\Property(property="titulaire", type="string", example="Hawa BB Wane"),
         *                 @OA\Property(property="email", type="string", format="email", example="cheikh.sy@example.com"),
         *                 @OA\Property(property="telephone", type="string", example="+221771234567"),
         *                 @OA\Property(property="adresse", type="string", example="Dakar, Sénégal")
         *             )
         *         )
         *     ),
         *     @OA\Response(
         *         response=201,
         *         description="Compte créé avec succès",
         *         @OA\JsonContent(
         *             @OA\Property(property="success", type="boolean", example=true),
         *             @OA\Property(property="message", type="string", example="Compte créé avec succès"),
         *             @OA\Property(property="data", ref="#/components/schemas/Compte")
         *         )
         *     ),
         *     @OA\Response(response=400, description="Données invalides"),
         *     @OA\Response(response=401, description="Non autorisé")
         * )
         */
        Route::post('/comptes', [App\Http\Controllers\CompteController::class, 'store']);

        /**
         * @OA\Patch(
         *     path="/api/v1/comptes/{compteId}",
         *     summary="Mettre à jour les informations du client",
         *     description="Mettre à jour les informations du client associé au compte",
         *     operationId="updateCompte",
         *     tags={"Comptes"},
         *     security={{"bearerAuth":{}}},
         *     @OA\Parameter(
         *         name="compteId",
         *         in="path",
         *         required=true,
         *         @OA\Schema(type="string", format="uuid")
         *     ),
         *     @OA\RequestBody(
         *         required=true,
         *         @OA\JsonContent(
         *             @OA\Property(property="titulaire", type="string", nullable=true),
         *             @OA\Property(property="informationsClient", type="object",
         *                 @OA\Property(property="telephone", type="string", nullable=true),
         *                 @OA\Property(property="email", type="string", format="email", nullable=true),
         *                 @OA\Property(property="password", type="string", nullable=true),
         *                 @OA\Property(property="nci", type="string", nullable=true)
         *             )
         *         )
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="Compte mis à jour avec succès",
         *         @OA\JsonContent(
         *             @OA\Property(property="success", type="boolean", example=true),
         *             @OA\Property(property="message", type="string", example="Compte mis à jour avec succès"),
         *             @OA\Property(property="data", ref="#/components/schemas/Compte")
         *         )
         *     ),
         *     @OA\Response(response=400, description="Données invalides"),
         *     @OA\Response(response=404, description="Compte non trouvé"),
         *     @OA\Response(response=401, description="Non autorisé")
         * )
         */
        Route::patch('/comptes/{compte}', [App\Http\Controllers\CompteController::class, 'update']);

        /**
         * @OA\Delete(
         *     path="/api/v1/comptes/{compteId}",
         *     summary="Supprimer un compte",
         *     description="Supprimer un compte (soft delete)",
         *     operationId="deleteCompte",
         *     tags={"Comptes"},
         *     security={{"bearerAuth":{}}},
         *     @OA\Parameter(
         *         name="compteId",
         *         in="path",
         *         required=true,
         *         @OA\Schema(type="string", format="uuid")
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="Compte supprimé avec succès",
         *         @OA\JsonContent(
         *             @OA\Property(property="success", type="boolean", example=true),
         *             @OA\Property(property="message", type="string", example="Compte supprimé avec succès"),
         *             @OA\Property(property="data", type="object",
         *                 @OA\Property(property="id", type="string", format="uuid"),
         *                 @OA\Property(property="numeroCompte", type="string"),
         *                 @OA\Property(property="statut", type="string", example="ferme"),
         *                 @OA\Property(property="dateFermeture", type="string", format="date-time")
         *             )
         *         )
         *     ),
         *     @OA\Response(response=404, description="Compte non trouvé"),
         *     @OA\Response(response=401, description="Non autorisé")
         * )
         */
        Route::delete('/comptes/{compte}', [App\Http\Controllers\CompteController::class, 'destroy']);

        /**
         * @OA\Post(
         *     path="/api/v1/comptes/{compteId}/bloquer",
         *     summary="Bloquer un compte",
         *     description="Bloquer un compte épargne actif",
         *     operationId="bloquerCompte",
         *     tags={"Comptes"},
         *     security={{"bearerAuth":{}}},
         *     @OA\Parameter(
         *         name="compteId",
         *         in="path",
         *         required=true,
         *         @OA\Schema(type="string", format="uuid")
         *     ),
         *     @OA\RequestBody(
         *         required=true,
         *         @OA\JsonContent(
         *             @OA\Property(property="motif", type="string", example="Activité suspecte détectée"),
         *             @OA\Property(property="duree", type="integer", example=30),
         *             @OA\Property(property="unite", type="string", enum={"jours", "mois"}, example="mois")
         *         )
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="Compte bloqué avec succès",
         *         @OA\JsonContent(
         *             @OA\Property(property="success", type="boolean", example=true),
         *             @OA\Property(property="message", type="string", example="Compte bloqué avec succès"),
         *             @OA\Property(property="data", type="object",
         *                 @OA\Property(property="id", type="string", format="uuid"),
         *                 @OA\Property(property="statut", type="string", example="bloque"),
         *                 @OA\Property(property="motifBlocage", type="string"),
         *                 @OA\Property(property="dateBlocage", type="string", format="date-time"),
         *                 @OA\Property(property="dateDeblocagePrevue", type="string", format="date-time")
         *             )
         *         )
         *     ),
         *     @OA\Response(response=400, description="Impossible de bloquer le compte"),
         *     @OA\Response(response=404, description="Compte non trouvé"),
         *     @OA\Response(response=401, description="Non autorisé")
         * )
         */
        Route::post('/comptes/{compte}/bloquer', [App\Http\Controllers\CompteController::class, 'bloquer']);

        /**
         * @OA\Post(
         *     path="/api/v1/comptes/{compteId}/debloquer",
         *     summary="Débloquer un compte",
         *     description="Débloquer un compte bloqué",
         *     operationId="debloquerCompte",
         *     tags={"Comptes"},
         *     security={{"bearerAuth":{}}},
         *     @OA\Parameter(
         *         name="compteId",
         *         in="path",
         *         required=true,
         *         @OA\Schema(type="string", format="uuid")
         *     ),
         *     @OA\RequestBody(
         *         required=true,
         *         @OA\JsonContent(
         *             @OA\Property(property="motif", type="string", example="Vérification complétée")
         *         )
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="Compte débloqué avec succès",
         *         @OA\JsonContent(
         *             @OA\Property(property="success", type="boolean", example=true),
         *             @OA\Property(property="message", type="string", example="Compte débloqué avec succès"),
         *             @OA\Property(property="data", type="object",
         *                 @OA\Property(property="id", type="string", format="uuid"),
         *                 @OA\Property(property="statut", type="string", example="actif"),
         *                 @OA\Property(property="dateDeblocage", type="string", format="date-time")
         *             )
         *         )
         *     ),
         *     @OA\Response(response=400, description="Impossible de débloquer le compte"),
         *     @OA\Response(response=404, description="Compte non trouvé"),
         *     @OA\Response(response=401, description="Non autorisé")
         * )
         */
        Route::post('/comptes/{compte}/debloquer', [App\Http\Controllers\CompteController::class, 'debloquer']);
    });
});
