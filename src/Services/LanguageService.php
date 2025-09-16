<?php

namespace Devrabiul\LaravelGeoGenius\Services;

use Devrabiul\LaravelGeoGenius\Services\TranslateService\TranslateService;
use Devrabiul\LaravelGeoGenius\Trait\LanguageTrait;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageService
{
    use LanguageTrait;

    public function getUserLanguage(): mixed
    {
        if (session()->has('genius_local_Language')) {
            $languageCode = session('genius_local_Language');
        } else {
            $userCountryCode = strtolower(laravelGeoGenius()->geo()->getCountryCode());
            $languageCode = LanguageTrait::getLanguageCodeFromCountryCode($userCountryCode);

            $direction = self::isRtl($languageCode) ? 'rtl' : 'ltr';
            session()->put('genius_local_Language', $languageCode);
            session()->put('direction', $direction);
        }
        return $languageCode;
    }

    public function changeUserLanguage($locale): void
    {
        $languageCode = LanguageTrait::checkLocaleValidity(locale: $locale);
        $direction = self::isRtl($languageCode) ? 'rtl' : 'ltr';
        session()->put('genius_local_Language', $languageCode);
        session()->put('direction', $direction);
        app()->setLocale($languageCode);
    }

    public function getUserLangDirection(): string
    {
        return session('direction') ?? 'ltr';
    }

    /**
     * Detect user preferred language from Accept-Language header.
     *
     * @return string|null Returns language code like 'en', 'fr', 'bn', etc., or null if none detected.
     */
    public function detect(): ?string
    {
        $acceptLanguage = Request::header('Accept-Language');

        if (!$acceptLanguage) {
            return null;
        }

        // Get the first language code before any ';' or ',' with quality factor
        $languages = explode(',', $acceptLanguage);

        if (count($languages) === 0) {
            return null;
        }

        $primaryLanguage = strtolower(explode(';', $languages[0])[0]);

        return $primaryLanguage ?: null;
    }

    /**
     * Get full language name from language code.
     *
     * @param string $langCode e.g. 'en', 'fr'
     * @return string|null e.g. 'English', 'French', or null if unknown
     */
    public function getLanguageName(string $langCode): ?string
    {
        $map = $this->getAllLanguageNames();
        $langCode = strtolower(substr($langCode, 0, 2)); // handle 'en-US' => 'en'
        return $map[$langCode] ?? null;
    }

    /**
     * Check if language is right-to-left (RTL).
     *
     * @param string $langCode
     * @return bool
     */
    public function isRtl(string $langCode): bool
    {
        $rtlLanguages = ['ar', 'he', 'fa', 'ur'];
        return in_array(strtolower(substr($langCode, 0, 2)), $rtlLanguages, true);
    }

    public function translateAndUpdateNewMessages($translatedMessages, $newMessagesArray, $textKey, $languageCode, $manualMessage = null): array
    {
        $newDataFiltered = array_filter($newMessagesArray, function ($key) use ($textKey) {
            return $key !== $textKey;
        }, ARRAY_FILTER_USE_KEY);

        if (isset($newMessagesArray[$textKey])) {
            $text = ucfirst(str_replace('_', ' ', LanguageTrait::geniusRemoveInvalidCharacters(str_replace("'", "", $textKey))));

            if ($manualMessage) {
                $translatedText = $manualMessage;
            } else {
                $translated = $this->autoTranslator($text, 'en', $languageCode);
                $translatedText = $translated ? LanguageTrait::geniusRemoveInvalidCharacters(preg_replace('/\s+/', ' ', $translated)) : null;
            }

            if ($translatedText !== null) {
                if (Str::lower($translatedText) == Str::lower($textKey)) {
                    $newDataFiltered[$textKey] = $translatedText;
                    $status = $languageCode == 'en' ? 1 : 0;
                } else {
                    $removeFromNewMessages[$textKey] = $translatedText;
                    $status = 1;
                }
            } else {
                $status = 0;
            }
        } else {
            $status = 0;
        }

        $this->writeTranslationFile($languageCode, 'new-messages.php', $newDataFiltered);
        $this->writeTranslationFile($languageCode, 'messages.php', array_merge($translatedMessages, $removeFromNewMessages ?? []));

        return [
            'status' => $status,
            'message' => geniusTranslate($status ? 'Translate_Successful' : 'Cannot_translate_now'),
            'translatedText' => $translatedText ?? '',
        ];
    }

    function writeTranslationFile($languageCode, $fileName, $messagesData): void
    {
        $messagesString = "<?php\n\nreturn [\n";
        foreach ($messagesData as $key => $value) {
            $messagesString .= "\t\"" . $key . "\" => \"" . $value . "\",\n";
        }
        $messagesString .= "];\n";
        file_put_contents(base_path('resources/lang/' . $languageCode . '/' . $fileName), $messagesString);
    }

    public function autoTranslator($text, $languageFrom, $languageTo): array|string|null
    {
        try {
            $translator = new TranslateService();
            $translated = $translator->setSource($languageFrom)->setTarget($languageTo)->translate(str_replace('_', ' ', $text));

            if ($this->needsTransliteration($languageTo)) {
                $translated = $this->transliterateCyrillicToLatin($translated, $languageTo);
            }

            return $translated;
        } catch (Throwable $th) {
            return LanguageTrait::geniusRemoveInvalidCharacters($text);
        }
    }

    public function needsTransliteration(string $targetLang): bool
    {
        $languagesRequiringTransliteration = [
            'sr-Latn', // Serbian (Latin, Google only returns Cyrillic)
            'bs-Latn-BA', // Bosnian Latin may sometimes return Cyrillic
            'az-Latn-AZ', // Edge cases
            'uz-Latn-UZ',
        ];

        return in_array($targetLang, $languagesRequiringTransliteration);
    }

    function transliterateCyrillicToLatin(string $text, string $langCode): string
    {
        $map = [];

        if (in_array($langCode, ['sr-Latn', 'bs-Latn-BA'])) {
            // Serbian and Bosnian
            $map = [
                'Љ' => 'Lj', 'Њ' => 'Nj', 'Џ' => 'Dž', 'љ' => 'lj', 'њ' => 'nj', 'џ' => 'dž',
                'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Ђ' => 'Đ', 'Е' => 'E', 'Ж' => 'Ž', 'З' => 'Z',
                'И' => 'I', 'Ј' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O', 'П' => 'P',
                'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'Ћ' => 'Ć', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C', 'Ч' => 'Č', 'Ш' => 'Š',
                'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'ђ' => 'đ', 'е' => 'e', 'ж' => 'ž', 'з' => 'z',
                'и' => 'i', 'ј' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p',
                'р' => 'r', 'с' => 's', 'т' => 't', 'ћ' => 'ć', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'č', 'ш' => 'š',
            ];
        } elseif ($langCode === 'uz-Latn-UZ') {
            // Uzbek (Cyrillic to Latin)
            $map = [
                'А' => 'A', 'Б' => 'B', 'Д' => 'D', 'Э' => 'E', 'Ф' => 'F', 'Г' => 'G', 'Ҳ' => 'H', 'И' => 'I', 'Ж' => 'J',
                'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Қ' => 'Q', 'Р' => 'R', 'С' => 'S',
                'Ш' => 'Sh', 'Т' => 'T', 'У' => 'U', 'В' => 'V', 'Х' => 'X', 'Й' => 'Y', 'З' => 'Z', 'Ч' => 'Ch', 'Ў' => 'O‘',
                'Ю' => 'Yu', 'Я' => 'Ya', 'Ё' => 'Yo', 'Ц' => 'Ts', 'Ъ' => '', 'Ь' => '', 'Е' => 'Ye', 'НГ' => 'Ng',
                'а' => 'a', 'б' => 'b', 'д' => 'd', 'э' => 'e', 'ф' => 'f', 'г' => 'g', 'ҳ' => 'h', 'и' => 'i', 'ж' => 'j',
                'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'қ' => 'q', 'р' => 'r', 'с' => 's',
                'ш' => 'sh', 'т' => 't', 'у' => 'u', 'в' => 'v', 'х' => 'x', 'й' => 'y', 'з' => 'z', 'ч' => 'ch', 'ў' => 'o‘',
                'ю' => 'yu', 'я' => 'ya', 'ё' => 'yo', 'ц' => 'ts', 'ъ' => '', 'ь' => '', 'е' => 'ye', 'нг' => 'ng',
            ];
        } elseif ($langCode === 'az-Latn-AZ') {
            // Azerbaijani (Cyrillic to Latin)
            $map = [
                'А' => 'A', 'Б' => 'B', 'С' => 'S', 'Ч' => 'Ç', 'Д' => 'D', 'Ә' => 'Ə', 'Е' => 'E', 'Ф' => 'F',
                'Г' => 'Q', 'Һ' => 'H', 'Ы' => 'I', 'Ж' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N',
                'О' => 'O', 'Ө' => 'Ö', 'П' => 'P', 'Р' => 'R', 'Ш' => 'Ş', 'Т' => 'T', 'У' => 'U', 'Ү' => 'Ü',
                'В' => 'V', 'Й' => 'Y', 'З' => 'Z',
                'а' => 'a', 'б' => 'b', 'с' => 's', 'ч' => 'ç', 'д' => 'd', 'ә' => 'ə', 'е' => 'e', 'ф' => 'f',
                'г' => 'q', 'һ' => 'h', 'ы' => 'ı', 'ж' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
                'о' => 'o', 'ө' => 'ö', 'п' => 'p', 'р' => 'r', 'ш' => 'ş', 'т' => 't', 'у' => 'u', 'ү' => 'ü',
                'в' => 'v', 'й' => 'y', 'з' => 'z',
            ];
        }

        return strtr($text, $map);
    }
}
