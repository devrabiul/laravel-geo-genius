<?php

use Devrabiul\LaravelGeoGenius\Trait\LanguageTrait;
use Illuminate\Support\Facades\App;

if (!function_exists('geniusTrans')) {
    function geniusTrans($key = null): string|null
    {
        return geniusTranslate($key);
    }
}

if (!function_exists('geniusTranslate')) {
    function geniusTranslate($key = null): string|null
    {
        if (!empty($key) || $key == 0) {
            if (App::getLocale() != 'en') {
                $languageDirectories = LanguageTrait::getLanguageFilesDirectories(path: base_path('resources/lang/'));
                foreach ($languageDirectories as $directory) {
                    LanguageTrait::geniusTranslateMessageValueByKey(local: $directory, key: $key);
                }
            }
            $local = LanguageTrait::checkLocaleValidity(locale: App::getLocale());
            return LanguageTrait::geniusTranslateMessageValueByKey(local: $local, key: $key);
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