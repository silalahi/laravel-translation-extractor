<?php

namespace Silalahi\TranslationExtractor\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Silalahi\TranslationExtractor\Contracts\TranslationProviderInterface;

abstract class AbstractTranslationProvider implements TranslationProviderInterface
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Translate multiple text strings in a single batch.
     */
    public function translateBatch(array $texts, string $targetLocale, string $sourceLocale = 'en'): array
    {
        if (empty($texts)) {
            return [];
        }

        if (!$this->isConfigured()) {
            Log::warning("Translation provider {$this->getName()} is not configured");
            return array_fill_keys(array_keys($texts), '');
        }

        if (!$this->supports($targetLocale)) {
            Log::warning("Translation provider {$this->getName()} does not support locale: {$targetLocale}");
            return array_fill_keys(array_keys($texts), '');
        }

        $batchSize = $this->getBatchSize();
        $chunks = array_chunk($texts, $batchSize, true);
        $results = [];

        foreach ($chunks as $chunk) {
            try {
                $translated = $this->callApi($chunk, $targetLocale, $sourceLocale);
                $results = array_merge($results, $translated);
            } catch (\Exception $e) {
                Log::error("Translation failed for provider {$this->getName()}", [
                    'error' => $e->getMessage(),
                    'locale' => $targetLocale,
                    'keys_count' => count($chunk),
                ]);

                // Fill with empty strings for failed translations
                $results = array_merge($results, array_fill_keys(array_keys($chunk), ''));
            }
        }

        return $results;
    }

    /**
     * Translate a single text string.
     */
    public function translate(string $text, string $targetLocale, string $sourceLocale = 'en'): string
    {
        $result = $this->translateBatch([$text], $targetLocale, $sourceLocale);
        return $result[0] ?? '';
    }

    /**
     * Make HTTP request with retry logic.
     */
    protected function makeRequest(string $url, array $payload, array $headers = []): mixed
    {
        $timeout = $this->config['timeout'] ?? 30;
        $maxRetries = $this->config['max_retries'] ?? 2;

        $response = Http::timeout($timeout)
            ->retry($maxRetries, 100, function ($exception, $request) {
                // Retry on timeout or 5xx errors
                return $exception instanceof \Illuminate\Http\Client\ConnectionException ||
                       ($exception instanceof \Illuminate\Http\Client\RequestException &&
                        $exception->response->status() >= 500);
            })
            ->withHeaders($headers)
            ->post($url, $payload);

        if ($response->failed()) {
            throw new \RuntimeException(
                "API request failed: {$response->status()} - {$response->body()}"
            );
        }

        return $response->json();
    }

    /**
     * Get the batch size for this provider.
     */
    protected function getBatchSize(): int
    {
        return $this->config['batch_size'] ?? 20;
    }

    /**
     * Call the translation API for a batch of texts.
     * Must be implemented by each provider.
     */
    abstract protected function callApi(array $texts, string $targetLocale, string $sourceLocale): array;
}
