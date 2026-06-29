<?php
// index.php - Front Controller (PHP 8.2 Optimized)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

try {
    // Harden Session Security
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Lax');

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    date_default_timezone_set('Asia/Phnom_Penh');

    $baseDir = dirname(__FILE__);
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
    $host = str_replace('www.', '', $host);
    $isProduction = (strpos($host, 'mekongcyberunit.app') !== false || strpos($host, 'mekongcy') !== false);
    
    // Auto-load Core Components
    require_once $baseDir . '/core/classes/Database.php';
    require_once $baseDir . '/core/classes/Tenant.php';
    require_once $baseDir . '/core/classes/Auth.php';
    require_once $baseDir . '/core/classes/Language.php';
    require_once $baseDir . '/middleware/AuthMiddleware.php';
    require_once $baseDir . '/middleware/TenantMiddleware.php';

    // Initialize Language
    Language::init();

    // Dynamic Base Path Detection
    // This allows the app to run in root, /Mekong_CyberUnit, or any other subfolder
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
    $requestUri = $_SERVER['REQUEST_URI'];
    
    // Remove query string
    if (strpos($requestUri, '?') !== false) {
        $requestUri = explode('?', $requestUri)[0];
    }
    
    // If we are in a subfolder, strip it from the request URI to get the relative path
    if ($scriptDir !== '/' && strpos($requestUri, $scriptDir) === 0) {
        $path = substr($requestUri, strlen($scriptDir));
    } else {
        $path = $requestUri;
    }

    if (empty($path)) $path = '/';

    // 1. Static/Public Routing (Explicit /public or /admin)
    if (strpos($path, '/public/') === 0 || strpos($path, '/admin/') === 0) {
        $cleanPath = str_replace('/', DIRECTORY_SEPARATOR, $path);
        $file = $baseDir . $cleanPath;
        if (file_exists($file) && !is_dir($file)) {
            include $file;
            exit;
        }
    }

    // 1.1 "Hidden" Public Routing (Serve /login.php from /public/login.php)
    // This allows cleaner URLs without redirecting to /public/
    $publicPath = $baseDir . '/public' . str_replace('/', DIRECTORY_SEPARATOR, $path);
    if ($path !== '/' && file_exists($publicPath) && !is_dir($publicPath)) {
        // Security check: Don't allow accessing PHP files that shouldn't be public if any (though public folder is meant to be public)
        include $publicPath;
        exit;
    }

    // 2. Tenant Routing (e.g. /socheatcofe/dashboard)
    $segments = explode('/', trim($path, '/'));
    
    // If running in a subdirectory on production, the first segment might be the folder name
    if (isset($segments[0]) && $segments[0] === 'Mekong_CyberUnit') {
        array_shift($segments);
    }

    if (count($segments) >= 2) {
        $tenantSlug = $segments[0];
        $module = $segments[1];

        // List of reserved words to skip tenant detection
        $reserved = ['admin', 'public', 'core', 'middleware', 'config', 'modules', 'api'];
        if (!in_array($tenantSlug, $reserved)) {
            Tenant::detect($tenantSlug);
            
            if ($module === 'dashboard') {
                include $baseDir . '/tenant/dashboard.php';
                exit;
            }
            if ($module === 'users') {
                include $baseDir . '/tenant/users.php';
                exit;
            }
            if ($module === 'settings') {
                include $baseDir . '/tenant/settings.php';
                exit;
            }
            if ($module === 'logout') {
                include $baseDir . '/public/logout.php';
                exit;
            }
            // POS Module Routing
            if ($module === 'pos' && isset($segments[2])) {
                $sub = $segments[2];
                $controller = null;
                $action = 'index';

                if ($sub === 'dashboard') {
                    require_once $baseDir . '/modules/pos/controllers/DashboardController.php';
                    $controller = new DashboardController();
                } elseif ($sub === 'pos') {
                    require_once $baseDir . '/modules/pos/controllers/PosController.php';
                    $controller = new PosController();
                } elseif ($sub === 'products') {
                    require_once $baseDir . '/modules/pos/controllers/ProductController.php';
                    $controller = new ProductController();
                } elseif ($sub === 'orders') {
                    require_once $baseDir . '/modules/pos/controllers/OrderController.php';
                    $controller = new OrderController();
                } elseif ($sub === 'customers') {
                    require_once $baseDir . '/modules/pos/controllers/CustomerController.php';
                    $controller = new CustomerController();
                } elseif ($sub === 'reports') {
                    require_once $baseDir . '/modules/pos/controllers/ReportsController.php';
                    $controller = new ReportsController();
                } elseif ($sub === 'settings') {
                    require_once $baseDir . '/modules/pos/controllers/SettingsController.php';
                    $controller = new SettingsController();
                } elseif ($sub === 'menu') {
                    require_once $baseDir . '/modules/pos/controllers/MenuController.php';
                    $controller = new MenuController();
                } elseif ($sub === 'holds') {
                    require_once $baseDir . '/modules/pos/controllers/OrderController.php';
                    $controller = new OrderController();
                    $action = 'holds';
                }

                if ($controller) {
                    // Check for third segment (Action or ID)
                    if (isset($segments[3])) {
                        $thirdSeg = $segments[3];
                        
                        // Case A: /module/action (e.g., /products/create)
                        if (!is_numeric($thirdSeg) && method_exists($controller, $thirdSeg)) {
                            $action = $thirdSeg;
                            $controller->$action();
                        } 
                        // Case B: /module/id/... (e.g., /products/5 or /products/5/edit)
                        else {
                            $id = $thirdSeg;
                            if (isset($segments[4])) {
                                $action = $segments[4];
                                if (method_exists($controller, $action)) {
                                    $controller->$action($id);
                                } else {
                                    http_response_code(404);
                                    echo "<h1>404 - Action Not Found</h1>";
                                }
                            } else {
                                // Default action for ID if no sub-action provided
                                if (method_exists($controller, 'show')) {
                                    $controller->show($id);
                                } elseif (method_exists($controller, 'edit')) {
                                    // Often /products/5 means edit
                                    $controller->edit($id);
                                } else {
                                    $controller->index();
                                }
                            }
                        }
                    } else {
                        // Case C: /module (e.g., /products)
                        $controller->$action();
                    }
                    exit;
                }
            }
        }
    }

    // 3. Root/Home Page
    if ($path === '/' || $path === '') {
        include $baseDir . '/public/index.php';
        exit;
    }

    // 4. Default 404
    http_response_code(404);
    echo "<h1>404 - Page Not Found</h1>";
    echo "Path: " . htmlspecialchars($path);

} catch (Throwable $e) {
    echo "<div style='padding:20px; background:#fff1f2; color:#be123c; border:1px solid #fda4af; border-radius:8px; font-family:sans-serif; margin:20px; line-height:1.5;'>";
    echo "<h2 style='margin-top:0; border-bottom:1px solid #fda4af; padding-bottom:10px;'>System Error (Captured)</h2>";
    echo "<strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "<strong>File:</strong> " . htmlspecialchars($e->getFile()) . " (Line: " . $e->getLine() . ")<br>";
    echo "<details style='margin-top:10px;'><summary>Click for Stack Trace</summary><pre style='font-size:12px; overflow:auto;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre></details>";
    echo "</div>";
}
?>