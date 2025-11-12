# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel package that extracts translation keys from Blade views and PHP files, generating language files for localization. The package scans configured paths for translation function calls (`__()`, `trans()`, `@lang()`) and creates/updates JSON language files with the extracted keys.

## Package Structure

**Core Components:**
- `TranslationExtractorServiceProvider` - Laravel service provider that registers the Artisan command and publishes configuration
- `ExtractTranslationsCommand` - Artisan command that orchestrates the extraction process with CLI output
- `TranslationExtractor` - Service class that handles the actual scanning, extraction, and file generation logic

**Configuration Flow:**
The package uses a config file (`config/translation-extractor.php`) that controls:
- Target locale (determines JSON filename: `lang/{locale}.json`)
- Paths to scan (default: `resource_path('views')`)
- Translation functions to detect (default: `__`, `trans`, `@lang`)
- File extensions to scan (`.php`, `.blade.php`)
- Directories to exclude (`vendor`, `node_modules`, `storage`)
- Whether to preserve existing translations when re-running
- Whether to sort keys alphabetically

## Development Commands

**Testing:**
```bash
# Run all tests
composer test
# or
vendor/bin/phpunit

# Run specific test
vendor/bin/phpunit --filter TestMethodName

# Generate coverage report
composer test-coverage
```

**Testing the Package in a Laravel App:**
1. Add to a Laravel project via composer or local path in `composer.json`:
   ```json
   "repositories": [
       {
           "type": "path",
           "url": "../laravel-translation-extractor"
       }
   ]
   ```
2. Run `composer require silalahi/laravel-translation-extractor:@dev`
3. Publish config: `php artisan vendor:publish --tag=translation-extractor-config`
4. Run extraction: `php artisan translations:extract`

**Available Options:**
```bash
# Extract for specific locale
php artisan translations:extract --locale=id

# Force overwrite existing translations (ignore preserve_existing config)
php artisan translations:extract --force
```

## Architecture Details

**Extraction Process:**
1. Command reads configuration (with CLI option overrides)
2. `TranslationExtractor::extract()` scans directories recursively
3. For each file matching the configured extensions, regex patterns extract translation keys
4. Keys containing variables (`$`) or dots (`.`) are skipped to avoid dynamic translations
5. Results are sorted alphabetically if configured
6. Existing translations are merged (preserved) unless `--force` is used
7. Output file is generated as JSON with pretty-print formatting and Unicode preservation

**Translation File Output:**
- Target path: `lang/{locale}.json` (e.g., `lang/id.json`)
- Format: JSON object with keys as translation strings and empty string values for untranslated items
- When `preserve_existing` is true, existing translated values are maintained and only new keys are added
- Uses `JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE` flags for readable output with international characters

**AI Translation Flow (Optional, v0.3.0+):**
1. After extraction and merging, if `--translate` flag is present and AI is configured:
2. `TranslationExtractor::translateMissingKeys()` identifies keys with empty values
3. Keys are optionally grouped by category (auth, medical, finance, ui, etc.) for consistent terminology
4. `TranslationProviderFactory::make()` creates the appropriate provider (OpenAI, DeepL, or Google)
5. Provider batches keys and calls respective AI API
6. Translated values replace empty strings while preserving manually edited translations
7. Statistics track AI-translated count and any failures

**AI Translation Architecture (v0.3.0+):**
- **Interface**: `Contracts/TranslationProviderInterface.php` - Common contract for all providers
- **Base Class**: `Services/AI/AbstractTranslationProvider.php` - Shared logic (batching, error handling, retries)
- **Factory**: `Services/AI/TranslationProviderFactory.php` - Creates provider instances based on config
- **Providers**:
  - `Services/AI/Providers/OpenAiProvider.php` - GPT-based translation with context awareness
  - `Services/AI/Providers/DeepLProvider.php` - DeepL API integration
  - `Services/AI/Providers/GoogleTranslateProvider.php` - Google Translate API integration
- **Error Handling**: Graceful degradation - logs errors but continues with empty values
- **Context Features**: Domain context and key grouping for consistent terminology

**Pattern Matching:**
The extraction uses two regex patterns per function:
1. `{function}\s*\(\s*['"](.+?)['"]\s*\)` - Matches `__('key')` or `__("key")`
2. `@{function}\s*\(\s*['"](.+?)['"]\s*\)` - Matches Blade directives like `@lang('key')`

## Testing Architecture

Tests use Orchestra Testbench to provide a Laravel environment without a full Laravel installation. The `TestCase` base class sets up:
- Package service provider registration
- Default configuration values for testing
- Isolated environment for each test

Test categories:
- **Unit tests** (`tests/Unit/`) - Test `TranslationExtractor` service in isolation
- **Feature tests** (`tests/Feature/`) - Test the full Artisan command integration

## Important Conventions

- All translation keys are stored as strings (no nested objects)
- The package intentionally skips dynamic keys (containing variables or dots)
- Generated JSON files use pretty-print formatting with 4-space indentation
- Statistics tracking helps users identify translation progress
- The package never deletes existing translations by default (safety-first approach)