<?php
// modules/pos/controllers/SessionController.php
require_once __DIR__ . '/../../../core/classes/Database.php';
require_once __DIR__ . '/../../../core/classes/Tenant.php';
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

        $sessions = $db->fetchAll(
            "SELECT s.*, u.username FROM pos_sessions s 
             JOIN users u ON s.user_id = u.id 
             WHERE s.tenant_id = ? 
             ORDER BY s.opened_at DESC", 
            [$tenantId]
        );

        $activeSession = $db->fetchOne(
            "SELECT * FROM pos_sessions WHERE tenant_id = ? AND status = 'open'", 
            [$tenantId]
        );

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

        // Check if there is already an active session
        $activeSession = $db->fetchOne(
            "SELECT id FROM pos_sessions WHERE tenant_id = ? AND status = 'open'", 
            [$tenantId]
        );

        if ($activeSession) {
            $prefix = mc_base_path();
            header("Location: " . $prefix . "/" . Tenant::getCurrent()['subdomain'] . "/pos/pos");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $openingBalance = isset($_POST['opening_balance']) ? (float)$_POST['opening_balance'] : 0.0;
            $userId = Auth::user()['id'];

            $db->insert('pos_sessions', [
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'opening_balance' => $openingBalance,
                'status' => 'open',
                'opened_at' => date('Y-m-d H:i:s')
            ]);

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

        // Get payment breakdown
        $payments = $db->fetchAll(
            "SELECT p.method, COALESCE(SUM(p.amount), 0) as total_amount
             FROM payments p
             JOIN orders o ON p.order_id = o.id
             WHERE o.session_id = ? AND o.status = 'completed'
             GROUP BY p.method",
            [$id]
        );

        $paymentSummary = [];
        foreach ($payments as $p) {
            $paymentSummary[$p['method']] = (float)$p['total_amount'];
        }

        // Get sold products breakdown (Odoo POS style)
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

        include __DIR__ . '/../views/session_detail.php';
    }
}
?>
