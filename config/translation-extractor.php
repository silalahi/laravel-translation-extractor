<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Locale
    |--------------------------------------------------------------------------
    |
    | The default locale for translation extraction. This will be used as
    | the target language directory name.
    |
    */
    'locale' => 'id',

    /*
    |--------------------------------------------------------------------------
    | Translation File Name
    |--------------------------------------------------------------------------
    |
    | The name of the PHP file where translations will be stored.
    |
    */
    'file_name' => 'messages.php',

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
    | Auto Translate
    |--------------------------------------------------------------------------
    |
    | If enabled, the package will attempt to preserve existing translations
    | and only add new keys without translation values.
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
];
