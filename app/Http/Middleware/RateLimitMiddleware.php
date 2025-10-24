<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMiddleware
{
    /**
     * Nombre maximum de requêtes par jour
     */
    const MAX_REQUESTS_PER_DAY = 10;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user) {
            $userId = $user->id;
            $cacheKey = "rate_limit_user_{$userId}";
            $today = now()->toDateString();

            // Récupérer ou initialiser les données de rate limiting
            $rateLimitData = Cache::get($cacheKey, [
                'date' => $today,
                'count' => 0
            ]);

            // Réinitialiser le compteur si c'est un nouveau jour
            if ($rateLimitData['date'] !== $today) {
                $rateLimitData = [
                    'date' => $today,
                    'count' => 0
                ];
            }

            // Vérifier si la limite est atteinte
            if ($rateLimitData['count'] >= self::MAX_REQUESTS_PER_DAY) {
                // Logger l'utilisateur qui dépasse la limite
                Log::warning('Rate limit exceeded', [
                    'user_id' => $userId,
                    'user_email' => $user->email ?? 'unknown',
                    'request_count' => $rateLimitData['count'],
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'date' => $today
                ]);

                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'RATE_LIMIT_EXCEEDED',
                        'message' => 'Limite de requêtes dépassée. Vous avez atteint le maximum de ' . self::MAX_REQUESTS_PER_DAY . ' requêtes par jour.',
                        'details' => [
                            'max_requests' => self::MAX_REQUESTS_PER_DAY,
                            'current_count' => $rateLimitData['count'],
                            'reset_date' => now()->addDay()->startOfDay()->toISOString()
                        ]
                    ]
                ], 429);
            }

            // Incrémenter le compteur
            $rateLimitData['count']++;

            // Sauvegarder en cache (expire à minuit)
            $secondsUntilMidnight = now()->endOfDay()->diffInSeconds(now());
            Cache::put($cacheKey, $rateLimitData, $secondsUntilMidnight);

            // Ajouter les headers de rate limiting à la réponse
            $response = $next($request);

            if ($response instanceof JsonResponse) {
                $response->headers->set('X-RateLimit-Limit', self::MAX_REQUESTS_PER_DAY);
                $response->headers->set('X-RateLimit-Remaining', max(0, self::MAX_REQUESTS_PER_DAY - $rateLimitData['count']));
                $response->headers->set('X-RateLimit-Reset', now()->endOfDay()->timestamp);
            }
        }

        return $next($request);
    }
}
