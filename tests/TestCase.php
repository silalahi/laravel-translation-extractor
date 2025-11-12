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
        ]);
    }
}
