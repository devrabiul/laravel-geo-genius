<?php

namespace Devrabiul\LaravelGeoGenius\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Class TranslateLanguageAll
 *
 * Runs the `geo:translate-language-batch` command repeatedly until all
 * strings in `new-messages.php` are translated and moved into `messages.php`.
 *
 * Usage:
 *   php artisan geo:translate-language-all en --count=300
 *
 * Options:
 *   --count   Number of strings per batch (default: 300, max: 500)
 *
 * Process:
 *   - Loads strings from `new-messages.php`
 *   - Calls `geo:translate-language-batch` multiple times in batches
 *   - Stops automatically when no untranslated strings remain
 *
 * @package Devrabiul\LaravelGeoGenius\Commands
 */
class TranslateLanguageAll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     *
     * Example:
     *   php artisan geo:translate-language-all en --count=300
     */
    protected $signature = 'geo:translate-language-all
                            {locale : The locale code (e.g. en, bn)}
                            {--count=300 : Number of strings per batch (max 500)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Translate all missing strings by running geo:translate-language-batch until complete.';

    /**
     * Execute the console command.
     *
     * @return int Command::SUCCESS on success, Command::FAILURE on error
     */
    public function handle(): int
    {
        $startTime = microtime(true);

        $locale = strtolower($this->argument('locale'));
        $count = (int)$this->option('count');

        // Enforce maximum count limit
        if ($count > 500) {
            $count = 500;
            $this->warn("⚠️ Count limited to 500 per batch.");
        }

        $langPath = resource_path("lang/{$locale}");
        $newMessagesFile = "{$langPath}/new-messages.php";

        // Ensure new-messages.php exists
        if (!File::exists($newMessagesFile)) {
            $this->warn("⚠️ File [new-messages.php] not found in {$langPath}.");
            return Command::FAILURE;
        }

        $round = 1;
        while (true) {
            $newMessages = include($newMessagesFile);
            $remaining = count($newMessages);

            if (empty($newMessages)) {
                break; // ✅ Nothing left to translate
            }

            $this->line("\n🚀 Batch #{$round}: Translating next {$count} strings…");
            $this->info("📝 Strings remaining to translate: {$remaining}");

            // Call geo:translate-language-batch
            $this->call('geo:translate-language-batch', [
                'locale' => $locale,
                '--count' => $count,
            ]);

            $round++;
        }

        $elapsed = round(microtime(true) - $startTime, 2);
        $this->info("\n🎉 All translations for '{$locale}' completed in {$elapsed} seconds!");

        return Command::SUCCESS;
    }
}
