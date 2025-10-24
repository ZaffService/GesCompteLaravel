<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHENTICATED',
                    'message' => 'Authentification requise'
                ]
            ], 401);
        }

        // Vérifier le rôle de l'utilisateur
        $userRole = $this->getUserRole($user);

        if (!$this->hasRole($userRole, $role)) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INSUFFICIENT_PERMISSIONS',
                    'message' => 'Permissions insuffisantes pour cette action',
                    'details' => [
                        'required_role' => $role,
                        'user_role' => $userRole
                    ]
                ]
            ], 403);
        }

        return $next($request);
    }

    /**
     * Déterminer le rôle de l'utilisateur
     */
    private function getUserRole($user): string
    {
        // Pour cette implémentation simple, on détermine le rôle par le type de modèle
        if ($user instanceof \App\Models\Client) {
            return 'client';
        }

        // TODO: Implémenter un système d'admin plus tard
        return 'admin';
    }

    /**
     * Vérifier si l'utilisateur a le rôle requis
     */
    private function hasRole(string $userRole, string $requiredRole): bool
    {
        $roles = explode('|', $requiredRole);

        return in_array($userRole, $roles);
    }
}
