<?php

namespace Silalahi\TranslationExtractor;

use Illuminate\Support\ServiceProvider;
use Silalahi\TranslationExtractor\Commands\ExtractTranslationsCommand;

class TranslationExtractorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/translation-extractor.php' => config_path('translation-extractor.php'),
            ], 'translation-extractor-config');

            $this->commands([
                ExtractTranslationsCommand::class,
            ]);
        }
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/translation-extractor.php',
            'translation-extractor'
        );
    }
}
