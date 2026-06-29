<?php
// public/set_lang.php
require_once __DIR__ . '/../core/classes/Language.php';
require_once __DIR__ . '/../core/helpers/url.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    Language::setLanguage($lang);
}

$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : mc_url('/');
header("Location: " . $referer);
exit;
