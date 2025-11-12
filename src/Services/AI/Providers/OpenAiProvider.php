<?php

namespace Silalahi\TranslationExtractor\Services\AI\Providers;

use Silalahi\TranslationExtractor\Services\AI\AbstractTranslationProvider;

class OpenAiProvider extends AbstractTranslationProvider
{
    /**
     * Get the provider name.
     */
    public function getName(): string
    {
        return 'openai';
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
        // OpenAI supports virtually all languages
        return true;
    }

    /**
     * Call the OpenAI API to translate a batch of texts.
     */
    protected function callApi(array $texts, string $targetLocale, string $sourceLocale): array
    {
        $apiKey = $this->config['api_key'];
        $model = $this->config['model'] ?? 'gpt-4o-mini';
        $temperature = $this->config['temperature'] ?? 0.3;

        $userContent = $this->buildTranslationPrompt($texts, $targetLocale, $sourceLocale);

        $response = $this->makeRequest(
            'https://api.openai.com/v1/chat/completions',
            [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $this->getSystemPrompt($targetLocale),
                    ],
                    [
                        'role' => 'user',
                        'content' => $userContent,
                    ],
                ],
                'temperature' => $temperature,
            ],
            [
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ]
        );

        return $this->parseResponse($response, $texts);
    }

    /**
     * Build the system prompt with optional domain context.
     */
    protected function getSystemPrompt(string $targetLocale): string
    {
        $domainContext = $this->config['domain_context'] ?? null;

        $prompt = "You are a professional translator.";

        if ($domainContext) {
            $prompt .= " You specialize in translating content for a {$domainContext} application.";
        }

        $prompt .= " Translate the provided strings to {$targetLocale}, maintaining the same tone, style, and technical accuracy. Return ONLY a valid JSON object with the original strings as keys and translations as values. Do not add any explanation or markdown formatting.";

        return $prompt;
    }

    /**
     * Build the user prompt with the texts to translate.
     */
    protected function buildTranslationPrompt(array $texts, string $targetLocale, string $sourceLocale): string
    {
        $jsonTexts = json_encode($texts, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return "Translate the following JSON object from {$sourceLocale} to {$targetLocale}. Maintain the keys exactly as they are, only translate the values:\n\n{$jsonTexts}";
    }

    /**
     * Parse the OpenAI API response.
     */
    protected function parseResponse(array $response, array $originalTexts): array
    {
        $content = $response['choices'][0]['message']['content'] ?? '';

        // Clean up potential markdown formatting
        $content = preg_replace('/^```json\s*/m', '', $content);
        $content = preg_replace('/\s*```$/m', '', $content);
        $content = trim($content);

        $translated = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("Failed to parse OpenAI response as JSON: " . json_last_error_msg());
        }

        // Ensure all original keys are present
        $result = [];
        foreach ($originalTexts as $key => $originalValue) {
            $result[$key] = $translated[$originalValue] ?? $translated[$key] ?? '';
        }

        return $result;
    }
}
