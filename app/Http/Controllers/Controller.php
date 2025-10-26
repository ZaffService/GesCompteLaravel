<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     title="API Banque Laravel",
 *     version="1.0.0",
 *     description="Documentation complète de l'API de gestion des comptes bancaires",
 *     @OA\Contact(
 *         email="seckmoustapha238@gmail.com",
 *         name="Moustapha Seck"
 *     )
 * )
 * @OA\Server(
 *     url="https://moustapha-seck.onrender.com",
 *     description="Serveur de production"
 * )
 * @OA\Server(
 *     url="http://localhost:8000",
 *     description="Serveur de développement local"
 * )
 * @OA\SecurityScheme(
 *     type="http",
 *     securityScheme="bearerAuth",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Entrez votre token JWT obtenu via POST /api/v1/auth/login"
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
