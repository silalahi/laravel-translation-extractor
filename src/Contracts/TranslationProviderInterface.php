<?php

namespace Silalahi\TranslationExtractor\Contracts;

interface TranslationProviderInterface
{
    /**
     * Translate a single text string.
     */
    public function translate(string $text, string $targetLocale, string $sourceLocale = 'en'): string;

    /**
     * Translate multiple text strings in a single batch.
     *
     * @param  array  $texts  Array of strings to translate
     * @return array Array of translated strings with same keys
     */
    public function translateBatch(array $texts, string $targetLocale, string $sourceLocale = 'en'): array;

    /**
     * Check if the provider is properly configured.
     */
    public function isConfigured(): bool;

    /**
     * Check if the provider supports the given locale.
     */
    public function supports(string $locale): bool;

    /**
     * Get the provider name.
     */
    public function getName(): string;
}
