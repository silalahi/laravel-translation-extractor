# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel package that extracts translation keys from Blade views and PHP files, generating language files for localization. The package scans configured paths for translation function calls (`__()`, `trans()`, `@lang()`) and creates/updates PHP language files with the extracted keys.

## Package Structure

**Core Components:**
- `TranslationExtractorServiceProvider` - Laravel service provider that registers the Artisan command and publishes configuration
- `ExtractTranslationsCommand` - Artisan command that orchestrates the extraction process with CLI output
- `TranslationExtractor` - Service class that handles the actual scanning, extraction, and file generation logic

**Configuration Flow:**
The package uses a config file (`config/translation-extractor.php`) that controls:
- Target locale and file name
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
7. Output file is generated using custom `varExport()` for consistent formatting

**Translation File Output:**
- Target path: `lang/{locale}/{file_name}` (e.g., `lang/id/messages.php`)
- Format: PHP array with keys as translation strings and empty string values for untranslated items
- When `preserve_existing` is true, existing translated values are maintained and only new keys are added

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

- All translation keys are stored as strings (no nested arrays)
- The package intentionally skips dynamic keys (containing variables or dots)
- Generated files use 4-space indentation
- Statistics tracking helps users identify translation progress
- The package never deletes existing translations by default (safety-first approach)