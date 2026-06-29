<?php
// core/helpers/url.php
// Centralized helpers for building URLs that adapt to root or subfolder deployments.

if (!function_exists('mc_base_path')) {
    function mc_base_path(): string {
        static $basePath = null;
        if ($basePath !== null) {
            return $basePath;
        }

        $envValue = getenv('MC_BASE_PATH');
        if ($envValue === false) {
            $envValue = $_ENV['MC_BASE_PATH'] ?? ($_SERVER['MC_BASE_PATH'] ?? null);
        }
        if ($envValue !== null) {
            $envValue = trim((string) $envValue);
            if ($envValue === '' || $envValue === '/') {
                return $basePath = '';
            }
            $envValue = '/' . trim($envValue, '/');
            return $basePath = ($envValue === '/' ? '' : $envValue);
        }

        $projectRoot = str_replace('\\', '/', realpath(dirname(__DIR__, 2)) ?: dirname(__DIR__, 2));
        $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
        $documentRoot = str_replace('\\', '/', realpath($documentRoot) ?: $documentRoot);

        if ($documentRoot && stripos($projectRoot, $documentRoot) === 0) {
            $relative = trim(substr($projectRoot, strlen($documentRoot)), '/');
            $basePath = $relative === '' ? '' : '/' . $relative;
            return $basePath;
        }

        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/');
        $scriptDir = rtrim(dirname($scriptName), '/');
        if ($scriptDir && $scriptDir !== '.') {
            // If script path ends with /public or /public/api, strip that part to get base folder.
            if (preg_match('#(/public(?:/api)?)$#i', $scriptDir, $matches)) {
                $scriptDir = substr($scriptDir, 0, -strlen($matches[1]));
            }
            $scriptDir = rtrim($scriptDir, '/');
            $basePath = $scriptDir ?: '';
            return $basePath;
        }

        return $basePath = '';
    }
}

if (!function_exists('mc_url')) {
    function mc_url(string $path = '', bool $absolute = false): string {
        $base = mc_base_path();
        $normalizedPath = $path === '' ? '' : '/' . ltrim($path, '/');
        $relative = $base === '' ? $normalizedPath : rtrim($base, '/') . $normalizedPath;
        if ($relative === '') {
            $relative = '/';
        }

        if ($absolute) {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            return $scheme . '://' . $host . $relative;
        }
        return $relative;
    }
}

if (!function_exists('mc_asset')) {
    function mc_asset(string $path = '', bool $absolute = false): string {
        $cleanPath = 'public/' . ltrim($path, '/');
        return mc_url($cleanPath, $absolute);
    }
}
