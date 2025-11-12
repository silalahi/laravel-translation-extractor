<?php

namespace Silalahi\TranslationExtractor\Tests\Feature;

use Silalahi\TranslationExtractor\Tests\TestCase;
use Illuminate\Support\Facades\File;

class ExtractTranslationsCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a temporary views directory for testing
        $viewPath = resource_path('views');
        if (!File::isDirectory($viewPath)) {
            File::makeDirectory($viewPath, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        // Clean up test files
        $langPath = lang_path('id');
        if (File::isDirectory($langPath)) {
            File::deleteDirectory($langPath);
        }

        parent::tearDown();
    }

    /** @test */
    public function it_can_extract_translations_from_views()
    {
        // Create a test view file
        $viewPath = resource_path('views/test.blade.php');
        File::put($viewPath, "{{ __('Hello World') }}\n{{ __('Welcome') }}");

        // Run the command
        $this->artisan('translations:extract')
            ->expectsOutput('🔍 Scanning for translation keys...')
            ->assertExitCode(0);

        // Check if translation file was created
        $translationFile = lang_path('id/messages.php');
        $this->assertFileExists($translationFile);

        // Check if translations were extracted
        $translations = include $translationFile;
        $this->assertArrayHasKey('Hello World', $translations);
        $this->assertArrayHasKey('Welcome', $translations);

        // Clean up
        File::delete($viewPath);
    }

    /** @test */
    public function it_can_extract_translations_for_specific_locale()
    {
        // Create a test view file
        $viewPath = resource_path('views/test.blade.php');
        File::put($viewPath, "{{ __('Test Key') }}");

        // Run the command with locale option
        $this->artisan('translations:extract', ['--locale' => 'es'])
            ->assertExitCode(0);

        // Check if translation file was created for Spanish
        $translationFile = lang_path('es/messages.php');
        $this->assertFileExists($translationFile);

        // Clean up
        File::delete($viewPath);
        if (File::isDirectory(lang_path('es'))) {
            File::deleteDirectory(lang_path('es'));
        }
    }

    /** @test */
    public function it_preserves_existing_translations()
    {
        // Create existing translation file
        $langPath = lang_path('id');
        File::makeDirectory($langPath, 0755, true);
        
        $translationFile = $langPath . '/messages.php';
        File::put($translationFile, "<?php\n\nreturn [\n    'Existing Key' => 'Kunci yang Ada',\n];");

        // Create a test view file with new key
        $viewPath = resource_path('views/test.blade.php');
        File::put($viewPath, "{{ __('Existing Key') }}\n{{ __('New Key') }}");

        // Run the command
        $this->artisan('translations:extract')
            ->assertExitCode(0);

        // Check if existing translation was preserved
        $translations = include $translationFile;
        $this->assertEquals('Kunci yang Ada', $translations['Existing Key']);
        $this->assertArrayHasKey('New Key', $translations);
        $this->assertEquals('', $translations['New Key']);

        // Clean up
        File::delete($viewPath);
    }

    /** @test */
    public function it_shows_no_keys_found_message_when_no_translations()
    {
        // Create a test view file without translation keys
        $viewPath = resource_path('views/test.blade.php');
        File::put($viewPath, "<div>Static content</div>");

        // Run the command
        $this->artisan('translations:extract')
            ->expectsOutput('⚠️  No translation keys found.')
            ->assertExitCode(0);

        // Clean up
        File::delete($viewPath);
    }
}
