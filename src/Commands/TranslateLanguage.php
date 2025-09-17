<?php

namespace Devrabiul\LaravelGeoGenius\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Devrabiul\LaravelGeoGenius\Trait\LanguageTrait;

/**
 * Class TranslateLanguage
 *
 * This command translates missing strings for a given locale
 * from `new-messages.php` and saves them into the main `messages.php`.
 * It uses LanguageTrait to handle translation logic.
 *
 * Usage:
 *   php artisan geo:translate-language en --count=100
 *
 * Options:
 *   --count   Number of strings to translate per run (default: 5, max: 300)
 *
 * @package Devrabiul\LaravelGeoGenius\Commands
 */
class TranslateLanguage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     *
     * Usage: php artisan geo:translate-language en --count=10
     */
    protected $signature = 'geo:translate-language
                            {locale : The locale code (e.g. en, bn)}
                            {--count=5 : Number of strings to translate per run (max 300)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Translate missing strings for a given locale.';

    /**
     * Execute the console command.
     *
     * - Ensures the locale folder exists
     * - Reads new strings from new-messages.php
     * - Translates up to `count` strings
     * - Updates messages.php with the translated values
     * - Displays a progress bar for translation process
     *
     * @return int Command::SUCCESS on success, Command::FAILURE on error
     */
    public function handle(): int
    {
        $startTime = microtime(true);

        $locale = strtolower($this->argument('locale'));
        $count = (int)$this->option('count');

        // Enforce maximum count limit
        if ($count > 300) {
            $count = 300;
            $this->warn("⚠️ Count limited to 300 translations per run.");
        }

        $langPath = resource_path("lang/{$locale}");
        $newMessagesFile = "{$langPath}/new-messages.php";

        // Validate folder and file
        if (!File::exists($langPath)) {
            $this->error("❌ Language folder [resources/lang/{$locale}] not found.");
            return Command::FAILURE;
        }

        if (!File::exists($newMessagesFile)) {
            $this->warn("⚠️ File [new-messages.php] not found in {$langPath}.");
            return Command::FAILURE;
        }

        try {
            $this->info("🔄 Translating '{$locale}' language strings (limit: {$count}) …");

            // Get all strings to translate (up to $count)
            $stringsToTranslate = LanguageTrait::getAllMessagesTranslateProcess(
                languageCode: $locale,
                count: $count,
            );

            $totalStrings = count($stringsToTranslate);
            $this->info("Total strings to translate: {$totalStrings}");

            if ($totalStrings === 0) {
                $this->info("✅ No new strings to translate.");
                return Command::SUCCESS;
            }

            // Initialize progress bar
            $bar = $this->output->createProgressBar($totalStrings);
            $bar->start();

            // Process translation one by one
            foreach ($stringsToTranslate as $key => $text) {
                LanguageTrait::translateSingleString($locale, $key, $text); // Translate & save
                $bar->advance();
            }

            $bar->finish();

            $elapsed = round(microtime(true) - $startTime, 2);
            $this->info("\n✅ Language '{$locale}' translated successfully in {$elapsed} seconds!");

            return Command::SUCCESS;

        } catch (\Throwable $e) {
            $this->error("❌ Translation failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
