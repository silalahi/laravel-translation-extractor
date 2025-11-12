<?php

namespace Silalahi\TranslationExtractor\Services;

use Illuminate\Filesystem\Filesystem;
use Silalahi\TranslationExtractor\Contracts\TranslationProviderInterface;
use Silalahi\TranslationExtractor\Services\AI\TranslationProviderFactory;

class TranslationExtractor
{
    protected Filesystem $files;
    protected array $config;
    protected array $translations = [];
    protected ?TranslationProviderInterface $aiProvider = null;
    protected int $aiTranslatedCount = 0;
    protected int $aiFailedCount = 0;

    public function __construct(Filesystem $files, array $config)
    {
        $this->files = $files;
        $this->config = $config;

        // Initialize AI provider if enabled
        if ($this->isAiEnabled()) {
            $this->aiProvider = TranslationProviderFactory::make($config['ai_translation']);
        }
    }

    /**
     * Extract translations from configured paths.
     */
    public function extract(): array
    {
        $this->translations = [];

        foreach ($this->config['paths'] as $path) {
            if ($this->files->isDirectory($path)) {
                $this->scanDirectory($path);
            }
        }

        if ($this->config['sort_keys']) {
            ksort($this->translations);
        }

        return $this->translations;
    }

    /**
     * Scan directory recursively for translation keys.
     */
    protected function scanDirectory(string $directory): void
    {
        $files = $this->files->allFiles($directory);

        foreach ($files as $file) {
            // Check if file should be excluded (relative to the scanned directory)
            $relativePath = str_replace($directory, '', $file->getPathname());

            $excluded = false;
            foreach ($this->config['exclude'] as $exclude) {
                if (str_contains($relativePath, DIRECTORY_SEPARATOR . $exclude . DIRECTORY_SEPARATOR) ||
                    str_starts_with($relativePath, DIRECTORY_SEPARATOR . $exclude . DIRECTORY_SEPARATOR)) {
                    $excluded = true;
                    break;
                }
            }

            if ($excluded) {
                continue;
            }

            // Check file extension
            $fileName = $file->getFilename();

            $shouldScan = false;
            foreach ($this->config['extensions'] as $allowedExtension) {
                if (str_ends_with($fileName, '.' . $allowedExtension)) {
                    $shouldScan = true;
                    break;
                }
            }

            if ($shouldScan) {
                $this->scanFile($file->getPathname());
            }
        }
    }

    /**
     * Scan a single file for translation keys.
     */
    protected function scanFile(string $filePath): void
    {
        $content = $this->files->get($filePath);

        foreach ($this->config['functions'] as $function) {
            $this->extractFromFunction($content, $function);
        }
    }

    /**
     * Extract translation keys from a specific function.
     */
    protected function extractFromFunction(string $content, string $function): void
    {
        $patterns = [
            // __('key') or __("key")
            "/{$function}\s*\(\s*['\"](.+?)['\"]\s*\)/",
            // @lang('key') or @lang("key")
            "/@{$function}\s*\(\s*['\"](.+?)['\"]\s*\)/",
        ];

        foreach ($patterns as $pattern) {
            preg_match_all($pattern, $content, $matches);

            if (!empty($matches[1])) {
                foreach ($matches[1] as $key) {
                    // Skip keys with variables or concatenation
                    if (!str_contains($key, '$') && !str_contains($key, '.')) {
                        $this->translations[$key] = '';
                    }
                }
            }
        }
    }

    /**
     * Save translations to file.
     */
    public function saveTranslations(array $translations, string $locale = null): string
    {
        $locale = $locale ?? $this->config['locale'];
        $filePath = lang_path($locale . '.json');

        // Create lang directory if it doesn't exist
        $langPath = dirname($filePath);
        if (!$this->files->isDirectory($langPath)) {
            $this->files->makeDirectory($langPath, 0755, true);
        }

        // Load existing translations if preserve is enabled
        if ($this->config['preserve_existing'] && $this->files->exists($filePath)) {
            $existingContent = $this->files->get($filePath);
            $existing = json_decode($existingContent, true);
            if (is_array($existing)) {
                // Merge: existing translations take precedence
                $translations = array_merge($translations, $existing);
            }
        }

        // AI Translation: Translate missing keys
        if ($this->shouldTranslate()) {
            $translations = $this->translateMissingKeys($translations, $locale);
        }

        // Sort keys if configured
        if ($this->config['sort_keys']) {
            ksort($translations);
        }

        // Generate JSON content with pretty print and preserve Unicode characters
        $content = json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

        $this->files->put($filePath, $content);

        return $filePath;
    }

