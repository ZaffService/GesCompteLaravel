<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiResponseTrait
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}

// Trait pour standardiser les réponses API
trait ApiResponseFormatter
{
    /**
     * Réponse de succès
     */
    protected function successResponse($data = null, string $message = '', int $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Réponse d'erreur
     */
    protected function errorResponse(string $message, string $code = 'ERROR', array $details = [], int $statusCode = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message,
                'details' => $details
            ]
        ], $statusCode);
    }

    /**
     * Réponse paginée
     */
    protected function paginatedResponse($paginatedData, string $message = ''): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $paginatedData->items(),
            'pagination' => [
                'currentPage' => $paginatedData->currentPage(),
                'totalPages' => $paginatedData->lastPage(),
                'totalItems' => $paginatedData->total(),
                'itemsPerPage' => $paginatedData->perPage(),
                'hasNext' => $paginatedData->hasMorePages(),
                'hasPrevious' => $paginatedData->currentPage() > 1
            ],
            'links' => [
                'self' => $paginatedData->url($paginatedData->currentPage()),
                'first' => $paginatedData->url(1),
                'last' => $paginatedData->url($paginatedData->lastPage()),
                'next' => $paginatedData->nextPageUrl(),
                'prev' => $paginatedData->previousPageUrl()
            ]
        ]);
    }

    /**
     * Réponse de création
     */
    protected function createdResponse($data, string $message = 'Ressource créée avec succès'): JsonResponse
    {
        return $this->successResponse($data, $message, 201);
    }

    /**
     * Réponse de non trouvé
     */
    protected function notFoundResponse(string $resource = 'Ressource', string $identifier = ''): JsonResponse
    {
        $message = $resource . ' non trouvée';
        if ($identifier) {
            $message .= ' : ' . $identifier;
        }

        return $this->errorResponse($message, 'NOT_FOUND', [], 404);
    }

    /**
     * Réponse d'accès refusé
     */
    protected function forbiddenResponse(string $message = 'Accès refusé'): JsonResponse
    {
        return $this->errorResponse($message, 'FORBIDDEN', [], 403);
    }

    /**
     * Réponse de validation échouée
     */
    protected function validationErrorResponse(array $errors, string $message = 'Données invalides'): JsonResponse
    {
        return $this->errorResponse($message, 'VALIDATION_ERROR', $errors, 422);
    }
}
