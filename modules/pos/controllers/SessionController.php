<?php
// modules/pos/controllers/SessionController.php
require_once __DIR__ . '/../../../core/classes/Database.php';
require_once __DIR__ . '/../../../core/classes/Tenant.php';
require_once __DIR__ . '/../../../core/classes/Store.php';
require_once __DIR__ . '/../../../core/classes/Auth.php';
require_once __DIR__ . '/../../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../../middleware/TenantMiddleware.php';

class SessionController {
    public function index() {
        TenantMiddleware::handle();
        AuthMiddleware::handle();

        if (!Tenant::hasModule('pos')) {
            die('POS system not subscribed for your plan');
        }

        if (!Auth::hasPermission('pos', 'read')) {
            die('No permission to view POS sessions');
        }

        $db = Database::getInstance();
        $tenantId = Tenant::getId();

        // Load stores for filter
        $allStores = Store::getAll($tenantId);
        $storeId = isset($_GET['store_id']) ? (int)$_GET['store_id'] : 0;
        $storeFilter = '';
        $storeParams = [$tenantId];

        if ($storeId > 0) {
            $storeFilter = ' AND store_id = ?';
            $storeParams[] = $storeId;
        }

        // Safe: check if store_id column exists before filtering
        try {
            $sessions = $db->fetchAll(
                "SELECT s.*, u.username, st.name as store_name, st.code as store_code
                 FROM pos_sessions s 
                 JOIN users u ON s.user_id = u.id
                 LEFT JOIN stores st ON s.store_id = st.id
                 WHERE s.tenant_id = ?" . $storeFilter . "
                 ORDER BY s.opened_at DESC",
                $storeParams
            );

            $activeSession = $db->fetchOne(
                "SELECT * FROM pos_sessions WHERE tenant_id = ? AND status = 'open'" . $storeFilter,
                $storeParams
            );
        } catch (Exception $e) {
            // Fallback: store_id column doesn't exist yet, query without store filter
            $sessions = $db->fetchAll(
                "SELECT s.*, u.username, '' as store_name, '' as store_code
                 FROM pos_sessions s 
                 JOIN users u ON s.user_id = u.id
                 WHERE s.tenant_id = ?
                 ORDER BY s.opened_at DESC",
                [$tenantId]
            );

            $activeSession = $db->fetchOne(
                "SELECT * FROM pos_sessions WHERE tenant_id = ? AND status = 'open'",
                [$tenantId]
            );
        }

        include __DIR__ . '/../views/sessions.php';
    }

    public function open() {
        TenantMiddleware::handle();
        AuthMiddleware::handle();

        if (!Tenant::hasModule('pos')) {
            die('POS system not subscribed for your plan');
        }

        if (!Auth::hasPermission('pos', 'write')) {
            die('No permission to open POS sessions');
        }

        $db = Database::getInstance();
        $tenantId = Tenant::getId();
        $allStores = Store::getAll($tenantId);

        // Auto-detect store: Store switcher → user's assigned store → default store
        $currentStore = Store::getCurrent($tenantId);
        $autoStoreId = $currentStore ? $currentStore['id'] : null;

        // Check if there is already an active session (for this user or globally)
        try {
            $activeSession = $db->fetchOne(
                "SELECT id FROM pos_sessions WHERE tenant_id = ? AND status = 'open' AND user_id = ?",
                [$tenantId, Auth::user()['id']]
            );
        } catch (Exception $e) {
            // Fallback without user_id filter
            $activeSession = $db->fetchOne(
                "SELECT id FROM pos_sessions WHERE tenant_id = ? AND status = 'open'",
                [$tenantId]
            );
        }

        if ($activeSession) {
            $prefix = mc_base_path();
            header("Location: " . $prefix . "/" . Tenant::getCurrent()['subdomain'] . "/pos/pos");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $openingBalance = isset($_POST['opening_balance']) ? (float)$_POST['opening_balance'] : 0.0;
            $storeId = !empty($_POST['store_id']) ? (int)$_POST['store_id'] : null;
            $userId = Auth::user()['id'];

            // Auto-detect: POSTed store → Store::getCurrent() → user's assigned store → default
            if (!$storeId) {
                $storeId = $autoStoreId;
            }
            if (!$storeId && Auth::user()['current_store_id']) {
                $storeId = (int)Auth::user()['current_store_id'];
            }
            if (!$storeId) {
                $defaultStore = Store::getDefault($tenantId);
                $storeId = $defaultStore ? $defaultStore['id'] : null;
            }

            $sessionId = $db->insert('pos_sessions', [
                'tenant_id'       => $tenantId,
                'store_id'        => $storeId,
                'user_id'         => $userId,
                'opening_balance' => $openingBalance,
                'status'          => 'open',
                'opened_at'       => date('Y-m-d H:i:s')
            ]);

            // Auto-create GPS tracking session
            try {
                $db->insert('gps_tracking_sessions', [
                    'tenant_id'      => $tenantId,
                    'store_id'       => $storeId,
                    'user_id'        => $userId,
                    'pos_session_id' => $sessionId,
                    'status'         => 'active',
                    'device_info'    => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                    'started_at'     => date('Y-m-d H:i:s')
                ]);
            } catch (Exception $e) {
                // Table might not exist yet, fail silently
            }

            // Send Telegram notification for session open
            $this->sendTelegramSessionNotification($tenantId, $userId, 'open', $openingBalance);

            $_SESSION['success_msg'] = __('session_opened_success');
            $prefix = mc_base_path();
            header("Location: " . $prefix . "/" . Tenant::getCurrent()['subdomain'] . "/pos/pos");
            exit;
        }

        include __DIR__ . '/../views/session_open.php';
    }

