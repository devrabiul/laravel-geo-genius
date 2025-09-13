<?php

namespace Devrabiul\LaravelGeoGenius\Trait;

use Devrabiul\LaravelGeoGenius\Services\GeoLocationService;
use Devrabiul\LaravelGeoGenius\Services\LanguageService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\App;

trait LanguageTrait
{

    public static function getLanguageFilesDirectories(string $path): array
    {
        if (!is_dir(resource_path('lang'))) {
            mkdir(resource_path('lang'), 0777, true);
        } else {
            $output = [];
            exec('chmod -R 0777 ' . resource_path('lang'), $output);
        }

        $directories = [];
        $items = scandir($path);
        foreach ($items as $item) {
            if ($item == '..' || $item == '.')
                continue;
            if (is_dir($path . '/' . $item))
                $directories[] = $item;
        }
        return $directories;
    }

    public static function geniusRemoveInvalidCharacters($str): array|string
    {
        return str_ireplace(['"', ';', '<', '>'], ' ', preg_replace('/\s\s+/', ' ', $str));
    }

    public static function checkLocaleValidity($locale): string
    {
        return array_key_exists($locale, self::getAllLanguageNames()) ? $locale : 'en';
    }

    public static function updateNewMessages($local, $cleanKey, $action = 'add')
    {
        $config = config('laravel-geo-genius', []);
        $translateConfig = $config['translate'];
        $newMessagesArray = include(base_path('resources/lang/' . $local . '/new-messages.php'));

        $translatedText = [];
        if (App::getLocale() != 'en' && ($translateConfig['auto_translate'] ?? false) && (!($action == 'update') || array_key_exists($cleanKey, $newMessagesArray))) {
            try {
                $languageService = new LanguageService();
                $translatedText = $languageService->translateAndUpdateNewMessages(
                    translatedMessages: include(base_path('resources/lang/' . $local . '/messages.php')),
                    newMessagesArray: include(base_path('resources/lang/' . $local . '/new-messages.php')),
                    textKey: $cleanKey,
                    languageCode: $local
                );
            } catch (\Exception $exception) {
            }
        }

        return $translatedText['translatedText'] ?? null;
    }

