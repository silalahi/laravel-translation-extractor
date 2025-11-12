<?php

namespace Silalahi\TranslationExtractor\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Silalahi\TranslationExtractor\TranslationExtractorServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            TranslationExtractorServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup default configuration
        $app['config']->set('translation-extractor', [
            'locale' => 'id',
            'paths' => [
                resource_path('views'),
            ],
            'functions' => [
                '__',
                'trans',
                '@lang',
            ],
            'extensions' => [
                'php',
                'blade.php',
            ],
            'exclude' => [
                'vendor',
                'node_modules',
                'storage',
            ],
            'preserve_existing' => true,
            'sort_keys' => true,
            'ai_translation' => [
                'enabled' => false,
                'provider' => 'openai',
                'source_locale' => 'en',
                'domain_context' => null,
                'group_related_keys' => true,
                'batch_size' => 20,
                'timeout' => 30,
                'max_retries' => 2,
                'providers' => [
                    'openai' => [
                        'api_key' => null,
                        'model' => 'gpt-4o-mini',
                        'temperature' => 0.3,
                    ],
                    'deepl' => [
                        'api_key' => null,
                        'api_url' => 'https://api-free.deepl.com/v2/translate',
                    ],
                    'google' => [
                        'api_key' => null,
                    ],
                ],
            ],
        ]);
    }
}
