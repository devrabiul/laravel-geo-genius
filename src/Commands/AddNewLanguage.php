<?php

namespace Devrabiul\LaravelGeoGenius\Commands;

use Devrabiul\LaravelGeoGenius\Trait\LanguageTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class AddNewLanguage extends Command
{
    /**
     * Usage: php artisan geo:add-language en
     */
    protected $signature = 'geo:add-language {locale : The locale code, e.g. en, bn}';

    protected $description = 'Add a new language to your LaravelGeoGenius setup if it does not exist';

    public function handle(): void
    {
        $locale = $this->argument('locale');

        // Check if the language directory or files already exist
        $langPath = base_path("resources/lang/{$locale}");
        $messagesFile = "{$langPath}/messages.php";
        $newMessagesFile = "{$langPath}/new-messages.php";

        if (File::exists($messagesFile) || File::exists($newMessagesFile)) {
            $this->warn("⚠️ Language '{$locale}' already exists.");
            return;
        }

        // Check if the locale code is valid from LanguageTrait
        if (!array_key_exists($locale, LanguageTrait::getAllLanguageNames())) {
            $this->warn("❌ Language code '{$locale}' does not exist in supported languages.");
            return;
        }

        // Run your add-process
        LanguageTrait::getLanguageAddProcess(lang: $locale);

        $this->info("✅ Language '{$locale}' added successfully!");
    }
}