    public static function geniusTranslateMessageValueByKey(string $local, string|null $key): mixed
    {
        if (
            !File::exists(base_path('resources/lang/en/messages.php')) ||
            !File::exists(base_path('resources/lang/en/new-messages.php'))
        ) {
            self::getLanguageAddProcess(lang: 'en');
        }

        if (
            !File::exists(base_path('resources/lang/' . $local . '/messages.php')) ||
            !File::exists(base_path('resources/lang/' . $local . '/new-messages.php'))
        ) {
            self::getLanguageAddProcess(lang: $local);
        }

        try {
            $escapedKey = str_replace("'", "/'", $key);
            $cleanKey = LanguageTrait::geniusRemoveInvalidCharacters($escapedKey);
            $processedKey = str_replace('_', ' ', LanguageTrait::geniusRemoveInvalidCharacters(str_replace("\'", "'", $key)));

            $translatedMessagesArray = include(base_path('resources/lang/' . $local . '/messages.php'));
            $newMessagesArray = include(base_path('resources/lang/' . $local . '/new-messages.php'));

            self::updateNewMessages($local, $cleanKey, 'update');

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
                $message = self::updateNewMessages($local, $cleanKey, 'add') ?? $processedKey;
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

    public static function geniusSortTranslateArrayByKey($targetPath): void
    {
        $getMessagesArray = include($targetPath);
        ksort($getMessagesArray);
        $remainingMessagesFileContents = "<?php\n\nreturn [\n";
        foreach ($getMessagesArray as $newMsgKey => $newMsgValue) {
            $remainingMessagesFileContents .= "\t\"" . $newMsgKey . "\" => \"" . $newMsgValue . "\",\n";
        }
        $remainingMessagesFileContents .= "];\n";
        file_put_contents($targetPath, $remainingMessagesFileContents);
    }

    public static function getLanguageAddProcess(string $lang): void
    {
        if (!is_dir(resource_path('lang'))) {
            mkdir(resource_path('lang'), 0777, true);
        } else {
            $output = [];
            exec('chmod -R 0777 ' . resource_path('lang'), $output);
        }

        if (!file_exists(base_path('resources/lang/' . $lang))) {
            mkdir(base_path('resources/lang/' . $lang), 0777, true);
            $files = File::allFiles(base_path('resources/lang/' . $lang));
            foreach ($files as $file) {
                chmod($file, 0777);
            }

            if (!file_exists(base_path('resources/lang/en/messages.php'))) {
                file_put_contents(base_path('resources/lang/en/messages.php'), "<?php\n\nreturn [];\n");
                file_put_contents(base_path('resources/lang/en/new-messages.php'), "<?php\n\nreturn [];\n");
            }

            $messagesFromDefaultLanguage = file_get_contents(base_path('resources/lang/en/new-messages.php'));
            if ($lang != 'en') {
                $messagesNewFile = fopen(base_path('resources/lang/' . $lang . '/' . 'new-messages.php'), "w") or die("Unable to open file!");
                $messagesFile = fopen(base_path('resources/lang/' . $lang . '/' . 'messages.php'), "w") or die("Unable to open file!");
                fwrite($messagesNewFile, $messagesFromDefaultLanguage);
                $messagesFileContents = "<?php\n\nreturn [];\n";
                file_put_contents(base_path('resources/lang/' . $lang . '/messages.php'), $messagesFileContents);
                $translatedMessagesArray = include(base_path('resources/lang/en/messages.php'));
                $newMessagesArray = include(base_path('resources/lang/en/new-messages.php'));
                $allMessages = array_merge($translatedMessagesArray, $newMessagesArray);
                $dataFiltered = [];
                foreach ($allMessages as $key => $data) {
                    $dataFiltered[$key] = $data;
                }
                $string = "<?php return " . var_export($dataFiltered, true) . ";";
                file_put_contents(base_path('resources/lang/' . $lang . '/new-messages.php'), $string);
            }
            self::geniusSortTranslateArrayByKey(targetPath: base_path('resources/lang/' . $lang . '/messages.php'));
        }

        $languagePath = [];
        exec('chmod -R 0777 ' . resource_path('lang'), $languagePath);
    }


    public static function getAllLanguageNames(): array
    {
        return [
            'aa' => 'Afar',
            'ab' => 'Abkhazian',
            'af' => 'Afrikaans',
            'ak' => 'Akan',
            'sq' => 'Albanian',
            'am' => 'Amharic',
            'ar' => 'Arabic',
            'an' => 'Aragonese',
            'hy' => 'Armenian',
            'as' => 'Assamese',
            'av' => 'Avaric',
            'ae' => 'Avestan',
            'ay' => 'Aymara',
            'az' => 'Azerbaijani',
            'ba' => 'Bashkir',
            'bm' => 'Bambara',
            'eu' => 'Basque',
            'be' => 'Belarusian',
            'bn' => 'Bengali',
            'bh' => 'Bihari languages',
            'bi' => 'Bislama',
            'bs' => 'Bosnian',
            'br' => 'Breton',
            'bg' => 'Bulgarian',
            'my' => 'Burmese',
            'ca' => 'Catalan',
            'ch' => 'Chamorro',
            'ce' => 'Chechen',
            'zh' => 'Chinese',
            'cu' => 'Church Slavic',
            'cv' => 'Chuvash',
            'kw' => 'Cornish',
            'co' => 'Corsican',
            'cr' => 'Cree',
            'cs' => 'Czech',
            'da' => 'Danish',
            'dv' => 'Divehi',
            'nl' => 'Dutch',
            'dz' => 'Dzongkha',
            'en' => 'English',
            'eo' => 'Esperanto',
            'et' => 'Estonian',
            'ee' => 'Ewe',
            'fo' => 'Faroese',
            'fa' => 'Persian',
            'fj' => 'Fijian',
            'fi' => 'Finnish',
            'fr' => 'French',
            'fy' => 'Western Frisian',
            'ff' => 'Fulah',
            'gd' => 'Scottish Gaelic',
            'gl' => 'Galician',
            'lg' => 'Ganda',
            'ka' => 'Georgian',
            'de' => 'German',
            'ki' => 'Kikuyu',
            'el' => 'Greek',
            'kl' => 'Kalaallisut',
            'gn' => 'Guarani',
            'gu' => 'Gujarati',
            'ht' => 'Haitian',
            'ha' => 'Hausa',
            'he' => 'Hebrew',
            'hz' => 'Herero',
            'hi' => 'Hindi',
            'ho' => 'Hiri Motu',
            'hr' => 'Croatian',
            'hu' => 'Hungarian',
            'ig' => 'Igbo',
            'is' => 'Icelandic',
            'io' => 'Ido',
            'ii' => 'Sichuan Yi',
            'iu' => 'Inuktitut',
            'ie' => 'Interlingue',
            'ia' => 'Interlingua',
            'id' => 'Indonesian',
            'ik' => 'Inupiaq',
            'it' => 'Italian',
            'jv' => 'Javanese',
            'ja' => 'Japanese',
            'kn' => 'Kannada',
            'ks' => 'Kashmiri',
            'kr' => 'Kanuri',
            'kk' => 'Kazakh',
            'km' => 'Central Khmer',
            'ki' => 'Kikuyu',
            'rw' => 'Kinyarwanda',
            'ky' => 'Kirghiz',
            'kv' => 'Komi',
            'kg' => 'Kongo',
            'ko' => 'Korean',
            'kj' => 'Kuanyama',
            'ku' => 'Kurdish',
            'lo' => 'Lao',
            'la' => 'Latin',
            'lv' => 'Latvian',
            'li' => 'Limburgan',
            'ln' => 'Lingala',
            'lt' => 'Lithuanian',
            'lu' => 'Luba-Katanga',
            'lb' => 'Luxembourgish',
            'mk' => 'Macedonian',
            'mh' => 'Marshallese',
            'ml' => 'Malayalam',
            'mr' => 'Marathi',
            'mg' => 'Malagasy',
            'mt' => 'Maltese',
            'mn' => 'Mongolian',
            'na' => 'Nauru',
            'nv' => 'Navajo',
            'nr' => 'Ndebele, South',
            'nd' => 'Ndebele, North',
            'ng' => 'Ndonga',
            'ne' => 'Nepali',
            'nn' => 'Norwegian Nynorsk',
            'nb' => 'Norwegian Bokmål',
            'no' => 'Norwegian',
            'ny' => 'Chichewa',
            'oc' => 'Occitan',
            'oj' => 'Ojibwa',
            'or' => 'Oromo',
            'om' => 'Oromo',
            'os' => 'Ossetian',
            'pa' => 'Panjabi',
            'pi' => 'Pali',
            'pl' => 'Polish',
            'pt' => 'Portuguese',
            'ps' => 'Pashto',
            'qu' => 'Quechua',
            'rm' => 'Romansh',
            'ro' => 'Romanian',
            'rn' => 'Rundi',
            'ru' => 'Russian',
            'sg' => 'Sango',
            'sa' => 'Sanskrit',
            'si' => 'Sinhala',
            'sk' => 'Slovak',
            'sl' => 'Slovenian',
            'se' => 'Northern Sami',
            'sm' => 'Samoan',
            'sn' => 'Shona',
            'sd' => 'Sindhi',
            'so' => 'Somali',
            'st' => 'Sotho, Southern',
            'es' => 'Spanish',
            'sc' => 'Sardinian',
            'sr' => 'Serbian',
            'ss' => 'Swati',
            'su' => 'Sundanese',
            'sw' => 'Swahili',
            'sv' => 'Swedish',
            'ty' => 'Tahitian',
            'ta' => 'Tamil',
            'tt' => 'Tatar',
            'te' => 'Telugu',
            'tg' => 'Tajik',
            'tl' => 'Tagalog',
            'th' => 'Thai',
            'bo' => 'Tibetan',
            'ti' => 'Tigrinya',
            'to' => 'Tonga',
            'tn' => 'Tswana',
            'ts' => 'Tsonga',
            'tk' => 'Turkmen',
            'tr' => 'Turkish',
            'tw' => 'Twi',
            'ug' => 'Uighur',
            'uk' => 'Ukrainian',
            'ur' => 'Urdu',
            'uz' => 'Uzbek',
            've' => 'Venda',
            'vi' => 'Vietnamese',
            'vo' => 'Volapük',
            'wa' => 'Walloon',
            'wo' => 'Wolof',
            'xh' => 'Xhosa',
            'yi' => 'Yiddish',
            'yo' => 'Yoruba',
            'za' => 'Zhuang',
            'zu' => 'Zulu',
        ];
    }
}