    public function close() {
        TenantMiddleware::handle();
        AuthMiddleware::handle();

        if (!Tenant::hasModule('pos')) {
            die('POS system not subscribed for your plan');
        }

        if (!Auth::hasPermission('pos', 'write')) {
            die('No permission to close POS sessions');
        }

        $db = Database::getInstance();
        $tenantId = Tenant::getId();

        // Find active session
        $activeSession = $db->fetchOne(
            "SELECT * FROM pos_sessions WHERE tenant_id = ? AND status = 'open'", 
            [$tenantId]
        );

        if (!$activeSession) {
            $prefix = mc_base_path();
            header("Location: " . $prefix . "/" . Tenant::getCurrent()['subdomain'] . "/pos/sessions");
            exit;
        }

        // Calculate sales breakdown by payment method for this session
        $payments = $db->fetchAll(
            "SELECT p.method, COALESCE(SUM(p.amount), 0) as total_amount
             FROM payments p
             JOIN orders o ON p.order_id = o.id
             WHERE o.session_id = ? AND o.status = 'completed'
             GROUP BY p.method",
            [$activeSession['id']]
        );

        $paymentSummary = [];
        $totalSessionSales = 0.0;
        foreach ($payments as $p) {
            $paymentSummary[$p['method']] = (float)$p['total_amount'];
            $totalSessionSales += (float)$p['total_amount'];
        }

        $cashSales = $paymentSummary['cash'] ?? 0.0;
        $expectedCash = (float)$activeSession['opening_balance'] + $cashSales;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $closingBalance = isset($_POST['closing_balance']) ? (float)$_POST['closing_balance'] : 0.0;

            $db->update(
                'pos_sessions', 
                [
                    'closing_balance' => $closingBalance,
                    'total_sales' => $totalSessionSales,
                    'status' => 'closed',
                    'closed_at' => date('Y-m-d H:i:s')
                ], 
                'id = ? AND tenant_id = ?', 
                [$activeSession['id'], $tenantId]
            );

            // Stop GPS tracking session
            try {
                $db->update(
                    'gps_tracking_sessions',
                    ['status' => 'stopped', 'ended_at' => date('Y-m-d H:i:s')],
                    'pos_session_id = ? AND tenant_id = ? AND status = ?',
                    [$activeSession['id'], $tenantId, 'active']
                );
            } catch (Exception $e) {
                // Table might not exist yet, fail silently
            }

            // Send Telegram notification with sales report
            $this->sendTelegramSessionNotification($tenantId, Auth::user()['id'], 'close', null, $activeSession['id'], $totalSessionSales, $paymentSummary);

            $_SESSION['success_msg'] = __('session_closed_success');
            $prefix = mc_base_path();
            header("Location: " . $prefix . "/" . Tenant::getCurrent()['subdomain'] . "/pos/sessions");
            exit;
        }

