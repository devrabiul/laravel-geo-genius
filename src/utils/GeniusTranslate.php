<?php

use Devrabiul\LaravelGeoGenius\Trait\LanguageTrait;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;

if (!function_exists('geniusTrans')) {
    function geniusTrans($key = null): string|null
    {
        return geniusTranslate($key);
    }
}

if (!function_exists('geniusTranslate')) {
    function geniusTranslate($key = null): string|null
    {
        if (
            !File::exists(base_path('resources/lang/en/messages.php')) ||
            !File::exists(base_path('resources/lang/en/new-messages.php'))
        ) {
            LanguageTrait::getLanguageAddProcess(lang: 'en');
        }

        if (!empty($key) || $key == 0) {
            if (App::getLocale() != 'en') {
                $languageDirectories = LanguageTrait::getLanguageFilesDirectories(path: base_path('resources/lang/'));
                foreach ($languageDirectories as $directory) {
                    geniusTranslateMessageValueByKey(local: $directory, key: $key);
                }
            }
            $local = LanguageTrait::checkLocaleValidity(locale: laravelGeoGenius()->language()->getUserLanguage());
            App::setLocale($local);
            return geniusTranslateMessageValueByKey(local: $local, key: $key);
        }
        return $key;
    }
}

if (!function_exists('geniusTranslateNumber')) {
    function geniusTranslateNumber($key): string
    {
        $lang = App::getLocale();
        if (in_array($lang, ['bn'])) {
            $string = '';
            $key = strval($key);
            foreach (str_split($key) as $character) {
                if (is_numeric($character)) {
                    $string .= geniusTranslate($character);
                } else {
                    $string .= $character;
                }
            }
            return $string;
        } else {
            return $key;
        }
    }
}


if (!function_exists('geniusTranslateMessageValueByKey')) {
    function geniusTranslateMessageValueByKey(string $local, string|null $key): mixed
    {
        if (
            !File::exists(base_path('resources/lang/' . $local . '/messages.php')) ||
            !File::exists(base_path('resources/lang/' . $local . '/new-messages.php'))
        ) {
            LanguageTrait::getLanguageAddProcess(lang: $local);
        }

        try {
            $escapedKey = str_replace("'", "/'", $key);
            $cleanKey = LanguageTrait::geniusRemoveInvalidCharacters($escapedKey);
            $processedKey = str_replace('_', ' ', LanguageTrait::geniusRemoveInvalidCharacters(str_replace("\'", "'", $key)));

            $translatedMessagesArray = include(base_path('resources/lang/' . $local . '/messages.php'));
            $newMessagesArray = include(base_path('resources/lang/' . $local . '/new-messages.php'));

            if (!array_key_exists($cleanKey, $translatedMessagesArray) && !array_key_exists($cleanKey, $newMessagesArray)) {
                $newMessagesArray[$cleanKey] = $processedKey;

                // Build the PHP file contents
                $languageFileContents = "<?php\n\nreturn [\n";
                foreach ($newMessagesArray as $languageKey => $value) {
                    $languageFileContents .= "\t\"" . $languageKey . "\" => \"" . $value . "\",\n";
                }
                $languageFileContents .= "];\n";

                $targetPath = base_path('resources/lang/' . $local . '/new-messages.php');
                file_put_contents($targetPath, $languageFileContents);

                LanguageTrait::geniusSortTranslateArrayByKey(targetPath: $targetPath);
                $message = $processedKey;
            } elseif (array_key_exists($cleanKey, $translatedMessagesArray)) {
                $message = __('messages.' . $cleanKey);
            } elseif (array_key_exists($cleanKey, $newMessagesArray)) {
                $message = __('new-messages.' . $cleanKey);
            } else {
                $message = __('messages.' . $cleanKey);;
            }
        } catch (\Exception $exception) {
            $message = str_replace('_', ' ', LanguageTrait::geniusRemoveInvalidCharacters(str_replace("\'", "'", $key)));
        }
        return $message;
    }
}