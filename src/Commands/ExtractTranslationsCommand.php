<?php

namespace Silalahi\TranslationExtractor\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Silalahi\TranslationExtractor\Services\TranslationExtractor;

class ExtractTranslationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translations:extract
                            {--locale= : The locale to extract translations for}
                            {--force : Overwrite existing translations}
                            {--translate : Enable AI translation for empty keys}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Extract translation keys from views and generate JSON language files';

    protected TranslationExtractor $extractor;

    /**
     * Execute the console command.
     */
    public function handle(Filesystem $files): int
    {
        $this->info('🔍 Scanning for translation keys...');
        $this->newLine();

        $config = config('translation-extractor');

        // Override locale if provided
        if ($locale = $this->option('locale')) {
            $config['locale'] = $locale;
        }

        // Override preserve_existing if force is provided
        if ($this->option('force')) {
            $config['preserve_existing'] = false;
        }

        // Enable AI translation if --translate flag is provided
        if ($this->option('translate')) {
            $config['ai_translation']['enabled'] = true;
        }

        $this->extractor = new TranslationExtractor($files, $config);

        // Show AI translation status if enabled
        if ($config['ai_translation']['enabled'] ?? false) {
            $provider = $config['ai_translation']['provider'] ?? 'openai';
            $this->info("🤖 AI Translation enabled ({$provider})");
            $this->newLine();
        }

        // Extract translations
        $translations = $this->extractor->extract();

        if (empty($translations)) {
            $this->warn('⚠️  No translation keys found.');
            return self::SUCCESS;
        }

        $this->info('✅ Found ' . count($translations) . ' unique translation keys.');
        $this->newLine();

        // Display some sample keys
        $this->info('📝 Sample keys:');
        $samples = array_slice(array_keys($translations), 0, 5);
        foreach ($samples as $key) {
            $this->line('   - ' . $key);
        }
        
        if (count($translations) > 5) {
            $this->line('   ... and ' . (count($translations) - 5) . ' more');
        }
        
        $this->newLine();

        // Save translations
        $filePath = $this->extractor->saveTranslations($translations);

        $this->info('💾 Translations saved to: ' . $filePath);
        $this->newLine();

        // Load saved translations to get accurate stats
        $savedTranslations = json_decode($files->get($filePath), true);

        // Display statistics
        $stats = $this->extractor->getStats($savedTranslations);

        $this->info('📊 Statistics:');

        $tableData = [
            ['Total Keys', $stats['total']],
            ['Translated', $stats['translated']],
            ['Untranslated', $stats['untranslated']],
            ['Progress', $stats['percentage'] . '%'],
        ];

        // Add AI translation stats if applicable
        if (($stats['ai_translated'] ?? 0) > 0 || ($stats['ai_failed'] ?? 0) > 0) {
            $tableData[] = ['AI Translated', $stats['ai_translated']];
            if ($stats['ai_failed'] > 0) {
                $tableData[] = ['AI Failed', $stats['ai_failed']];
            }
        }

        $this->table(['Metric', 'Value'], $tableData);

        $this->newLine();

        // Show warnings/tips
        if (($stats['ai_failed'] ?? 0) > 0) {
            $this->warn("⚠️  {$stats['ai_failed']} keys failed to translate (see logs for details)");
        }

        if ($stats['untranslated'] > 0) {
            $this->comment('💡 Tip: Edit ' . $filePath . ' to add translations for untranslated keys.');
        } else {
            $this->info('🎉 All keys have translations!');
        }

        return self::SUCCESS;
    }
}
