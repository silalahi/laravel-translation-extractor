<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Locale
    |--------------------------------------------------------------------------
    |
    | The default locale for translation extraction. This will be used as
    | the JSON filename (e.g., 'id' creates lang/id.json).
    |
    */
    'locale' => 'id',

    /*
    |--------------------------------------------------------------------------
    | Paths to Scan
    |--------------------------------------------------------------------------
    |
    | Array of directories to scan for translation keys.
    |
    */
    'paths' => [
        resource_path('views'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Translation Functions
    |--------------------------------------------------------------------------
    |
    | Array of function names to look for when extracting translations.
    |
    */
    'functions' => [
        '__',
        'trans',
        '@lang',
    ],

    /*
    |--------------------------------------------------------------------------
    | File Extensions
    |--------------------------------------------------------------------------
    |
    | File extensions to scan for translation keys.
    |
    */
    'extensions' => [
        'php',
        'blade.php',
    ],

    /*
    |--------------------------------------------------------------------------
    | Exclude Directories
    |--------------------------------------------------------------------------
    |
    | Directories to exclude from scanning.
    |
    */
    'exclude' => [
        'vendor',
        'node_modules',
        'storage',
    ],

    /*
    |--------------------------------------------------------------------------
    | Preserve Existing Translations
    |--------------------------------------------------------------------------
    |
    | If enabled, the package will preserve existing translations and only
    | add new keys without translation values.
    |
    */
    'preserve_existing' => true,

    /*
    |--------------------------------------------------------------------------
    | Sort Keys
    |--------------------------------------------------------------------------
    |
    | Sort translation keys alphabetically.
    |
    */
    'sort_keys' => true,

    /*
    |--------------------------------------------------------------------------
    | AI Translation
    |--------------------------------------------------------------------------
    |
    | Enable automatic translation using AI providers. This feature requires
    | an API key for the chosen provider. Translations will only be generated
    | for keys with empty values, preserving any manually edited translations.
    |
    */
    'ai_translation' => [
        'enabled' => env('TRANSLATION_AI_ENABLED', false),

        // Provider: 'openai', 'deepl', or 'google'
        'provider' => env('TRANSLATION_AI_PROVIDER', 'openai'),

        // Source language for translations (usually 'en')
        'source_locale' => env('TRANSLATION_AI_SOURCE', 'en'),

        // Optional: Domain context to improve translation accuracy
        // Example: 'medical clinic management', 'e-commerce platform', etc.
        'domain_context' => env('TRANSLATION_AI_DOMAIN'),

        // Group related keys together for consistent terminology
        'group_related_keys' => true,

        // Number of texts to translate in a single API call
        'batch_size' => 20,

        // API request timeout in seconds
        'timeout' => 30,

        // Maximum number of retry attempts on failure
        'max_retries' => 2,

        // Provider-specific configuration
        'providers' => [
            'openai' => [
                'api_key' => env('OPENAI_API_KEY'),
                'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
                'temperature' => 0.3, // Lower = more consistent, Higher = more creative
            ],

            'deepl' => [
                'api_key' => env('DEEPL_API_KEY'),
                'api_url' => env('DEEPL_API_URL', 'https://api-free.deepl.com/v2/translate'),
            ],

            'google' => [
                'api_key' => env('GOOGLE_TRANSLATE_API_KEY'),
            ],
        ],
    ],
];
