<?php

namespace Silalahi\TranslationExtractor\Services\AI;

use Silalahi\TranslationExtractor\Contracts\TranslationProviderInterface;
use Silalahi\TranslationExtractor\Services\AI\Providers\DeepLProvider;
use Silalahi\TranslationExtractor\Services\AI\Providers\GoogleTranslateProvider;
use Silalahi\TranslationExtractor\Services\AI\Providers\OpenAiProvider;

class TranslationProviderFactory
{
    /**
     * Create a translation provider instance based on configuration.
     */
    public static function make(array $config): ?TranslationProviderInterface
    {
        $provider = $config['provider'] ?? 'openai';

        if (!isset($config['providers'][$provider])) {
            return null;
        }

        $providerConfig = array_merge(
            $config['providers'][$provider],
            [
                'timeout' => $config['timeout'] ?? 30,
                'max_retries' => $config['max_retries'] ?? 2,
                'batch_size' => $config['batch_size'] ?? 20,
                'domain_context' => $config['domain_context'] ?? null,
            ]
        );

        return match ($provider) {
            'openai' => new OpenAiProvider($providerConfig),
            'deepl' => new DeepLProvider($providerConfig),
            'google' => new GoogleTranslateProvider($providerConfig),
            default => null,
        };
    }
}
