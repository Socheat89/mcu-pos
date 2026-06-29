<?php
// modules/pos/controllers/MenuController.php
require_once __DIR__ . '/../../../core/classes/Database.php';
require_once __DIR__ . '/../../../core/classes/Tenant.php';
require_once __DIR__ . '/../../../core/classes/Settings.php';
require_once __DIR__ . '/../../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../../middleware/TenantMiddleware.php';
require_once __DIR__ . '/../models/Product.php';
require_once dirname(__DIR__, 3) . '/core/helpers/url.php';

class MenuController {
    public function index() {
        // Publicly accessible - only require Tenant detection
        TenantMiddleware::handle();

        $tenantId = Tenant::getId();
        $products = Product::getAll($tenantId);
        $settings = Settings::getAll($tenantId);
        
        $tenant = Tenant::getCurrent();
        
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];

        // Group products by category
        $categories = [];
        foreach ($products as $product) {
            $catName = $product['category_name'] ?: 'General';
            if (!isset($categories[$catName])) {
                $categories[$catName] = [];
            }
            $categories[$catName][] = $product;
        }

        include __DIR__ . '/../views/digital_menu.php';
    }

    public function admin() {
        // Admin only
        TenantMiddleware::handle();
        AuthMiddleware::handle();

        if (!Auth::hasPermission('pos', 'read')) {
            die('No permission');
        }

        $tenant = Tenant::getCurrent();
        $tenantSlug = $tenant['subdomain'];
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        
        // Base project URL
        $menuUrl = $protocol . '://' . $host . mc_url($tenantSlug . '/pos/menu');

        include __DIR__ . '/../views/menu_admin.php';
    }

    public function place_order() {
        TenantMiddleware::handle();
        $tenantId = Tenant::getId();
        $tenant = Tenant::getCurrent();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }

        // Get raw POST data for JSON
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!$data || empty($data['items'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Your cart is empty']);
            exit;
        }

        $db = Database::getInstance();
        $db->getConnection()->beginTransaction();

        try {
            // Table number or customer name
            $note = isset($data['customer_name']) ? "Customer: " . $data['customer_name'] : "";
            if (isset($data['table_number'])) {
                $note .= ($note ? " | " : "") . "Table: " . $data['table_number'];
            }

            // Create order as pending (held)
            $orderData = [
                'tenant_id' => $tenantId,
                'customer_id' => null,
                'total' => 0,
                'status' => 'pending', // Important: customers place pending orders
                'notes' => $note
            ];

            // Check if notes column exists, if not, skip or handle
            // For now assume standard orders table
            $orderId = $db->insert('orders', [
                'tenant_id' => $orderData['tenant_id'],
                'customer_id' => $orderData['customer_id'],
                'total' => $orderData['total'],
                'status' => $orderData['status'],
                'notes' => $orderData['notes']
            ]);

            $total = 0;
            foreach ($data['items'] as $item) {
                $product = $db->fetchOne(
                    "SELECT * FROM products WHERE id = ? AND tenant_id = ?",
                    [$item['id'], $tenantId]
                );

                if (!$product || $product['status'] !== 'active') continue;

                $quantity = (int)$item['quantity'];
                if ($quantity <= 0) continue;

                $unitPrice = (float)$product['price'];
                $itemTotal = $quantity * $unitPrice;

                $db->insert('order_items', [
                    'order_id' => $orderId,
                    'product_id' => $product['id'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total' => $itemTotal
                ]);

                $total += $itemTotal;
            }

            // Update final total
            $db->update('orders', ['total' => $total], 'id = ? AND tenant_id = ?', [$orderId, $tenantId]);

            $db->getConnection()->commit();

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Order placed successfully!', 'order_id' => $orderId]);
            exit;

        } catch (Exception $e) {
            $db->getConnection()->rollBack();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Order failed: ' . $e->getMessage()]);
            exit;
        }
    }
}
