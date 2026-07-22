<?php
// core/helpers/api.php

if (!function_exists('mc_api_apply_cors')) {
    function mc_api_apply_cors(string $methods = 'GET, POST, OPTIONS', string $headers = 'Content-Type, X-API-Key'): void {
        header('Content-Type: application/json');

        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        if ($origin !== '') {
            $originHost = parse_url($origin, PHP_URL_HOST);
            $requestHost = $_SERVER['HTTP_HOST'] ?? '';
            $requestHost = preg_replace('/:\d+$/', '', strtolower($requestHost));
            $originHost = strtolower((string) $originHost);

            if ($originHost !== '' && hash_equals($requestHost, $originHost)) {
                header('Access-Control-Allow-Origin: ' . $origin);
                header('Access-Control-Allow-Credentials: true');
                header('Vary: Origin');
            }
        }

        header('Access-Control-Allow-Methods: ' . $methods);
        header('Access-Control-Allow-Headers: ' . $headers);
    }
}

if (!function_exists('mc_api_preflight')) {
    function mc_api_preflight(string $methods = 'GET, POST, OPTIONS', string $headers = 'Content-Type, X-API-Key'): void {
        mc_api_apply_cors($methods, $headers);
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }
}

if (!function_exists('mc_json')) {
    function mc_json(array $payload, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($payload);
        exit;
    }
}

if (!function_exists('mc_json_error')) {
    function mc_json_error(string $message = 'Server error', int $status = 500, ?string $code = null): void {
        $payload = ['success' => false, 'error' => $message];
        if ($code !== null) {
            $payload['code'] = $code;
        }

        mc_json($payload, $status);
    }
}

if (!function_exists('mc_log_exception')) {
    function mc_log_exception(string $context, Throwable $e): void {
        error_log($context . ': ' . $e->getMessage());
    }
}
