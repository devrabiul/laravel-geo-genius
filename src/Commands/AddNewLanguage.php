<?php

namespace Devrabiul\LaravelGeoGenius\Commands;

use Devrabiul\LaravelGeoGenius\Trait\LanguageTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Class AddNewLanguage
 *
 * This command adds a new language to the LaravelGeoGenius setup.
 * It ensures that the language directory and files are created
 * only if they do not already exist and if the locale code is valid.
 *
 * Usage example:
 *  php artisan geo:add-language en
 *
 * @package Devrabiul\LaravelGeoGenius\Commands
 */
class AddNewLanguage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geo:add-language {locale : The locale code, e.g. en, bn}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a new language to your LaravelGeoGenius setup if it does not exist';

    /**
     * Execute the console command.
     *
     * @return void
     */
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
