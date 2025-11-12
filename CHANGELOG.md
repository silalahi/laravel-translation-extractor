# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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

[Unreleased]: https://github.com/silalahi/laravel-translation-extractor/compare/v0.1.1...HEAD
[0.1.1]: https://github.com/silalahi/laravel-translation-extractor/compare/v0.1.0...v0.1.1
[0.1.0]: https://github.com/silalahi/laravel-translation-extractor/releases/tag/v0.1.0
