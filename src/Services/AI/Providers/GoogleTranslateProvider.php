<?php

namespace Silalahi\TranslationExtractor\Services\AI\Providers;

use Silalahi\TranslationExtractor\Services\AI\AbstractTranslationProvider;

class GoogleTranslateProvider extends AbstractTranslationProvider
{
    /**
     * Get the provider name.
     */
    public function getName(): string
    {
        return 'google';
    }

    /**
     * Check if the provider is properly configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->config['api_key']);
    }

    /**
     * Check if the provider supports the given locale.
     */
    public function supports(string $locale): bool
    {
        // Google Translate supports virtually all languages
        return true;
    }

    /**
     * Call the Google Translate API to translate a batch of texts.
     */
    protected function callApi(array $texts, string $targetLocale, string $sourceLocale): array
    {
        $apiKey = $this->config['api_key'];
        $apiUrl = 'https://translation.googleapis.com/language/translate/v2';

        // Google Translate expects text as array of strings
        $textValues = array_values($texts);

        $response = $this->makeRequest(
            $apiUrl,
            [
                'q' => $textValues,
                'target' => $targetLocale,
                'source' => $sourceLocale,
                'format' => 'text',
                'key' => $apiKey,
            ]
        );

        return $this->parseResponse($response, $texts);
    }

    /**
     * Parse the Google Translate API response.
     */
    protected function parseResponse(array $response, array $originalTexts): array
    {
        if (!isset($response['data']['translations']) || !is_array($response['data']['translations'])) {
            throw new \RuntimeException("Invalid Google Translate API response structure");
        }

        $translations = $response['data']['translations'];
        $result = [];
        $keys = array_keys($originalTexts);

        foreach ($translations as $index => $translation) {
            $key = $keys[$index] ?? null;
            if ($key !== null) {
                $result[$key] = $translation['translatedText'] ?? '';
            }
        }

        return $result;
    }

    /**
     * Override batch size for Google Translate (supports up to 128 texts per request).
     */
    protected function getBatchSize(): int
    {
        return $this->config['batch_size'] ?? 100;
    }
}
