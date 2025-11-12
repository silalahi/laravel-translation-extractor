# Laravel Translation Extractor

[![Latest Version on Packagist](https://img.shields.io/packagist/v/silalahi/laravel-translation-extractor.svg?style=flat-square)](https://packagist.org/packages/silalahi/laravel-translation-extractor)
[![Total Downloads](https://img.shields.io/packagist/dt/silalahi/laravel-translation-extractor.svg?style=flat-square)](https://packagist.org/packages/silalahi/laravel-translation-extractor)

A Laravel package to automatically extract translation keys from your views and generate language files. Say goodbye to manually creating translation files!

## Features

- 🔍 **Automatic Extraction**: Scans your views for `__()`, `trans()`, and `@lang()` functions
- 🌍 **Multi-locale Support**: Generate translation files for any locale
- 📁 **Configurable Paths**: Scan custom directories beyond just views
- 🔄 **Preserve Existing**: Keeps your existing translations intact when re-running
- 📊 **Statistics**: Shows translation progress and completion percentage
- ⚙️ **Highly Configurable**: Customize function names, paths, and more

## Requirements

- PHP 8.1 or higher
- Laravel 10.x, 11.x, or 12.x

## Installation

You can install the package via composer:

```bash
composer require silalahi/laravel-translation-extractor
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag=translation-extractor-config
```

This will create a `config/translation-extractor.php` file.

## Usage

### Basic Usage

Extract translations for the default locale (configured in config file):

```bash
php artisan translations:extract
```

### Extract for Specific Locale

```bash
php artisan translations:extract --locale=id
```

### Force Overwrite

By default, existing translations are preserved. Use `--force` to overwrite:

```bash
php artisan translations:extract --force
```

## How It Works

The package scans your view files looking for translation function calls:

```php
// In your Blade views
__('I love programming.')
{{ __('Welcome to our website') }}
@lang('Hello World')
trans('Good morning')
```

It then generates a JSON file in your `lang` directory:

```json
// lang/id.json
{
    "Good morning": "",
    "Hello World": "",
    "I love programming.": "",
    "Welcome to our website": ""
}
```

You can then add your translations:

```json
// lang/id.json
{
    "Good morning": "Selamat pagi",
    "Hello World": "Halo Dunia",
    "I love programming.": "Saya suka pemrograman",
    "Welcome to our website": "Selamat datang di website kami"
}
```

## Configuration

The `config/translation-extractor.php` file provides extensive configuration options:

```php
return [
    // Default locale for extraction (creates lang/{locale}.json)
    'locale' => 'id',

    // Directories to scan
    'paths' => [
        resource_path('views'),
        // Add more paths as needed
    ],

    // Translation functions to look for
    'functions' => [
        '__',
        'trans',
        '@lang',
    ],

    // File extensions to scan
    'extensions' => [
        'php',
        'blade.php',
    ],

    // Directories to exclude
    'exclude' => [
        'vendor',
        'node_modules',
        'storage',
    ],

    // Preserve existing translations
    'preserve_existing' => true,

    // Sort keys alphabetically
    'sort_keys' => true,
];
```

## Advanced Usage

### Scanning Custom Directories

You can scan additional directories by modifying the config:

```php
'paths' => [
    resource_path('views'),
    app_path('Http/Controllers'), // Scan controllers too
    app_path('Services'),
],
```

### Custom Translation Functions

If you use custom translation helper functions:

```php
'functions' => [
    '__',
    'trans',
    '@lang',
    'translate', // Your custom function
    'my_trans',
],
```

### Multiple Locales Workflow

Extract for multiple locales in sequence:

```bash
php artisan translations:extract --locale=id
php artisan translations:extract --locale=es
php artisan translations:extract --locale=fr
```

## Tips & Best Practices

1. **Run Regularly**: Extract translations during development to catch new keys
2. **Version Control**: Commit the generated files to track translation progress
3. **CI/CD Integration**: Add extraction to your CI pipeline to ensure no keys are missed
4. **Translation Services**: The generated JSON files are compatible with most translation services
5. **Keep Keys Simple**: Use clear, descriptive translation keys in English

## Example Output

```
🔍 Scanning for translation keys...

✅ Found 24 unique translation keys.

📝 Sample keys:
   - Welcome to our application
   - Login to continue
   - Email Address
   - Password
   - Remember Me
   ... and 19 more

💾 Translations saved to: /path/to/your/project/lang/id.json

📊 Statistics:
+--------------+-------+
| Metric       | Value |
+--------------+-------+
| Total Keys   | 24    |
| Translated   | 18    |
| Untranslated | 6     |
| Progress     | 75%   |
+--------------+-------+

💡 Tip: Edit /path/to/your/project/lang/id.json to add translations for untranslated keys.
```

## Testing

```bash
composer test
```

## Security

If you discover any security related issues, please email your.email@example.com instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Support

If you find this package helpful, please consider:

- ⭐ Starring the repository
- 🐛 Reporting bugs
- 💡 Suggesting new features
- 🔀 Submitting pull requests

## Roadmap

- [ ] Support for nested translation keys (dot notation)
- [ ] Integration with translation services (Google Translate API, DeepL)
- [ ] GUI for managing translations
- [ ] Automatic translation using AI
- [ ] Support for pluralization rules
- [ ] Vue.js and React component scanning
