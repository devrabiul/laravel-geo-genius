<?php

namespace Devrabiul\LaravelGeoGenius\Trait;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Devrabiul\LaravelGeoGenius\Services\LanguageService;

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
        $langDirectories = LanguageTrait::getLanguageFilesDirectories(path: base_path('resources/lang/'));
        if ($locale != 'en' && !in_array($locale, $langDirectories)) {
            return 'en';
        }
        return array_key_exists($locale, self::getAllLanguageNames()) ? $locale : 'en';
    }

    public static function geniusTranslateMessageValueByKey(string $local, string|null $key): mixed
    {
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

    public static function getAddTranslateNewKey($sourcePath, $targetPath, $translatedKey): void
    {
        $getNewMessagesArray = include($sourcePath);
        $remainingMessagesFileContents = "<?php\n\nreturn [\n";
        foreach ($getNewMessagesArray as $newMsgKey => $newMsgValue) {
            if ($newMsgKey != $translatedKey) {
                $remainingMessagesFileContents .= "\t\"" . $newMsgKey . "\" => \"" . $newMsgValue . "\",\n";
            }
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

    public static function getLanguageCodeFromCountryCode(string $countryCode): ?string
    {
        $languageData = self::getAllLanguageNames();
        foreach ($languageData as $languageCode => $data) {
            if (in_array(strtoupper($countryCode), $data['country_codes'])) {
                return $languageCode;
            }
        }
        return 'en';
    }

    public static function getAllMessagesTranslateProcess(string $languageCode, int $count = 999999999): array
    {
        $newMessagesArray = include(base_path('resources/lang/' . $languageCode . '/new-messages.php'));
        $translatedMessagesArray = include(base_path('resources/lang/' . $languageCode . '/messages.php'));
        $response = [
            'status' => 0,
            'message' => geniusTranslate("Cannot_translate_now"),
            'due_message' => count($newMessagesArray),
        ];

        $translateCount = 0;
        if ($newMessagesArray) {
            foreach ($newMessagesArray as $key => $value) {
                if ($translateCount < $count) {
                    $languageSerive = new LanguageService();
                    $translated = $languageSerive->autoTranslator($key, 'en', $languageCode);
                    if ($translated !== null) {
                        $translatedMessagesArray[$key] = preg_replace('/\s+/', ' ', LanguageTrait::geniusRemoveInvalidCharacters($translated));
                        $translatedKey = $key;

                        $messagesFileContents = "<?php\n\nreturn [\n";
                        foreach ($translatedMessagesArray as $k => $tmaValue) {
                            $messagesFileContents .= "\t\"" . $k . "\" => \"" . $tmaValue . "\",\n";
                        }
                        $messagesFileContents .= "];\n";
                        file_put_contents(base_path('resources/lang/' . $languageCode . '/messages.php'), $messagesFileContents);
                        LanguageTrait::geniusSortTranslateArrayByKey(targetPath: base_path('resources/lang/' . $languageCode . '/messages.php'));

                        $sourcePath = base_path('resources/lang/' . $languageCode . '/new-messages.php');
                        $targetPath = base_path('resources/lang/' . $languageCode . '/new-messages.php');
                        LanguageTrait::getAddTranslateNewKey($sourcePath, $targetPath, $translatedKey);
                        LanguageTrait::geniusSortTranslateArrayByKey(targetPath: $targetPath);
                        $translateCount++;
                        $response = [
                            'status' => 1,
                            'message' => geniusTranslate("Translate_Successful"),
                            'due_message' => count(include(base_path('resources/lang/' . $languageCode . '/new-messages.php'))),
                        ];
                    }
                }
            }
        } else {
            $response = [
                'status' => 1,
                'message' => geniusTranslate("All_Messages_are_translated"),
                'due_message' => count(include(base_path('resources/lang/' . $languageCode . '/new-messages.php'))),
            ];
        }

        return $response;
    }

    public static function getAllLanguageNames(): array
    {
        return [
            'aa' => [
                'name' => 'Afar',
                'country_codes' => ['DJ', 'ER', 'ET'],
            ],
            'ab' => [
                'name' => 'Abkhazian',
                'country_codes' => ['GE'],
            ],
            'af' => [
                'name' => 'Afrikaans',
                'country_codes' => ['NA', 'ZA'],
            ],
            'ak' => [
                'name' => 'Akan',
                'country_codes' => ['GH'],
            ],
            'sq' => [
                'name' => 'Albanian',
                'country_codes' => ['AL', 'XK'],
            ],
            'am' => [
                'name' => 'Amharic',
                'country_codes' => ['ET'],
            ],
            'ar' => [
                'name' => 'Arabic',
                'country_codes' => ['DZ', 'BH', 'TD', 'KM', 'DJ', 'EG', 'ER', 'IQ', 'IL', 'JO', 'KW', 'LB', 'LY', 'MR', 'MA', 'OM', 'PS', 'QA', 'SA', 'SO', 'SD', 'SY', 'TN', 'AE', 'EH', 'YE'],
            ],
            'an' => [
                'name' => 'Aragonese',
                'country_codes' => ['ES'],
            ],
            'hy' => [
                'name' => 'Armenian',
                'country_codes' => ['AM'],
            ],
            'as' => [
                'name' => 'Assamese',
                'country_codes' => ['IN'],
            ],
            'av' => [
                'name' => 'Avaric',
                'country_codes' => ['RU'],
            ],
            'ae' => [
                'name' => 'Avestan',
                'country_codes' => ['IR'],
            ],
            'ay' => [
                'name' => 'Aymara',
                'country_codes' => ['BO', 'PE'],
            ],
            'az' => [
                'name' => 'Azerbaijani',
                'country_codes' => ['AZ', 'IR'],
            ],
            'ba' => [
                'name' => 'Bashkir',
                'country_codes' => ['RU'],
            ],
            'bm' => [
                'name' => 'Bambara',
                'country_codes' => ['ML'],
            ],
            'eu' => [
                'name' => 'Basque',
                'country_codes' => ['ES', 'FR'],
            ],
            'be' => [
                'name' => 'Belarusian',
                'country_codes' => ['BY'],
            ],
            'bn' => [
                'name' => 'Bengali',
                'country_codes' => ['BD', 'IN'],
            ],
            'bh' => [
                'name' => 'Bihari languages',
                'country_codes' => ['IN'],
            ],
            'bi' => [
                'name' => 'Bislama',
                'country_codes' => ['VU'],
            ],
            'bs' => [
                'name' => 'Bosnian',
                'country_codes' => ['BA'],
            ],
            'br' => [
                'name' => 'Breton',
                'country_codes' => ['FR'],
            ],
            'bg' => [
                'name' => 'Bulgarian',
                'country_codes' => ['BG'],
            ],
            'my' => [
                'name' => 'Burmese',
                'country_codes' => ['MM'],
            ],
            'ca' => [
                'name' => 'Catalan',
                'country_codes' => ['AD', 'ES', 'FR', 'IT'],
            ],
            'ch' => [
                'name' => 'Chamorro',
                'country_codes' => ['GU', 'MP'],
            ],
            'ce' => [
                'name' => 'Chechen',
                'country_codes' => ['RU'],
            ],
            'zh' => [
                'name' => 'Chinese',
                'country_codes' => ['CN', 'HK', 'MO', 'SG', 'TW'],
            ],
            'cu' => [
                'name' => 'Church Slavic',
                'country_codes' => ['UA'],
            ],
            'cv' => [
                'name' => 'Chuvash',
                'country_codes' => ['RU'],
            ],
            'kw' => [
                'name' => 'Cornish',
                'country_codes' => ['GB'],
            ],
            'co' => [
                'name' => 'Corsican',
                'country_codes' => ['FR'],
            ],
            'cr' => [
                'name' => 'Cree',
                'country_codes' => ['CA'],
            ],
            'cs' => [
                'name' => 'Czech',
                'country_codes' => ['CZ', 'SK'],
            ],
            'da' => [
                'name' => 'Danish',
                'country_codes' => ['DK', 'FO', 'GL'],
            ],
            'dv' => [
                'name' => 'Divehi',
                'country_codes' => ['MV'],
            ],
            'nl' => [
                'name' => 'Dutch',
                'country_codes' => ['AW', 'BE', 'CW', 'NL', 'SX', 'SR'],
            ],
            'dz' => [
                'name' => 'Dzongkha',
                'country_codes' => ['BT'],
            ],
            'en' => [
                'name' => 'English',
                'country_codes' => ['AG', 'AU', 'BS', 'BB', 'BZ', 'BW', 'CA', 'CM', 'CK', 'DM', 'FJ', 'GM', 'GH', 'GI', 'GD', 'GU', 'GY', 'IE', 'IN', 'JM', 'KE', 'KI', 'LS', 'LR', 'MW', 'MT', 'MH', 'MU', 'FM', 'NA', 'NR', 'NZ', 'NG', 'NU', 'PK', 'PG', 'PH', 'RW', 'VC', 'WS', 'SC', 'SL', 'SG', 'SB', 'ZA', 'SS', 'SD', 'SZ', 'TZ', 'TO', 'TT', 'UG', 'GB', 'US', 'VU', 'ZM', 'ZW'],
            ],
            'eo' => [
                'name' => 'Esperanto',
                'country_codes' => ['worldwide'],
            ],
            'et' => [
                'name' => 'Estonian',
                'country_codes' => ['EE'],
            ],
            'ee' => [
                'name' => 'Ewe',
                'country_codes' => ['GH', 'TG'],
            ],
            'fo' => [
                'name' => 'Faroese',
                'country_codes' => ['FO', 'DK'],
            ],
            'fa' => [
                'name' => 'Persian',
                'country_codes' => ['IR', 'AF'],
            ],
            'fj' => [
                'name' => 'Fijian',
                'country_codes' => ['FJ'],
            ],
            'fi' => [
                'name' => 'Finnish',
                'country_codes' => ['FI'],
            ],
            'fr' => [
                'name' => 'French',
                'country_codes' => ['BE', 'BJ', 'BF', 'BI', 'CM', 'CA', 'CF', 'TD', 'CD', 'CG', 'CI', 'DJ', 'GQ', 'FR', 'GA', 'GN', 'HT', 'LU', 'MG', 'ML', 'MC', 'NE', 'RW', 'SN', 'SC', 'CH', 'TG', 'VU'],
            ],
            'fy' => [
                'name' => 'Western Frisian',
                'country_codes' => ['NL'],
            ],
            'ff' => [
                'name' => 'Fulah',
                'country_codes' => ['BJ', 'BF', 'CM', 'TD', 'GM', 'GN', 'GW', 'ML', 'MR', 'NE', 'NG', 'SN', 'SL'],
            ],
            'gd' => [
                'name' => 'Scottish Gaelic',
                'country_codes' => ['GB'],
            ],
            'gl' => [
                'name' => 'Galician',
                'country_codes' => ['ES'],
            ],
            'lg' => [
                'name' => 'Ganda',
                'country_codes' => ['UG'],
            ],
            'ka' => [
                'name' => 'Georgian',
                'country_codes' => ['GE'],
            ],
            'de' => [
                'name' => 'German',
                'country_codes' => ['AT', 'BE', 'DE', 'LI', 'LU', 'CH'],
            ],
            'ki' => [
                'name' => 'Kikuyu',
                'country_codes' => ['KE'],
            ],
            'el' => [
                'name' => 'Greek',
                'country_codes' => ['CY', 'GR'],
            ],
            'kl' => [
                'name' => 'Kalaallisut',
                'country_codes' => ['GL'],
            ],
            'gn' => [
                'name' => 'Guarani',
                'country_codes' => ['AR', 'BO', 'BR', 'PY'],
            ],
            'gu' => [
                'name' => 'Gujarati',
                'country_codes' => ['IN'],
            ],
            'ht' => [
                'name' => 'Haitian',
                'country_codes' => ['HT'],
            ],
            'ha' => [
                'name' => 'Hausa',
                'country_codes' => ['NE', 'NG'],
            ],
            'he' => [
                'name' => 'Hebrew',
                'country_codes' => ['IL'],
            ],
            'hz' => [
                'name' => 'Herero',
                'country_codes' => ['NA', 'BW'],
            ],
            'hi' => [
                'name' => 'Hindi',
                'country_codes' => ['IN'],
            ],
            'ho' => [
                'name' => 'Hiri Motu',
                'country_codes' => ['PG'],
            ],
            'hr' => [
                'name' => 'Croatian',
                'country_codes' => ['HR', 'BA'],
            ],
            'hu' => [
                'name' => 'Hungarian',
                'country_codes' => ['HU'],
            ],
            'ig' => [
                'name' => 'Igbo',
                'country_codes' => ['NG'],
            ],
            'is' => [
                'name' => 'Icelandic',
                'country_codes' => ['IS'],
            ],
            'io' => [
                'name' => 'Ido',
                'country_codes' => [],
            ],
            'ii' => [
                'name' => 'Sichuan Yi',
                'country_codes' => ['CN'],
            ],
            'iu' => [
                'name' => 'Inuktitut',
                'country_codes' => ['CA'],
            ],
            'ie' => [
                'name' => 'Interlingue',
                'country_codes' => [],
            ],
            'ia' => [
                'name' => 'Interlingua',
                'country_codes' => [],
            ],
            'id' => [
                'name' => 'Indonesian',
                'country_codes' => ['ID'],
            ],
            'ik' => [
                'name' => 'Inupiaq',
                'country_codes' => ['US'],
            ],
            'it' => [
                'name' => 'Italian',
                'country_codes' => ['IT', 'SM', 'CH', 'VA'],
            ],
            'jv' => [
                'name' => 'Javanese',
                'country_codes' => ['ID', 'MY'],
            ],
            'ja' => [
                'name' => 'Japanese',
                'country_codes' => ['JP'],
            ],
            'kn' => [
                'name' => 'Kannada',
                'country_codes' => ['IN'],
            ],
            'ks' => [
                'name' => 'Kashmiri',
                'country_codes' => ['IN'],
            ],
            'kr' => [
                'name' => 'Kanuri',
                'country_codes' => ['NE', 'NG'],
            ],
            'kk' => [
                'name' => 'Kazakh',
                'country_codes' => ['KZ'],
            ],
            'km' => [
                'name' => 'Central Khmer',
                'country_codes' => ['KH'],
            ],
            'rw' => [
                'name' => 'Kinyarwanda',
                'country_codes' => ['RW', 'UG', 'CD'],
            ],
            'ky' => [
                'name' => 'Kirghiz',
                'country_codes' => ['KG'],
            ],
            'kv' => [
                'name' => 'Komi',
                'country_codes' => ['RU'],
            ],
            'kg' => [
                'name' => 'Kongo',
                'country_codes' => ['AO', 'CD', 'CG'],
            ],
            'ko' => [
                'name' => 'Korean',
                'country_codes' => ['KP', 'KR'],
            ],
            'kj' => [
                'name' => 'Kuanyama',
                'country_codes' => ['AO', 'NA'],
            ],
            'ku' => [
                'name' => 'Kurdish',
                'country_codes' => ['IR', 'IQ', 'SY', 'TR'],
            ],
            'lo' => [
                'name' => 'Lao',
                'country_codes' => ['LA'],
            ],
            'la' => [
                'name' => 'Latin',
                'country_codes' => ['VA'],
            ],
            'lv' => [
                'name' => 'Latvian',
                'country_codes' => ['LV'],
            ],
            'li' => [
                'name' => 'Limburgan',
                'country_codes' => ['BE', 'DE', 'NL'],
            ],
            'ln' => [
                'name' => 'Lingala',
                'country_codes' => ['AO', 'CF', 'CD', 'CG'],
            ],
            'lt' => [
                'name' => 'Lithuanian',
                'country_codes' => ['LT'],
            ],
            'lu' => [
                'name' => 'Luba-Katanga',
                'country_codes' => ['CD'],
            ],
            'lb' => [
                'name' => 'Luxembourgish',
                'country_codes' => ['LU'],
            ],
            'mk' => [
                'name' => 'Macedonian',
                'country_codes' => ['MK'],
            ],
            'mh' => [
                'name' => 'Marshallese',
                'country_codes' => ['MH'],
            ],
            'ml' => [
                'name' => 'Malayalam',
                'country_codes' => ['IN'],
            ],
            'mr' => [
                'name' => 'Marathi',
                'country_codes' => ['IN'],
            ],
            'mg' => [
                'name' => 'Malagasy',
                'country_codes' => ['MG'],
            ],
            'mt' => [
                'name' => 'Maltese',
                'country_codes' => ['MT'],
            ],
            'mn' => [
                'name' => 'Mongolian',
                'country_codes' => ['MN'],
            ],
            'na' => [
                'name' => 'Nauru',
                'country_codes' => ['NR'],
            ],
            'nv' => [
                'name' => 'Navajo',
                'country_codes' => ['US'],
            ],
            'nr' => [
                'name' => 'Ndebele, South',
                'country_codes' => ['ZA'],
            ],
            'nd' => [
                'name' => 'Ndebele, North',
                'country_codes' => ['ZW'],
            ],
            'ng' => [
                'name' => 'Ndonga',
                'country_codes' => ['NA'],
            ],
            'ne' => [
                'name' => 'Nepali',
                'country_codes' => ['NP'],
            ],
            'nn' => [
                'name' => 'Norwegian Nynorsk',
                'country_codes' => ['NO'],
            ],
            'nb' => [
                'name' => 'Norwegian Bokmål',
                'country_codes' => ['NO'],
            ],
            'no' => [
                'name' => 'Norwegian',
                'country_codes' => ['NO'],
            ],
            'ny' => [
                'name' => 'Chichewa',
                'country_codes' => ['MW', 'ZM'],
            ],
            'oc' => [
                'name' => 'Occitan',
                'country_codes' => ['FR', 'ES', 'IT'],
            ],
            'oj' => [
                'name' => 'Ojibwa',
                'country_codes' => ['CA', 'US'],
            ],
            'or' => [
                'name' => 'Oriya',
                'country_codes' => ['IN'],
            ],
            'om' => [
                'name' => 'Oromo',
                'country_codes' => ['ET', 'KE'],
            ],
            'os' => [
                'name' => 'Ossetian',
                'country_codes' => ['GE', 'RU'],
            ],
            'pa' => [
                'name' => 'Panjabi',
                'country_codes' => ['IN', 'PK'],
            ],
            'pi' => [
                'name' => 'Pali',
                'country_codes' => ['MM', 'LK'],
            ],
            'pl' => [
                'name' => 'Polish',
                'country_codes' => ['PL'],
            ],
            'pt' => [
                'name' => 'Portuguese',
                'country_codes' => ['AO', 'BR', 'CV', 'GQ', 'GW', 'MO', 'MZ', 'PT', 'ST', 'TL'],
            ],
            'ps' => [
                'name' => 'Pashto',
                'country_codes' => ['AF', 'PK'],
            ],
            'qu' => [
                'name' => 'Quechua',
                'country_codes' => ['AR', 'BO', 'EC', 'PE'],
            ],
            'rm' => [
                'name' => 'Romansh',
                'country_codes' => ['CH'],
            ],
            'ro' => [
                'name' => 'Romanian',
                'country_codes' => ['MD', 'RO'],
            ],
            'rn' => [
                'name' => 'Rundi',
                'country_codes' => ['BI'],
            ],
            'ru' => [
                'name' => 'Russian',
                'country_codes' => ['BY', 'KZ', 'KG', 'MD', 'RU', 'UA'],
            ],
            'sg' => [
                'name' => 'Sango',
                'country_codes' => ['CF', 'CD'],
            ],
            'sa' => [
                'name' => 'Sanskrit',
                'country_codes' => ['IN'],
            ],
            'si' => [
                'name' => 'Sinhala',
                'country_codes' => ['LK'],
            ],
            'sk' => [
                'name' => 'Slovak',
                'country_codes' => ['SK'],
            ],
            'sl' => [
                'name' => 'Slovenian',
                'country_codes' => ['SI'],
            ],
            'se' => [
                'name' => 'Northern Sami',
                'country_codes' => ['NO', 'SE', 'FI'],
            ],
            'sm' => [
                'name' => 'Samoan',
                'country_codes' => ['AS', 'WS'],
            ],
            'sn' => [
                'name' => 'Shona',
                'country_codes' => ['ZW', 'MZ'],
            ],
            'sd' => [
                'name' => 'Sindhi',
                'country_codes' => ['PK', 'IN'],
            ],
            'so' => [
                'name' => 'Somali',
                'country_codes' => ['DJ', 'ET', 'SO', 'KE'],
            ],
            'st' => [
                'name' => 'Sotho, Southern',
                'country_codes' => ['LS', 'ZA'],
            ],
            'es' => [
                'name' => 'Spanish',
                'country_codes' => ['AR', 'BO', 'CL', 'CO', 'CR', 'CU', 'DO', 'EC', 'SV', 'GQ', 'GT', 'HN', 'MX', 'NI', 'PA', 'PY', 'PE', 'PR', 'ES', 'UY', 'VE'],
            ],
            'sc' => [
                'name' => 'Sardinian',
                'country_codes' => ['IT'],
            ],
            'sr' => [
                'name' => 'Serbian',
                'country_codes' => ['BA', 'RS', 'ME'],
            ],
            'ss' => [
                'name' => 'Swati',
                'country_codes' => ['SZ', 'ZA'],
            ],
            'su' => [
                'name' => 'Sundanese',
                'country_codes' => ['ID'],
            ],
            'sw' => [
                'name' => 'Swahili',
                'country_codes' => ['CD', 'KE', 'RW', 'TZ', 'UG'],
            ],
            'sv' => [
                'name' => 'Swedish',
                'country_codes' => ['SE', 'FI'],
            ],
            'ty' => [
                'name' => 'Tahitian',
                'country_codes' => ['PF'],
            ],
            'ta' => [
                'name' => 'Tamil',
                'country_codes' => ['IN', 'LK', 'SG'],
            ],
            'tt' => [
                'name' => 'Tatar',
                'country_codes' => ['RU'],
            ],
            'te' => [
                'name' => 'Telugu',
                'country_codes' => ['IN'],
            ],
            'tg' => [
                'name' => 'Tajik',
                'country_codes' => ['TJ'],
            ],
            'tl' => [
                'name' => 'Tagalog',
                'country_codes' => ['PH'],
            ],
            'th' => [
                'name' => 'Thai',
                'country_codes' => ['TH'],
            ],
            'bo' => [
                'name' => 'Tibetan',
                'country_codes' => ['CN', 'IN'],
            ],
            'ti' => [
                'name' => 'Tigrinya',
                'country_codes' => ['ER', 'ET'],
            ],
            'to' => [
                'name' => 'Tonga',
                'country_codes' => ['TO'],
            ],
            'tn' => [
                'name' => 'Tswana',
                'country_codes' => ['BW', 'ZA'],
            ],
            'ts' => [
                'name' => 'Tsonga',
                'country_codes' => ['MZ', 'ZA'],
            ],
            'tk' => [
                'name' => 'Turkmen',
                'country_codes' => ['TM'],
            ],
            'tr' => [
                'name' => 'Turkish',
                'country_codes' => ['TR', 'CY'],
            ],
            'tw' => [
                'name' => 'Twi',
                'country_codes' => ['GH'],
            ],
            'ug' => [
                'name' => 'Uighur',
                'country_codes' => ['CN'],
            ],
            'uk' => [
                'name' => 'Ukrainian',
                'country_codes' => ['UA'],
            ],
            'ur' => [
                'name' => 'Urdu',
                'country_codes' => ['IN', 'PK'],
            ],
            'uz' => [
                'name' => 'Uzbek',
                'country_codes' => ['UZ'],
            ],
            've' => [
                'name' => 'Venda',
                'country_codes' => ['ZA'],
            ],
            'vi' => [
                'name' => 'Vietnamese',
                'country_codes' => ['VN'],
            ],
            'vo' => [
                'name' => 'Volapük',
                'country_codes' => [],
            ],
            'wa' => [
                'name' => 'Walloon',
                'country_codes' => ['BE'],
            ],
            'wo' => [
                'name' => 'Wolof',
                'country_codes' => ['SN'],
            ],
            'xh' => [
                'name' => 'Xhosa',
                'country_codes' => ['ZA'],
            ],
            'yi' => [
                'name' => 'Yiddish',
                'country_codes' => ['DE', 'IL', 'PL', 'UA', 'US'],
            ],
            'yo' => [
                'name' => 'Yoruba',
                'country_codes' => ['NG', 'BJ'],
            ],
            'za' => [
                'name' => 'Zhuang',
                'country_codes' => ['CN'],
            ],
            'zu' => [
                'name' => 'Zulu',
                'country_codes' => ['ZA'],
            ],
        ];
    }
}
