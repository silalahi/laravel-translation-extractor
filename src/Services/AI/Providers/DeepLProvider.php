<?php

namespace Silalahi\TranslationExtractor\Services\AI\Providers;

use Silalahi\TranslationExtractor\Services\AI\AbstractTranslationProvider;

class DeepLProvider extends AbstractTranslationProvider
{
    /**
     * Locale mapping from Laravel to DeepL codes.
     */
    protected array $localeMap = [
        'en' => 'EN',
        'id' => 'ID', // Indonesian
        'de' => 'DE',
        'fr' => 'FR',
        'es' => 'ES',
        'it' => 'IT',
        'ja' => 'JA',
        'ko' => 'KO',
        'nl' => 'NL',
        'pl' => 'PL',
        'pt' => 'PT',
        'ru' => 'RU',
        'zh' => 'ZH',
        'ar' => 'AR',
        'cs' => 'CS',
        'da' => 'DA',
        'el' => 'EL',
        'et' => 'ET',
        'fi' => 'FI',
        'hu' => 'HU',
        'lt' => 'LT',
        'lv' => 'LV',
        'no' => 'NB',
        'ro' => 'RO',
        'sk' => 'SK',
        'sl' => 'SL',
        'sv' => 'SV',
        'tr' => 'TR',
        'uk' => 'UK',
    ];

    /**
     * Get the provider name.
     */
    public function getName(): string
    {
        return 'deepl';
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
        return isset($this->localeMap[$locale]);
    }

    /**
     * Call the DeepL API to translate a batch of texts.
     */
    protected function callApi(array $texts, string $targetLocale, string $sourceLocale): array
    {
        $apiKey = $this->config['api_key'];
        $apiUrl = $this->config['api_url'] ?? 'https://api-free.deepl.com/v2/translate';

        $targetLang = $this->localeMap[$targetLocale] ?? strtoupper($targetLocale);
        $sourceLang = $this->localeMap[$sourceLocale] ?? strtoupper($sourceLocale);

        // DeepL expects text as array of strings
        $textValues = array_values($texts);

        $response = $this->makeRequest(
            $apiUrl,
            [
                'auth_key' => $apiKey,
                'text' => $textValues,
                'source_lang' => $sourceLang,
                'target_lang' => $targetLang,
                'formality' => 'default',
            ]
        );

        return $this->parseResponse($response, $texts);
    }

    /**
     * Parse the DeepL API response.
     */
    protected function parseResponse(array $response, array $originalTexts): array
    {
        if (!isset($response['translations']) || !is_array($response['translations'])) {
            throw new \RuntimeException("Invalid DeepL API response structure");
        }

        $translations = $response['translations'];
        $result = [];
        $keys = array_keys($originalTexts);

        foreach ($translations as $index => $translation) {
            $key = $keys[$index] ?? null;
            if ($key !== null) {
                $result[$key] = $translation['text'] ?? '';
            }
        }

        return $result;
    }

    /**
     * Override batch size for DeepL (supports up to 50 texts per request).
     */
    protected function getBatchSize(): int
    {
        return $this->config['batch_size'] ?? 50;
    }
}
