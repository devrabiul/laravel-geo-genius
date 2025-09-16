<?php

namespace Devrabiul\LaravelGeoGenius\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Devrabiul\LaravelGeoGenius\Services\LanguageService;

class TranslateLanguage extends Command
{
    /**
     * Usage: php artisan geo:translate-language en
     */
    protected $signature = 'geo:translate-language 
                            {locale : The locale code (e.g. en, bn)} 
                            {--count=5 : Number of strings to translate per run}';

    protected $description = 'Translate missing strings for a given locale.';

    public function handle(): int
    {
        $locale = strtolower($this->argument('locale'));
        $count  = (int) $this->option('count');

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

            $languageService = app(LanguageService::class);

            // You can wrap in progress bar if LanguageService returns iterable
            $languageService->getAllMessagesTranslateProcess(
                languageCode: $locale,
                count: $count
            );

            $this->info("✅ Language '{$locale}' translated successfully!");
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("❌ Translation failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
