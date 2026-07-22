<?php
// public/set_lang.php
require_once __DIR__ . '/../core/classes/Language.php';
require_once __DIR__ . '/../core/helpers/url.php';

require_once __DIR__ . '/../core/bootstrap_session.php';

if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    Language::setLanguage($lang);
}

$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : mc_url('/');
if ($referer) {
    $refererHost = strtolower((string) parse_url($referer, PHP_URL_HOST));
    $currentHost = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
    $currentHost = preg_replace('/:\d+$/', '', $currentHost);
    if ($refererHost !== '' && !hash_equals($currentHost, $refererHost)) {
        $referer = mc_url('/');
    }
}
header("Location: " . $referer);
exit;
