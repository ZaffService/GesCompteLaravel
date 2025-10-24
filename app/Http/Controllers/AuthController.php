<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Connexion d'un utilisateur
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'code' => 'nullable|string|size:6' // Code requis après première connexion
        ]);

        // Trouver l'utilisateur par email
        $user = Client::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_CREDENTIALS',
                    'message' => 'Email ou mot de passe incorrect'
                ]
            ], 401);
        }

        // Vérifier si c'est la première connexion (code requis)
        if (is_null($user->code_verified_at)) {
            if (!$request->has('code') || $request->code !== $user->code) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'CODE_REQUIRED',
                        'message' => 'Code de vérification requis pour la première connexion'
                    ]
                ], 403);
            }

            // Marquer le code comme vérifié
            $user->code_verified_at = now();
            $user->save();
        }

        // Générer les tokens
        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Connexion réussie',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'titulaire' => $user->titulaire,
                    'email' => $user->email,
                    'telephone' => $user->telephone,
                ],
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => 3600 // 1 heure
            ]
        ]);
    }

    /**
     * Rafraîchir le token d'accès
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHENTICATED',
                    'message' => 'Token invalide'
                ]
            ], 401);
        }

        // Révoquer l'ancien token
        $request->user()->currentAccessToken()->delete();

        // Créer un nouveau token
        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Token rafraîchi',
            'data' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => 3600
            ]
        ]);
    }

    /**
     * Déconnexion
     */
    public function logout(Request $request): JsonResponse
    {
        // Révoquer le token actuel
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Déconnexion réussie'
        ]);
    }
}
