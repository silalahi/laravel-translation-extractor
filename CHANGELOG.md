# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.2.0] - 2025-01-12

### Changed
- **BREAKING**: Translation output format changed from PHP arrays to JSON
- **BREAKING**: File structure changed from `lang/{locale}/messages.php` to `lang/{locale}.json`
- **BREAKING**: Removed `file_name` configuration option (no longer needed with flat structure)
- Updated command description to reflect JSON output format
- Improved output formatting with `JSON_PRETTY_PRINT` and `JSON_UNESCAPED_UNICODE` flags

### Removed
- **BREAKING**: Removed `varExport()` method (replaced with native `json_encode()`)
- **BREAKING**: Removed `file_name` config option

### Migration Guide
To migrate from v0.1.x to v0.2.0:
1. Manually convert existing PHP translation files to JSON format:
   - Before: `lang/id/messages.php` with PHP array
   - After: `lang/id.json` with JSON object
2. Remove the `file_name` option from your `config/translation-extractor.php`
3. Update any scripts or processes that depend on PHP translation files

Example conversion:
```php
// Old format (lang/id/messages.php)
<?php
return [
    'Welcome' => 'Selamat datang',
    'Hello' => 'Halo',
];
```

```json
// New format (lang/id.json)
{
    "Welcome": "Selamat datang",
    "Hello": "Halo"
}
```

## [0.1.1] - 2025-01-12

### Added
- Laravel 12.x support
- PHPUnit 11.x support
- Orchestra Testbench 10.x support

## [0.1.0] - 2025-01-12

### Added
- Initial release of Laravel Translation Extractor
- `translations:extract` Artisan command for extracting translation keys
- Support for `__()`, `trans()`, and `@lang()` translation functions
- Configurable paths, file extensions, and functions to scan
- Preservation of existing translations when re-running extraction
- Alphabetical sorting of translation keys
- Statistics display showing translation progress (total, translated, untranslated, percentage)
- Multi-locale support with `--locale` option
- Force overwrite with `--force` option
- Smart exclusion of directories (vendor, node_modules, storage)
- Automatic skipping of dynamic keys with variables or concatenation
- Beautiful CLI output with color-coded messages and emojis
- Configuration file publishing via `vendor:publish`
- Comprehensive test suite with 7 tests and 25 assertions
- Full Laravel 10.x and 11.x support
- PHP 8.1+ compatibility
- Automatic package discovery via Laravel service provider
- Custom `varExport()` for clean, readable PHP array output

### Fixed
- File extension matching for compound extensions like `.blade.php`
- Exclude path logic using relative paths to prevent false exclusions
- Statistics calculation using saved translations instead of fresh extracts

[Unreleased]: https://github.com/silalahi/laravel-translation-extractor/compare/v0.2.0...HEAD
[0.2.0]: https://github.com/silalahi/laravel-translation-extractor/compare/v0.1.1...v0.2.0
[0.1.1]: https://github.com/silalahi/laravel-translation-extractor/compare/v0.1.0...v0.1.1
[0.1.0]: https://github.com/silalahi/laravel-translation-extractor/releases/tag/v0.1.0
