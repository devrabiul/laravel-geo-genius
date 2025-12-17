<?php

namespace Devrabiul\LaravelGeoGenius\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Devrabiul\LaravelGeoGenius\Trait\LanguageTrait;

/**
 * Class GenerateTranslationFiles
 *
 * Scans all PHP files in the project for translation calls
 * (`geniusTranslate`, `geniusTrans`, `translate`, `__`) and
 * generates/updates a `new-messages.php` file for the given locale.
 *
 * Dynamic or invalid strings (variables, function calls, CSS vars,
 * short strings, numbers) are skipped.
 *
 * Example usage:
 *   php artisan geo:translations-generate --locale=en
 *
 * @package Devrabiul\LaravelGeoGenius\Commands
 */
class GenerateTranslationFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Options:
     *   --locale : The locale to generate messages for (default: en)
     *
     * @var string
     */
    protected $signature = 'geo:translations-generate
                            {--locale=en : The locale to generate messages for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan project for translation calls and generate new-messages.php, skipping dynamic variables';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $startTime = microtime(true);

        $locale = $this->option('locale');
        $langPath = resource_path("lang/{$locale}");

        if (!File::exists($langPath)) {
            File::makeDirectory($langPath, 0777, true);
        }

        $messagesFile = "{$langPath}/new-messages.php";
        $messagesArray = File::exists($messagesFile) ? include($messagesFile) : [];

        // Map old messages: keep underscores in keys, clean values
        $messagesArray = collect($messagesArray)->mapWithKeys(function ($item, $key) {
            $escapedKey = str_replace("'", "/'", $key);
            $cleanKey = laravelGeoGenius()->language()->geniusRemoveInvalidCharacters($escapedKey); // underscores preserved
            $matchValue = str_replace('_', ' ', laravelGeoGenius()->language()->geniusRemoveInvalidCharacters(str_replace("\'", "'", $item))); // underscores replaced in value
            return [$cleanKey => $matchValue];
        })->toArray();

        $files = File::allFiles(base_path());
        $totalFiles = count($files);
        $foundKeywords = 0;

        $this->info("🔎 Scanning {$totalFiles} PHP files for translation strings…");

        // Progress bar for files
        $bar = $this->output->createProgressBar($totalFiles);
        $bar->start();

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                $bar->advance();
                continue;
            }

            $content = File::get($file->getRealPath());

            // Match translation calls
            preg_match_all(
                '/\b(?:geniusTranslate|geniusTrans|translate|__)\(\s*([\'"])(.*?)\1\s*\)/s',
                $content,
                $matches
            );

            foreach ($matches[2] as $match) {
                // Skip dynamic/invalid strings
                if (
                    preg_match('/\$\w+|\{\$\w+\}/', $match) ||
                    preg_match('/\w+\(.*\)/', $match) ||
                    preg_match('/[\{\}\[\]\(\):\-]/', $match) ||
                    preg_match('/var\(--/', $match) ||
                    strlen($match) <= 1 ||
                    is_numeric($match)
                ) {
                    continue;
                }

                if (!isset($messagesArray[$match])) {
                    $escapedKey = str_replace("'", "/'", $match);
                    $cleanKey = laravelGeoGenius()->language()->geniusRemoveInvalidCharacters($escapedKey); // keep underscores in key
                    $matchValue = str_replace('_', ' ', laravelGeoGenius()->language()->geniusRemoveInvalidCharacters(str_replace("\'", "'", $match))); // spaces in value
                    $messagesArray[$cleanKey] = $matchValue;
                    $foundKeywords++;
                }
            }

            $bar->advance();
        }

        $bar->finish();

        $this->savePhpArrayFile($messagesFile, $messagesArray);

        $elapsed = round(microtime(true) - $startTime, 2);
        $this->info("\n✅ Scan complete! Files scanned: {$totalFiles}, New keywords found: {$foundKeywords} in {$elapsed} seconds.");
    }

    /**
     * Save an associative array to a PHP file in return [...] format.
     *
     * @param string $filePath The file path to save the array into.
     * @param array $array The array of translations to save.
     *
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
