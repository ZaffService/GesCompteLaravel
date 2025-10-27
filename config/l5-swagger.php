<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Définition du document par défaut
    |--------------------------------------------------------------------------
    */
    'default' => 'default',

    /*
    |--------------------------------------------------------------------------
    | Définition des différentes documentations disponibles
    |--------------------------------------------------------------------------
    */
    'documentations' => [
        'default' => [
            'api' => [
                'title' => 'API Banque - Documentation',
            ],

            'routes' => [
                /*
                 * Route d’accès à l’interface Swagger UI
                 */
                'api' => 'api/documentation',
            ],

            'paths' => [
                /*
                 * Générer des URL absolues pour les ressources Swagger
                 */
                'use_absolute_path' => env('L5_SWAGGER_USE_ABSOLUTE_PATH', true),

                /*
                 * Dossier contenant les assets Swagger UI (JS, CSS)
                 */
                'swagger_ui_assets_path' => env('L5_SWAGGER_UI_ASSETS_PATH', 'docs/asset/'),

                /*
                 * Nom du fichier JSON généré par L5 Swagger
                 */
                'docs_json' => 'api-docs.json',

                /*
                 * Nom du fichier YAML optionnel
                 */
                'docs_yaml' => 'api-docs.yaml',

                /*
                 * Format utilisé par défaut (json ou yaml)
                 */
                'format_to_use_for_docs' => env('L5_FORMAT_TO_USE_FOR_DOCS', 'json'),

                /*
                 * URL pour accéder à la documentation générée
                 */
                'docs_url' => env('L5_SWAGGER_DOCS_URL', '/docs'),

                /*
                 * Dossier contenant les annotations Swagger
                 */
                'annotations' => [
                    base_path('app/Http/Controllers'),
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Valeurs par défaut de configuration
    |--------------------------------------------------------------------------
    */
    'defaults' => [

        /*
        |--------------------------------------------------------------------------
        | Routes
        |--------------------------------------------------------------------------
        */
        'routes' => [
            'docs' => 'docs',
            'oauth2_callback' => 'api/oauth2-callback',
            'middleware' => [
                'api' => [],
                'asset' => [],
                'docs' => [],
                'oauth2_callback' => [],
            ],
            'group_options' => [],
        ],

        /*
        |--------------------------------------------------------------------------
        | Chemins et structure des fichiers Swagger
        |--------------------------------------------------------------------------
        */
        'paths' => [
            'docs' => storage_path('api-docs'),
            'views' => base_path('resources/views/vendor/l5-swagger'),
            'base' => env('L5_SWAGGER_BASE_PATH', 'https://moustapha-seck.onrender.com'),
            'excludes' => [],
        ],

        /*
        |--------------------------------------------------------------------------
        | Options de scan (analyse des annotations)
        |--------------------------------------------------------------------------
        */
        'scanOptions' => [
            'default_processors_configuration' => [],
            'analyser' => null,
            'analysis' => null,
            'processors' => [],
            'pattern' => null,
            'exclude' => [],
            'open_api_spec_version' => env(
                'L5_SWAGGER_OPEN_API_SPEC_VERSION',
                \L5Swagger\Generator::OPEN_API_DEFAULT_SPEC_VERSION
            ),
        ],

        /*
        |--------------------------------------------------------------------------
        | Sécurité de l’API (JWT / Bearer)
        |--------------------------------------------------------------------------
        */
        'securityDefinitions' => [
            'securitySchemes' => [
                'bearerAuth' => [
                    'type' => 'http',
                    'scheme' => 'bearer',
                    'bearerFormat' => 'JWT',
                    'description' => 'Entrez votre token JWT obtenu via POST /api/v1/auth/login',
                ],
            ],
            'security' => [],
        ],

        /*
        |--------------------------------------------------------------------------
        | Génération et options globales
        |--------------------------------------------------------------------------
        */
        'generate_always' => env('L5_SWAGGER_GENERATE_ALWAYS', true),
        'generate_yaml_copy' => env('L5_SWAGGER_GENERATE_YAML_COPY', false),
        'proxy' => true,
        'additional_config_url' => null,
        'operations_sort' => env('L5_SWAGGER_OPERATIONS_SORT', null),
        'validator_url' => null,

        /*
        |--------------------------------------------------------------------------
        | Interface Swagger UI
        |--------------------------------------------------------------------------
        */
        'ui' => [
            'display' => [
                'dark_mode' => env('L5_SWAGGER_UI_DARK_MODE', false),
                'doc_expansion' => env('L5_SWAGGER_UI_DOC_EXPANSION', 'none'),
                'filter' => env('L5_SWAGGER_UI_FILTERS', true),

                /*
                 * ✅ Correction principale :
                 * Forcer Swagger UI à pointer vers /docs/api-docs.json
                 * au lieu de générer /docs?api-docs.json
                 */
                'url' => env('L5_SWAGGER_UI_DOCS_URL', '/docs/api-docs.json'),
            ],

            'authorization' => [
                'persist_authorization' => env('L5_SWAGGER_UI_PERSIST_AUTHORIZATION', true),
                'oauth2' => [
                    'use_pkce_with_authorization_code_grant' => false,
                ],
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Constantes globales accessibles dans les annotations Swagger
        |--------------------------------------------------------------------------
        */
        'constants' => [
            'L5_SWAGGER_CONST_HOST' => env('L5_SWAGGER_CONST_HOST', 'https://moustapha-seck.onrender.com'),
        ],
    ],
];
