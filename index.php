<?php
// index.php - Front Controller (PHP 8.2 Optimized)

// ---------------------------------------------------------------------------
// Environment detection  (must happen BEFORE any output or error config)
// ---------------------------------------------------------------------------
$_host_early = isset($_SERVER['HTTP_HOST']) ? strtolower(str_replace('www.', '', $_SERVER['HTTP_HOST'])) : '';
$_isProduction = (
    strpos($_host_early, 'mekongcyberunit.app') !== false ||
    strpos($_host_early, 'mekongcy') !== false
);

// ---------------------------------------------------------------------------
// PHP error configuration — NEVER expose errors to browser on Production
// ---------------------------------------------------------------------------
if ($_isProduction) {
    error_reporting(E_ALL);           // Capture everything ...
    ini_set('display_errors', '0');   // ... but NEVER show it in the browser
    ini_set('display_startup_errors', '0');
    ini_set('log_errors', '1');       // Write to server error log instead
} else {
    // Local / development: full visibility
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
}


try {
    $baseDir = dirname(__FILE__);
    require_once $baseDir . '/core/bootstrap_session.php';
    date_default_timezone_set('Asia/Phnom_Penh');
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

    // Helper to serve static assets with correct MIME type
    $serveStatic = function(string $file) {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $mimes = [
            'css'   => 'text/css',
            'js'    => 'application/javascript',
            'png'   => 'image/png',
            'jpg'   => 'image/jpeg',
            'jpeg'  => 'image/jpeg',
            'gif'   => 'image/gif',
            'svg'   => 'image/svg+xml',
            'ico'   => 'image/x-icon',
            'woff'  => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf'   => 'font/ttf',
            'otf'   => 'font/otf',
            'json'  => 'application/json',
            'xml'   => 'application/xml',
            'pdf'   => 'application/pdf',
        ];
        $mime = $mimes[$ext] ?? 'application/octet-stream';
        header("Content-Type: $mime");
        header("Cache-Control: public, max-age=86400");
        readfile($file);
        exit;
    };


    // 1. Static/Public Routing (Explicit /public or /admin)
    if (strpos($path, '/public/') === 0 || strpos($path, '/admin/') === 0) {
        $cleanPath = str_replace('/', DIRECTORY_SEPARATOR, $path);
        $file = $baseDir . $cleanPath;
        if (file_exists($file) && !is_dir($file)) {
            if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'php') {
                include $file;
            } else {
                $serveStatic($file);
            }

            exit;
        }
    }

    // 1.1 "Hidden" Public Routing (Serve /login.php from /public/login.php)
    // This allows cleaner URLs without redirecting to /public/
    $publicPath = $baseDir . '/public' . str_replace('/', DIRECTORY_SEPARATOR, $path);
    if ($path !== '/' && file_exists($publicPath) && !is_dir($publicPath)) {
        // Security check: Don't allow accessing PHP files that shouldn't be public if any (though public folder is meant to be public)
        if (strtolower(pathinfo($publicPath, PATHINFO_EXTENSION)) === 'php') {
            include $publicPath;
        } else {
            $serveStatic($publicPath);
        }

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
            try {
                Tenant::detect($tenantSlug);
            } catch (TenantNotFoundException $e) {
                // Log the lookup failure server-side (slug is safe to log)
                error_log('[MCU-POS] Tenant not found for slug "' . $tenantSlug . '" — IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
                http_response_code(404);
                include $baseDir . '/public/404.php';
                exit;
            }
            
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
                } elseif ($sub === 'ingredients') {
                    require_once $baseDir . '/modules/pos/controllers/IngredientController.php';
                    $controller = new IngredientController();
                } elseif ($sub === 'menu') {
                    require_once $baseDir . '/modules/pos/controllers/MenuController.php';
                    $controller = new MenuController();
                } elseif ($sub === 'sessions') {
                    require_once $baseDir . '/modules/pos/controllers/SessionController.php';
                    $controller = new SessionController();
                } elseif ($sub === 'cashiers') {
                    require_once $baseDir . '/modules/pos/controllers/CashierController.php';
                    require_once $baseDir . '/core/classes/User.php';
                    require_once $baseDir . '/core/classes/Settings.php';
                    $controller = new CashierController();

                } elseif ($sub === 'stores') {
                    require_once $baseDir . '/core/classes/Store.php';
                    require_once $baseDir . '/modules/pos/controllers/StoreController.php';
                    $controller = new StoreController();

                } elseif ($sub === 'gps') {
                    require_once $baseDir . '/modules/pos/controllers/GpsController.php';
                    $controller = new GpsController();
                    $action = 'dashboard';

                } elseif ($sub === 'holds') {
                    require_once $baseDir . '/modules/pos/controllers/OrderController.php';
                    $controller = new OrderController();
                    $action = 'holds';
                } elseif ($sub === 'stock-report') {
                    require_once $baseDir . '/modules/pos/controllers/StockReportController.php';
                    $controller = new StockReportController();
                } elseif ($sub === 'stock-transfer') {
                    require_once $baseDir . '/core/classes/Store.php';
                    require_once $baseDir . '/modules/pos/controllers/StockTransferController.php';
                    $controller = new StockTransferController();
                }

                if ($controller) {
                    // Check for third segment (Action or ID)
                    if (isset($segments[3])) {
                        $thirdSeg = $segments[3];
                        
                        // Case A: /module/action (e.g., /products/create or /products/deleteCategory/5)
                        if (!is_numeric($thirdSeg) && method_exists($controller, $thirdSeg)) {
                            $action = $thirdSeg;
                            if (isset($segments[4])) {
                                $controller->$action($segments[4]);
                            } else {
                                $controller->$action();
                            }
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
        if (Auth::check()) {
            $urlPrefix = mc_base_path();
            $subdomain = $_SESSION['tenant_subdomain'] ?? '';
            if (Auth::isSuperAdmin()) {
                header("Location: $urlPrefix/admin/index.php");
                exit;
            } elseif (!empty($subdomain)) {
                header("Location: $urlPrefix/$subdomain/pos/pos");
                exit;
            }
        }
        include $baseDir . '/public/index.php';
        exit;
    }

    // 4. Default 404
    http_response_code(404);
    if (file_exists($baseDir . '/public/404.php')) {
        include $baseDir . '/public/404.php';
    } else {
        echo "<h1>404 &mdash; Page Not Found</h1>";
    }

} catch (TenantNotFoundException $e) {
    // Tenant 404s that bubble up from outside the routing block (edge case)
    error_log('[MCU-POS] TenantNotFoundException (outer): ' . $e->getMessage() . ' | IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    http_response_code(404);
    $baseDir = $baseDir ?? dirname(__FILE__);
    if (file_exists($baseDir . '/public/404.php')) {
        include $baseDir . '/public/404.php';
    } else {
        echo "<h1>404 &mdash; Store Not Found</h1>";
    }

} catch (Throwable $e) {
    // ---------------------------------------------------------------------------
    // SECURE CATCH-ALL — log full detail server-side, show nothing sensitive
    // ---------------------------------------------------------------------------
    $logMessage = sprintf(
        '[MCU-POS] Uncaught %s: %s in %s on line %d%sStack trace:%s%s',
        get_class($e),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        PHP_EOL,
        PHP_EOL,
        $e->getTraceAsString()
    );
    error_log($logMessage);

    if ($isProduction ?? $_isProduction) {
        // Production: generic error page — zero internal details exposed
        if (!headers_sent()) {
            http_response_code(500);
        }
        $baseDir = $baseDir ?? dirname(__FILE__);
        if (file_exists($baseDir . '/public/500.php')) {
            include $baseDir . '/public/500.php';
        } else {
            echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Service Unavailable</title></head>";
            echo "<body style='font-family:sans-serif;text-align:center;padding:60px;'>"
                . "<h1 style='color:#c0392b;'>Something went wrong</h1>"
                . "<p>We&rsquo;re working on it. Please try again shortly.</p>"
                . "</body></html>";
        }
    } else {
        // Development: full details in a styled panel
        if (!headers_sent()) http_response_code(500);
        echo "<div style='padding:20px;background:#fff1f2;color:#be123c;border:1px solid #fda4af;border-radius:8px;font-family:monospace;margin:20px;line-height:1.6;'>";
        echo "<h2 style='margin-top:0;border-bottom:1px solid #fda4af;padding-bottom:10px;'>&#9888; System Error (Dev Mode)</h2>";
        echo "<strong>Type:</strong> " . htmlspecialchars(get_class($e)) . "<br>";
        echo "<strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "<br>";
        echo "<strong>File:</strong> " . htmlspecialchars($e->getFile()) . " (Line: " . $e->getLine() . ")<br>";
        echo "<details style='margin-top:10px;'><summary style='cursor:pointer;'>&#9660; Stack Trace</summary>";
        echo "<pre style='font-size:12px;overflow:auto;background:#1e1e1e;color:#d4d4d4;padding:12px;border-radius:4px;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre></details>";
        echo "</div>";
    }
}
?>