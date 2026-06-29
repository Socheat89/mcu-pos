<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/core/helpers/url.php';

header('Content-Type: application/xml; charset=utf-8');

$staticPages = [
    [
        'href' => 'public/',
        'file' => __DIR__ . '/index.php',
        'changefreq' => 'weekly',
        'priority' => 1.0,
    ],
    [
        'href' => 'public/pricing.php',
        'file' => __DIR__ . '/pricing.php',
        'changefreq' => 'weekly',
        'priority' => 0.8,
    ],
    [
        'href' => 'public/register.php',
        'file' => __DIR__ . '/register.php',
        'changefreq' => 'monthly',
        'priority' => 0.7,
    ],
    [
        'href' => 'public/login.php',
        'file' => __DIR__ . '/login.php',
        'changefreq' => 'monthly',
        'priority' => 0.5,
    ],
];

$generatedAt = gmdate('c');

$output = [];
$output[] = '<?xml version="1.0" encoding="UTF-8"?>';
$output[] = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

foreach ($staticPages as $page) {
    $loc = mc_url($page['href'], true);
    $loc = htmlspecialchars($loc, ENT_XML1 | ENT_COMPAT, 'UTF-8');

    $lastmod = $generatedAt;
    if (!empty($page['file']) && is_file($page['file'])) {
        $lastmod = gmdate('c', filemtime($page['file']));
    }

    $changefreq = htmlspecialchars($page['changefreq'] ?? 'monthly', ENT_XML1 | ENT_COMPAT, 'UTF-8');
    $priority = number_format((float) ($page['priority'] ?? 0.5), 1, '.', '');

    $output[] = '  <url>';
    $output[] = '    <loc>' . $loc . '</loc>';
    $output[] = '    <lastmod>' . $lastmod . '</lastmod>';
    $output[] = '    <changefreq>' . $changefreq . '</changefreq>';
    $output[] = '    <priority>' . $priority . '</priority>';
    $output[] = '  </url>';
}

$output[] = '</urlset>';

echo implode("\n", $output);
