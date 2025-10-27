<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class SwaggerDocsController extends Controller
{
    /**
     * Retourne le fichier JSON de documentation Swagger
     *
     * @return \Illuminate\Http\Response
     */
    public function getJson()
    {
        $path = storage_path('api-docs/api-docs.json');

        // Vérifier si le fichier existe
        if (!File::exists($path)) {
            // En environnement de développement, tenter de générer la doc
            if (app()->environment('local', 'development')) {
                try {
                    Artisan::call('l5-swagger:generate');

                    // Revérifier après génération
                    if (File::exists($path)) {
                        return $this->returnJsonFile($path);
                    }
                } catch (\Exception $e) {
                    return $this->returnError(
                        'Failed to generate API documentation: ' . $e->getMessage(),
                        500
                    );
                }
            }

            // Si toujours pas de fichier, retourner une erreur détaillée
            return $this->returnError(
                'API documentation not found. Please run: php artisan l5-swagger:generate',
                404,
                [
                    'expected_path' => $path,
                    'storage_path' => storage_path('api-docs'),
                    'directory_exists' => File::isDirectory(storage_path('api-docs')),
                    'directory_writable' => File::isWritable(storage_path('api-docs')),
                    'environment' => app()->environment()
                ]
            );
        }

        return $this->returnJsonFile($path);
    }

    /**
     * Route de debug pour vérifier la configuration Swagger
     * À utiliser uniquement en développement
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function debug()
    {
        // Sécurité : autoriser uniquement en environnement non-production
        if (app()->environment('production')) {
            abort(403, 'Debug endpoint not available in production');
        }

        $path = storage_path('api-docs/api-docs.json');
        $storageDir = storage_path('api-docs');

        $info = [
            'file' => [
                'exists' => File::exists($path),
                'path' => $path,
                'readable' => File::isReadable($path),
                'size' => File::exists($path) ? File::size($path) : 0,
                'last_modified' => File::exists($path) ? date('Y-m-d H:i:s', File::lastModified($path)) : null,
            ],
            'directory' => [
                'exists' => File::isDirectory($storageDir),
                'path' => $storageDir,
                'writable' => File::isWritable($storageDir),
                'readable' => File::isReadable($storageDir),
            ],
            'environment' => [
                'app_env' => app()->environment(),
                'app_url' => config('app.url'),
                'app_debug' => config('app.debug'),
            ],
            'swagger_config' => [
                'generate_always' => config('l5-swagger.defaults.generate_always'),
                'docs_path' => config('l5-swagger.defaults.paths.docs'),
                'docs_json' => config('l5-swagger.documentations.default.paths.docs_json'),
                'api_route' => config('l5-swagger.documentations.default.routes.api'),
            ],
            'routes' => [
                'documentation' => route('l5-swagger.default.api', [], false),
                'json' => route('l5-swagger.default.docs_json', [], false),
            ],
        ];

        // Ajouter le contenu du fichier si debug est activé
        if (config('app.debug') && File::exists($path)) {
            $content = File::get($path);
            $decoded = json_decode($content, true);

            $info['file']['valid_json'] = json_last_error() === JSON_ERROR_NONE;
            $info['file']['preview'] = $decoded ? array_slice($decoded, 0, 3) : null;
        }

        return response()->json($info, 200, [], JSON_PRETTY_PRINT);
    }

    /**
     * Route pour forcer la génération de la documentation
     * Utile pour le déploiement ou le debugging
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function generate()
    {
        try {
            // Vérifier que le dossier existe
            $storageDir = storage_path('api-docs');
            if (!File::isDirectory($storageDir)) {
                File::makeDirectory($storageDir, 0775, true);
            }

            // Générer la documentation
            Artisan::call('l5-swagger:generate');

            $path = storage_path('api-docs/api-docs.json');

            return response()->json([
                'success' => true,
                'message' => 'API documentation generated successfully',
                'file' => [
                    'path' => $path,
                    'exists' => File::exists($path),
                    'size' => File::exists($path) ? File::size($path) : 0,
                ],
                'redirect' => route('l5-swagger.default.api', [], false),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to generate documentation',
                'message' => $e->getMessage(),
                'trace' => app()->environment('local') ? $e->getTraceAsString() : null,
            ], 500);
        }
    }

    /**
     * Rediriger vers l'interface Swagger UI
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectToSwagger()
    {
        return redirect('/api/documentation');
    }

    /**
     * Retourner le fichier JSON avec les bons headers
     *
     * @param string $path
     * @return \Illuminate\Http\Response
     */
    private function returnJsonFile($path)
    {
        $content = File::get($path);

        return response($content, 200)
            ->header('Content-Type', 'application/json')
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization')
            ->header('Cache-Control', 'public, max-age=3600'); // Cache 1 heure
    }

    /**
     * Retourner une erreur formatée
     *
     * @param string $message
     * @param int $status
     * @param array $debug
     * @return \Illuminate\Http\JsonResponse
     */
    private function returnError($message, $status = 404, $debug = [])
    {
        $error = [
            'success' => false,
            'error' => $message,
            'status' => $status,
        ];

        // Ajouter les infos de debug uniquement si pas en production
        if (!app()->environment('production') && !empty($debug)) {
            $error['debug'] = $debug;
        }

        return response()->json($error, $status);
    }
}