    /**
     * Get statistics about extracted translations.
     */
    public function getStats(array $translations): array
    {
        $total = count($translations);
        $translated = count(array_filter($translations, fn($value) => !empty($value)));
        $untranslated = $total - $translated;

        return [
            'total' => $total,
            'translated' => $translated,
            'untranslated' => $untranslated,
            'percentage' => $total > 0 ? round(($translated / $total) * 100, 2) : 0,
            'ai_translated' => $this->aiTranslatedCount,
            'ai_failed' => $this->aiFailedCount,
        ];
    }

    /**
     * Check if AI translation is enabled and properly configured.
     */
    protected function isAiEnabled(): bool
    {
        return isset($this->config['ai_translation']['enabled']) &&
               $this->config['ai_translation']['enabled'] === true;
    }

    /**
     * Check if AI translation should be performed.
     */
    protected function shouldTranslate(): bool
    {
        return $this->aiProvider !== null &&
               $this->aiProvider->isConfigured();
    }

    /**
     * Translate keys with empty values using AI.
     */
    protected function translateMissingKeys(array $translations, string $locale): array
    {
        // Reset counters
        $this->aiTranslatedCount = 0;
        $this->aiFailedCount = 0;

        // Find keys that need translation (empty values)
        $keysToTranslate = [];
        foreach ($translations as $key => $value) {
            if (empty($value)) {
                $keysToTranslate[] = $key;
            }
        }

        if (empty($keysToTranslate)) {
            return $translations;
        }

        $sourceLocale = $this->config['ai_translation']['source_locale'] ?? 'en';

        // Group related keys if enabled
        if ($this->config['ai_translation']['group_related_keys'] ?? false) {
            $groups = $this->groupKeysByCategory($keysToTranslate);

            foreach ($groups as $category => $keys) {
                if (empty($keys)) {
                    continue;
                }

                $textsToTranslate = array_combine($keys, $keys);
                $translated = $this->aiProvider->translateBatch($textsToTranslate, $locale, $sourceLocale);

                foreach ($translated as $key => $translatedValue) {
                    if (!empty($translatedValue)) {
                        $translations[$key] = $translatedValue;
                        $this->aiTranslatedCount++;
                    } else {
                        $this->aiFailedCount++;
                    }
                }
            }
        } else {
            // Translate all keys together
            $textsToTranslate = array_combine($keysToTranslate, $keysToTranslate);
            $translated = $this->aiProvider->translateBatch($textsToTranslate, $locale, $sourceLocale);

            foreach ($translated as $key => $translatedValue) {
                if (!empty($translatedValue)) {
                    $translations[$key] = $translatedValue;
                    $this->aiTranslatedCount++;
                } else {
                    $this->aiFailedCount++;
                }
            }
        }

        return $translations;
    }

    /**
     * Group translation keys by category for consistent terminology.
     */
    protected function groupKeysByCategory(array $keys): array
    {
        $groups = [
            'auth' => [],
            'validation' => [],
            'medical' => [],
            'finance' => [],
            'ui' => [],
            'other' => [],
        ];

        foreach ($keys as $key) {
            $category = $this->detectKeyCategory($key);
            $groups[$category][] = $key;
        }

        return array_filter($groups);
    }

    /**
     * Detect the category of a translation key based on patterns.
     */
    protected function detectKeyCategory(string $key): string
    {
        $patterns = [
            'auth' => '/password|login|email|register|verify|logout|account|2fa|recovery|authentication/i',
            'validation' => '/required|invalid|must|should|error|warning|confirm/i',
            'medical' => '/patient|treatment|clinic|medicine|doctor|diagnosis|appointment|medical|health|prescription/i',
            'finance' => '/payment|invoice|price|cost|bill|finance|sales|revenue|transaction|receipt/i',
            'ui' => '/save|cancel|delete|update|create|search|back|continue|submit|edit|view|hide|show|settings|profile/i',
        ];

        foreach ($patterns as $category => $pattern) {
            if (preg_match($pattern, $key)) {
                return $category;
            }
        }

        return 'other';
    }
}
