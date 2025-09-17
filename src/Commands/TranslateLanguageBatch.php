<?php

namespace Devrabiul\LaravelGeoGenius\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Devrabiul\LaravelGeoGenius\Trait\LanguageTrait;
use Devrabiul\LaravelGeoGenius\Services\TranslateService\TranslateService;

/**
 * Class TranslateLanguageBatch
 *
 * Translates a limited number of missing strings from `new-messages.php`
 * and moves them into `messages.php` for a given locale.
 *
 * Usage:
 *   php artisan geo:translate-language-batch en --count=50
 *
 * Options:
 *   --count   Number of strings to translate in this run (default: 10, max: 500)
 *
 * Process:
 *   - Reads strings from `resources/lang/{locale}/new-messages.php`
 *   - Translates them in a batch using TranslateService
 *   - Merges them into `messages.php`
 *   - Removes them from `new-messages.php`
 *
 * Typically called multiple times by `geo:translate-language-all`.
 *
 * @package Devrabiul\LaravelGeoGenius\Commands
 */
class TranslateLanguageBatch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     *
     * Example:
     *   php artisan geo:translate-language-batch en --count=20
     */
    protected $signature = 'geo:translate-language-batch
                            {locale : The locale code (e.g. en, bn)}
                            {--count=10 : Number of strings to translate per run (max 500)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Translate missing strings from new-messages.php and move them to messages.php';

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

        // Enforce maximum limit
        if ($count > 500) {
            $count = 500;
            $this->warn("⚠️ Count limited to 500 translations per run.");
        }

        $langPath = resource_path("lang/{$locale}");
        $newMessagesFile = "{$langPath}/new-messages.php";
        $messagesFile = "{$langPath}/messages.php";

        // Ensure language folder exists
        if (!File::exists($langPath)) {
            $this->error("❌ Language folder [resources/lang/{$locale}] not found.");
            return Command::FAILURE;
        }

        // Ensure new-messages.php exists
        if (!File::exists($newMessagesFile)) {
            $this->warn("⚠️ File [new-messages.php] not found in {$langPath}.");
            return Command::FAILURE;
        }

        $newMessages = include($newMessagesFile);
        $messages = File::exists($messagesFile) ? include($messagesFile) : [];

        if (empty($newMessages)) {
            $this->info("✅ No new strings to translate for '{$locale}'.");
            return Command::SUCCESS;
        }

        // Pick limited number of strings to translate
        $batchKeys = array_slice(array_keys($newMessages), 0, $count, true);
        $batchInput = [];
        foreach ($batchKeys as $key) {
            $batchInput[$key] = str_replace('_', ' ',
                LanguageTrait::geniusRemoveInvalidCharacters(
                    str_replace("\'", "'", $newMessages[$key])
                )
            );
        }

        try {
            $this->info("🔄 Translating {$locale} strings…");

            $translator = new TranslateService($locale);

            // Progress bar
            $bar = $this->output->createProgressBar(count($batchInput));
            $bar->start();

            // Translate in batch
            $translated = $translator->translateBatch($batchInput);

            // Simulate progress
            foreach ($batchInput as $_) {
                usleep(1000);
                $bar->advance();
            }

            $bar->finish();

            // Merge and save translations
            $messages = array_merge($messages, $translated);
            foreach ($batchKeys as $key) {
                unset($newMessages[$key]);
            }

            $this->savePhpArrayFile($messagesFile, $messages);
            $this->savePhpArrayFile($newMessagesFile, $newMessages);

            $elapsed = round(microtime(true) - $startTime, 2);
            $this->info("\n✅ Translated and moved " . count($translated) . " strings to messages.php in {$elapsed} seconds.");

            return Command::SUCCESS;

        } catch (\Throwable $e) {
            $this->error("❌ Translation failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Save an array into a PHP file in `return [...]` format.
     *
     * @param string $filePath File path to save
     * @param array $array Data to save
     * @return void
     */
    protected function savePhpArrayFile(string $filePath, array $array): void
    {
        ksort($array);
        $phpContent = "<?php\n\nreturn [\n";
        foreach ($array as $key => $value) {
            $phpContent .= "\t\"" . addslashes($key) . "\" => \"" . addslashes($value) . "\",\n";
        }
        $phpContent .= "];\n";

        File::put($filePath, $phpContent);
    }
}
