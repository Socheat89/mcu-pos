<?php
// config/env.php
// Small environment helper shared by config files and public endpoints.

if (!function_exists('mc_env')) {
    function mc_env(string $key, $default = null) {
        $value = getenv($key);
        if ($value === false) {
            $value = $_ENV[$key] ?? ($_SERVER[$key] ?? null);
        }

        return $value === null ? $default : $value;
    }
}
if (!function_exists('mc_required_env')) {
    function mc_required_env(string $key): string {
        $value = mc_env($key);
        if ($value === null || $value === '') {
            throw new RuntimeException("Missing required environment variable: {$key}");
        }

        return (string) $value;
    }
}

if (!function_exists('mc_bool_env')) {
    function mc_bool_env(string $key, bool $default = false): bool {
        $value = mc_env($key);
        if ($value === null || $value === '') {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}

if (!function_exists('mc_request_host')) {
    function mc_request_host(): string {
        $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
        return preg_replace('/:\d+$/', '', trim($host, '[]'));
    }
}

if (!function_exists('mc_is_local_request')) {
    function mc_is_local_request(): bool {
        $host = mc_request_host();
        $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';

        return in_array($host, ['localhost', '127.0.0.1', '::1'], true)
            || strpos($host, 'localhost') !== false
            || in_array($remoteAddr, ['127.0.0.1', '::1'], true);
    }
}

if (!function_exists('mc_is_production_request')) {
    function mc_is_production_request(): bool {
        if (mc_bool_env('MC_PRODUCTION', false)) {
            return true;
        }

        $host = mc_request_host();
        return $host !== ''
            && !mc_is_local_request()
            && (
                strpos($host, 'mekongcyberunit.app') !== false
                || strpos($host, 'mekongcy') !== false
                || strpos($host, 'mcu-pos.me') !== false
            );
    }
}
