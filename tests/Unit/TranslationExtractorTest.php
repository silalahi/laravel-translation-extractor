<?php

namespace YourVendor\TranslationExtractor\Tests\Unit;

use YourVendor\TranslationExtractor\Tests\TestCase;
use YourVendor\TranslationExtractor\Services\TranslationExtractor;
use Illuminate\Filesystem\Filesystem;

class TranslationExtractorTest extends TestCase
{
    protected TranslationExtractor $extractor;
    protected Filesystem $files;

    protected function setUp(): void
    {
        parent::setUp();

        $this->files = new Filesystem();
        
        $config = [
            'locale' => 'id',
            'file_name' => 'messages.php',
            'paths' => [resource_path('views')],
            'functions' => ['__', 'trans', '@lang'],
            'extensions' => ['php', 'blade.php'],
            'exclude' => ['vendor', 'node_modules'],
            'preserve_existing' => true,
            'sort_keys' => true,
        ];

        $this->extractor = new TranslationExtractor($this->files, $config);
    }

    /** @test */
    public function it_calculates_statistics_correctly()
    {
        $translations = [
            'Hello' => 'Halo',
            'World' => 'Dunia',
            'Untranslated' => '',
            'Another' => '',
        ];

        $stats = $this->extractor->getStats($translations);

        $this->assertEquals(4, $stats['total']);
        $this->assertEquals(2, $stats['translated']);
        $this->assertEquals(2, $stats['untranslated']);
        $this->assertEquals(50.0, $stats['percentage']);
    }

    /** @test */
    public function it_handles_empty_translations()
    {
        $translations = [];

        $stats = $this->extractor->getStats($translations);

        $this->assertEquals(0, $stats['total']);
        $this->assertEquals(0, $stats['translated']);
        $this->assertEquals(0, $stats['untranslated']);
        $this->assertEquals(0, $stats['percentage']);
    }

    /** @test */
    public function it_handles_fully_translated_content()
    {
        $translations = [
            'Hello' => 'Halo',
            'World' => 'Dunia',
            'Good morning' => 'Selamat pagi',
        ];

        $stats = $this->extractor->getStats($translations);

        $this->assertEquals(3, $stats['total']);
        $this->assertEquals(3, $stats['translated']);
        $this->assertEquals(0, $stats['untranslated']);
        $this->assertEquals(100.0, $stats['percentage']);
    }
}
