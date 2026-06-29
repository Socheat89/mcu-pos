<?php

class Language {
    private static $translations = [];
    private static $currentLang = 'en';
    private static $availableLangs = ['en', 'km', 'zh'];

    public static function init() {
        if (isset($_SESSION['lang']) && in_array($_SESSION['lang'], self::$availableLangs)) {
            self::$currentLang = $_SESSION['lang'];
        } elseif (isset($_COOKIE['lang']) && in_array($_COOKIE['lang'], self::$availableLangs)) {
            self::$currentLang = $_COOKIE['lang'];
            $_SESSION['lang'] = self::$currentLang;
        }

        self::loadLanguage(self::$currentLang);
    }

    public static function setLanguage($lang) {
        if (in_array($lang, self::$availableLangs)) {
            self::$currentLang = $lang;
            $_SESSION['lang'] = $lang;
            setcookie('lang', $lang, time() + (86400 * 30), "/"); // 30 days
            self::loadLanguage($lang);
            return true;
        }
        return false;
    }

    public static function getCurrentLang() {
        return self::$currentLang;
    }

    private static function loadLanguage($lang) {
        $baseDir = dirname(dirname(dirname(__FILE__)));
        $file = $baseDir . "/lang/{$lang}.php";
        if (file_exists($file)) {
            self::$translations = require $file;
        } else {
            // Fallback to English if file not found
            $enFile = $baseDir . "/lang/en.php";
            if (file_exists($enFile)) {
                self::$translations = require $enFile;
            }
        }
    }

    public static function get($key, $placeholders = []) {
        $text = isset(self::$translations[$key]) ? self::$translations[$key] : $key;
        
        foreach ($placeholders as $placeholder => $value) {
            $text = str_replace(":{$placeholder}", $value, $text);
        }
        
        return $text;
    }
}

// Global helper function
if (!function_exists('__')) {
    function __($key, $placeholders = []) {
        return Language::get($key, $placeholders);
    }
}
