<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/core/helpers/url.php';

header('Content-Type: text/plain; charset=utf-8');

$baseUrl = rtrim(mc_url('', true), '/');
$sitemapUrl = $baseUrl . '/sitemap.xml';

$disallowPaths = [
    '/admin/',
    '/public/admin/',
    '/public/api/',
    '/public/logs/',
    '/public/login_process.php',
    '/public/register_process.php',
    '/public/renew_process.php',
    '/public/logout.php',
];

$lines = [];
$lines[] = 'User-agent: *';
foreach ($disallowPaths as $path) {
    $lines[] = 'Disallow: ' . $path;
}
$lines[] = '';
$lines[] = 'Sitemap: ' . $sitemapUrl;

echo implode("\n", $lines) . "\n";
