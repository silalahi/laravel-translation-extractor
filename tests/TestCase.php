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
        $app['config']->set('translation-extractor.locale', 'id');
        $app['config']->set('translation-extractor.file_name', 'messages.php');
    }
}