        include __DIR__ . '/../views/session_close.php';
    }

    public function show($id) {
        TenantMiddleware::handle();
        AuthMiddleware::handle();

        if (!Tenant::hasModule('pos')) {
            die('POS system not subscribed for your plan');
        }

        if (!Auth::hasPermission('pos', 'read')) {
            die('No permission to view POS session details');
        }

        $db = Database::getInstance();
        $tenantId = Tenant::getId();

        $session = $db->fetchOne(
            "SELECT s.*, u.username FROM pos_sessions s
             JOIN users u ON s.user_id = u.id
             WHERE s.id = ? AND s.tenant_id = ?",
            [$id, $tenantId]
        );

        if (!$session) {
            die('Session not found');
        }

        // Get orders list
        $orders = $db->fetchAll(
            "SELECT o.*, c.name as customer_name FROM orders o
             LEFT JOIN customers c ON o.customer_id = c.id
             WHERE o.session_id = ? AND o.tenant_id = ?
             ORDER BY o.created_at DESC",
            [$id, $tenantId]
        );

        // Get payment breakdown (summary) — include currency for cash split
        // Load exchange rate from settings
        require_once __DIR__ . '/../../../core/classes/Settings.php';
        $settings = Settings::getAll($tenantId);
        $exchangeRate = (float)($settings['exchange_rate_usd_khr'] ?? 4100);
        
        $payments = $db->fetchAll(
            "SELECT p.method, p.currency, COALESCE(SUM(p.amount), 0) as total_amount
             FROM payments p
             JOIN orders o ON p.order_id = o.id
             WHERE o.session_id = ? AND o.status = 'completed'
             GROUP BY p.method, p.currency
             ORDER BY p.method, p.currency",
            [$id]
        );

        $paymentSummary = [];
        $cashKHR = 0.0;  // USD amount paid in KHR
        $cashUSD = 0.0;  // USD amount paid in USD
        foreach ($payments as $p) {
            $key = $p['method'];
            $amt = (float)$p['total_amount'];
            // For cash, split by currency (amount is always in USD)
            if ($p['method'] === 'cash' && $p['currency'] === 'KHR') {
                $cashKHR = $amt;
                $key = 'cash_khr';
                $paymentSummary[$key] = $amt; // Store USD amount
            } elseif ($p['method'] === 'cash') {
                $cashUSD = $amt;
                $paymentSummary[$key] = $amt;
            } else {
                $paymentSummary[$key] = $amt;
            }
        }
        
        // Pass raw cash totals (USD amounts) to view
        $cashKHRRaw = $cashKHR;
        $cashUSDRaw = $cashUSD;

        // Get items sold per payment method (for the detailed payment tab breakdown)
        $paymentMethods = array_keys($paymentSummary);
        $itemsByPayment = [];
        foreach ($paymentMethods as $method) {
            $itemsByPayment[$method] = $db->fetchAll(
                "SELECT prod.name, prod.sku, SUM(oi.quantity) as qty_sold, SUM(oi.total) as total_revenue
                 FROM order_items oi
                 JOIN orders o ON oi.order_id = o.id
                 JOIN payments pay ON pay.order_id = o.id
                 JOIN products prod ON oi.product_id = prod.id
                 WHERE o.session_id = ? AND o.status = 'completed' AND pay.method = ?
                 GROUP BY prod.id, prod.name, prod.sku
                 ORDER BY qty_sold DESC",
                [$id, $method]
            );
        }

        // Get sold products breakdown (Odoo POS style — all methods combined)
        $soldProducts = $db->fetchAll(
            "SELECT p.id, p.name, p.sku, SUM(oi.quantity) as qty_sold, SUM(oi.total) as total_revenue
             FROM order_items oi
             JOIN orders o ON oi.order_id = o.id
             JOIN products p ON oi.product_id = p.id
             WHERE o.session_id = ? AND o.status = 'completed'
             GROUP BY p.id, p.name, p.sku
             ORDER BY qty_sold DESC",
            [$id]
        );

        // Get payment info per order for display
        $orderPaymentsRaw = $db->fetchAll(
            "SELECT p.order_id, p.method, p.bank_name
             FROM payments p
             JOIN orders o ON p.order_id = o.id
             WHERE o.session_id = ? AND o.status = 'completed'",
            [$id]
        );
        $orderPayments = [];
        foreach ($orderPaymentsRaw as $op) {
            $orderPayments[$op['order_id']] = $op;
        }

        include __DIR__ . '/../views/session_detail.php';
    }

    /**
     * Send Telegram notification for session open/close with optional sales report
     */
    private function sendTelegramSessionNotification($tenantId, $userId, $action, $openingBalance = null, $sessionId = null, $totalSales = null, $paymentSummary = null) {
        try {
            $db = Database::getInstance();

            // Get tenant's Telegram config or fallback to system
            $tgConfig = $db->fetchOne(
                "SELECT * FROM tenant_telegram_config WHERE tenant_id = ? AND is_active = 1",
                [$tenantId]
            );

            $botToken = $tgConfig['bot_token'] ?? null;
            $chatId = $tgConfig['chat_id'] ?? null;

            if (!$botToken || !$chatId) {
                $sysConfig = require __DIR__ . '/../../../config/telegram.php';
                $botToken = $sysConfig['bot_token'] ?? null;
                $chatId = $sysConfig['chat_id'] ?? null;
            }

            if (!$botToken || !$chatId) return;

            $user = $db->fetchOne("SELECT username, email FROM users WHERE id = ?", [$userId]);
            $tenant = $db->fetchOne("SELECT name, subdomain FROM tenants WHERE id = ?", [$tenantId]);
            $storeName = '';
            if ($sessionId) {
                $store = $db->fetchOne(
                    "SELECT s.name FROM stores s JOIN pos_sessions ps ON ps.store_id = s.id WHERE ps.id = ?",
                    [$sessionId]
                );
                $storeName = $store ? $store['name'] : '';
            }

            if ($action === 'open') {
                $message = "🟢 <b>POS Session Opened</b>\n";
                $message .= "🏪 " . ($tenant['name'] ?? 'Store') . "\n";
                if ($storeName) $message .= "📍 " . $storeName . "\n";
                $message .= "👤 " . ($user['username'] ?? 'N/A') . "\n";
                $message .= "💰 Opening Balance: <b>$" . number_format((float)$openingBalance, 2) . "</b>\n";
                $message .= "🕐 " . date('Y-m-d H:i:s');
            } elseif ($action === 'close') {
                $message = "🔴 <b>POS Session Closed</b>\n";
                $message .= "🏪 " . ($tenant['name'] ?? 'Store') . "\n";
                if ($storeName) $message .= "📍 " . $storeName . "\n";
                $message .= "👤 " . ($user['username'] ?? 'N/A') . "\n";
                $message .= "━━━━━━━━━━━━━━━━\n";
                $message .= "📊 <b>Sales Report</b>\n";
                $message .= "💰 Total Sales: <b>$" . number_format((float)$totalSales, 2) . "</b>\n";

                if ($paymentSummary && is_array($paymentSummary)) {
                    foreach ($paymentSummary as $method => $amount) {
                        $icon = match(strtolower($method)) {
                            'cash' => '💵', 'khqr' => '📱', 'card' => '💳',
                            default => '💲'
                        };
                        $message .= $icon . " " . ucfirst($method) . ": $" . number_format((float)$amount, 2) . "\n";
                    }
                }
                $message .= "🕐 " . date('Y-m-d H:i:s');
            }

            $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
            $data = [
                'chat_id'    => $chatId,
                'text'       => $message,
                'parse_mode' => 'HTML'
            ];
            $ctx = stream_context_create([
                'http' => [
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'POST',
                    'content' => http_build_query($data),
                    'ignore_errors' => true
                ]
            ]);
            @file_get_contents($url, false, $ctx);
        } catch (Exception $e) {
            // Silent fail - don't break session flow
        }
    }
}
?>
