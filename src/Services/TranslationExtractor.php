<?php

namespace Silalahi\TranslationExtractor\Services;

use Illuminate\Filesystem\Filesystem;

class TranslationExtractor
{
    protected Filesystem $files;
    protected array $config;
    protected array $translations = [];

    public function __construct(Filesystem $files, array $config)
    {
        $this->files = $files;
        $this->config = $config;
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
        $fileName = $this->config['file_name'];
        
        $langPath = lang_path($locale);
        $filePath = $langPath . DIRECTORY_SEPARATOR . $fileName;

        // Create directory if it doesn't exist
        if (!$this->files->isDirectory($langPath)) {
            $this->files->makeDirectory($langPath, 0755, true);
        }

        // Load existing translations if preserve is enabled
        if ($this->config['preserve_existing'] && $this->files->exists($filePath)) {
            $existing = include $filePath;
            if (is_array($existing)) {
                // Merge: existing translations take precedence
                $translations = array_merge($translations, $existing);
            }
        }

        // Sort keys if configured
        if ($this->config['sort_keys']) {
            ksort($translations);
        }

        // Generate PHP array content
        $content = "<?php\n\nreturn " . $this->varExport($translations, 1) . ";\n";

        $this->files->put($filePath, $content);

        return $filePath;
    }

    /**
     * Custom var_export for better formatting.
     */
    protected function varExport(array $array, int $indent = 0): string
    {
        $output = "[\n";
        $indentStr = str_repeat('    ', $indent);
        
        foreach ($array as $key => $value) {
            $output .= $indentStr . "    " . var_export($key, true) . " => ";
            
            if (is_array($value)) {
                $output .= $this->varExport($value, $indent + 1);
            } else {
                $output .= var_export($value, true);
            }
            
            $output .= ",\n";
        }
        
        $output .= $indentStr . "]";
        
        return $output;
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
        ];
    }
}
