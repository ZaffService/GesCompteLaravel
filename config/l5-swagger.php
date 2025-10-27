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
                 * Route d'accès à l'interface Swagger UI
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
                'swagger_ui_assets_path' => env('L5_SWAGGER_UI_ASSETS_PATH', 'vendor/swagger-api/'),

                /*
                 * Nom du fichier JSON généré par L5 Swagger
                 * Ceci créera l'URL : /docs/api-docs.json
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
            /*
             * Route pour l'interface Swagger UI
             */
            'docs' => 'docs',
            
            /*
             * Route pour le callback OAuth2
             */
            'oauth2_callback' => 'api/oauth2-callback',
            
            /*
             * Middleware à appliquer sur les routes
             */
            'middleware' => [
                'api' => [],
                'asset' => [],
                'docs' => [],
                'oauth2_callback' => [],
            ],
            
            /*
             * Options de groupe de routes
             */
            'group_options' => [],
        ],

        /*
        |--------------------------------------------------------------------------
        | Chemins et structure des fichiers Swagger
        |--------------------------------------------------------------------------
        */
        'paths' => [
            /*
             * Dossier où le fichier api-docs.json sera stocké
             */
            'docs' => storage_path('api-docs'),
            
            /*
             * Dossier contenant les vues Blade de Swagger UI
             */
            'views' => base_path('resources/views/vendor/l5-swagger'),
            
            /*
             * URL de base pour l'API
             */
            'base' => env('L5_SWAGGER_BASE_PATH', 'https://moustapha-seck.onrender.com'),
            
            /*
             * Dossiers à exclure du scan
             */
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
        | Sécurité de l'API (JWT / Bearer)
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
            'security' => [
                [
                    'bearerAuth' => [],
                ],
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Génération et options globales
        |--------------------------------------------------------------------------
        */
        
        /*
         * Générer la documentation à chaque requête (true) 
         * ou seulement avec la commande artisan (false)
         */
        'generate_always' => env('L5_SWAGGER_GENERATE_ALWAYS', false),
        
        /*
         * Générer également une copie YAML
         */
        'generate_yaml_copy' => env('L5_SWAGGER_GENERATE_YAML_COPY', false),
        
        /*
         * Utiliser le proxy configuré
         */
        'proxy' => false,
        
        /*
         * URL de configuration supplémentaire
         */
        'additional_config_url' => null,
        
        /*
         * Tri des opérations (null, 'alpha', 'method')
         */
        'operations_sort' => env('L5_SWAGGER_OPERATIONS_SORT', null),
        
        /*
         * URL du validateur Swagger (null pour désactiver)
         */
        'validator_url' => null,

        /*
        |--------------------------------------------------------------------------
        | Interface Swagger UI
        |--------------------------------------------------------------------------
        */
        'ui' => [
            'display' => [
                /*
                 * Mode sombre
                 */
                'dark_mode' => env('L5_SWAGGER_UI_DARK_MODE', false),
                
                /*
                 * Expansion de la documentation (none, list, full)
                 */
                'doc_expansion' => env('L5_SWAGGER_UI_DOC_EXPANSION', 'none'),
                
                /*
                 * Afficher le filtre de recherche
                 */
                'filter' => env('L5_SWAGGER_UI_FILTERS', true),
            ],

            'authorization' => [
                /*
                 * Persister l'autorisation entre les rechargements
                 */
                'persist_authorization' => env('L5_SWAGGER_UI_PERSIST_AUTHORIZATION', true),
                
                /*
                 * Configuration OAuth2
                 */
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