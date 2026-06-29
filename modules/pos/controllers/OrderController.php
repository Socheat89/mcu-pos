<?php
// modules/pos/controllers/OrderController.php
require_once __DIR__ . '/../../../core/classes/Database.php';
require_once __DIR__ . '/../../../core/classes/Tenant.php';
require_once __DIR__ . '/../../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../../middleware/TenantMiddleware.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Product.php';
require_once dirname(__DIR__, 3) . '/core/helpers/url.php';

class OrderController {
    public function create() {
        TenantMiddleware::handle();
        AuthMiddleware::handle();

        if (!Tenant::hasModule('pos')) {
            die('POS system not subscribed for your plan');
        }

        if (!Auth::hasPermission('pos', 'write')) {
            die('No permission to create orders');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processOrder();
        } else {
            $this->showForm();
        }
    }

    public function index() {
        TenantMiddleware::handle();
        AuthMiddleware::handle();

        if (!Tenant::hasModule('pos')) {
            die('POS system not subscribed for your plan');
        }

        if (!Auth::hasPermission('pos', 'read')) {
            die('No permission to view orders');
        }

        $orders = Order::getAll(null, 50);
        include __DIR__ . '/../views/orders.php';
    }

    public function holds() {
        TenantMiddleware::handle();
        AuthMiddleware::handle();

        if (!Tenant::hasModule('pos')) {
            die('POS system not subscribed for your plan');
        }

        if (!Auth::hasPermission('pos', 'read')) {
            die('No permission to view held orders');
        }

        $heldOrders = Order::getPending(null, 200);
        include __DIR__ . '/../views/holds.php';
    }

    public function show($id) {
        TenantMiddleware::handle();
        AuthMiddleware::handle();

        if (!Auth::hasPermission('pos', 'read')) {
            die('No permission to view order detail');
        }

        $order = Order::getById($id);
        if (!$order) {
            die('Order not found');
        }

        include __DIR__ . '/../views/order_detail.php';
    }

    public function receipt($id) {
        TenantMiddleware::handle();
        AuthMiddleware::handle();

        $order = Order::getById($id);
        if (!$order) {
            die('Order not found');
        }

        include __DIR__ . '/../views/receipt.php';
    }

    public function complete($id) {
        TenantMiddleware::handle();
        AuthMiddleware::handle();

        if (!Auth::hasPermission('pos', 'write')) {
            die('No permission to complete orders');
        }

        $db = Database::getInstance();
        $tenantId = Tenant::getId();

        // Update order status to completed
        $result = $db->update('orders', ['status' => 'completed'], 'id = ? AND tenant_id = ? AND status = ?', [$id, $tenantId, 'pending']);

        if ($result) {
            $prefix = mc_base_path();
            header("Location: " . $prefix . "/" . Tenant::getCurrent()['subdomain'] . "/pos/orders");
            exit;
        } else {
            die('Order not found or already completed');
        }
    }

    private function processOrder() {
        $db = Database::getInstance();
        $tenantId = Tenant::getId();

        // Start transaction
        $db->getConnection()->beginTransaction();

        try {
            // Create or update order
            $status = $_POST['order_status'] ?? 'completed';
            $resumeOrderId = isset($_POST['resume_order_id']) ? (int)$_POST['resume_order_id'] : 0;

            $customerId = $_POST['customer_id'] ?? null;
            if ($customerId === '' || $customerId === null) {
                $customerId = null;
            } else {
                $customerId = (int)$customerId;
                if ($customerId <= 0) {
                    $customerId = null;
                } else {
                    $customer = $db->fetchOne(
                        "SELECT id FROM customers WHERE id = ? AND tenant_id = ?",
                        [$customerId, $tenantId]
                    );
                    if (!$customer) {
                        throw new Exception('Invalid customer selected');
                    }
                }
            }

            if (!isset($_POST['items']) || !is_array($_POST['items']) || count($_POST['items']) === 0) {
                throw new Exception('No items in order');
            }

            if ($resumeOrderId > 0) {
                $existing = $db->fetchOne(
                    "SELECT id, status FROM orders WHERE id = ? AND tenant_id = ?",
                    [$resumeOrderId, $tenantId]
                );
                if (!$existing) {
                    throw new Exception('Held order not found');
                }
                if (($existing['status'] ?? '') !== 'pending') {
                    throw new Exception('Only pending held orders can be resumed');
                }

                // Replace items and update order header
                $db->delete('order_items', 'order_id = ?', [$resumeOrderId]);

                $total = 0;
                foreach ($_POST['items'] as $item) {
                    $product = $db->fetchOne(
                        "SELECT * FROM products WHERE id = ? AND tenant_id = ?",
                        [$item['product_id'], $tenantId]
                    );

                    if (!$product) continue;

                    $quantity = (int)$item['quantity'];
                    if ($quantity <= 0) continue;

                    if ($status === 'completed') {
                        $stock = (int)($product['stock_quantity'] ?? 0);
                        if ($quantity > $stock && Tenant::getPosLevel() >= 2) {
                            throw new Exception('Insufficient stock for: ' . ($product['name'] ?? 'product') . '. Upgrade to Standard ($50) for inventory management.');
                        }
                    }

                    $unitPrice = (float)$product['price'];
                    $itemTotal = $quantity * $unitPrice;

                    $db->insert('order_items', [
                        'order_id' => $resumeOrderId,
                        'product_id' => $item['product_id'],
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total' => $itemTotal
                    ]);

                    $total += $itemTotal;

                    if ($status === 'completed') {
                        $newStock = (int)$product['stock_quantity'] - $quantity;
                        $db->update('products', ['stock_quantity' => $newStock], 'id = ? AND tenant_id = ?', [$item['product_id'], $tenantId]);
                        $db->insert('stock_logs', [
                            'tenant_id' => $tenantId,
                            'product_id' => $item['product_id'],
                            'change_quantity' => -$quantity,
                            'reason' => 'sale',
                            'order_id' => $resumeOrderId
                        ]);
                    }
                }

                $db->update('orders', [
                    'customer_id' => $customerId,
                    'total' => $total,
                    'status' => $status
                ], 'id = ? AND tenant_id = ?', [$resumeOrderId, $tenantId]);

                // Keep payments clean
                $db->delete('payments', 'order_id = ?', [$resumeOrderId]);

                if ($status === 'completed') {
                    $paymentMethod = $_POST['payment_method'] ?? 'cash';
                    $db->insert('payments', [
                        'order_id' => $resumeOrderId,
                        'amount' => $total,
                        'method' => $paymentMethod,
                        'status' => 'completed'
                    ]);
                }

                $db->getConnection()->commit();

                $prefix = mc_base_path();

                if ($status === 'completed') {
                    header("Location: " . $prefix . "/" . Tenant::getCurrent()['subdomain'] . "/pos/orders/{$resumeOrderId}/receipt?autoprint=1");
                } else {
                    header("Location: " . $prefix . "/" . Tenant::getCurrent()['subdomain'] . "/pos/holds");
                }
                exit;
            }

            $orderData = [
                'tenant_id' => $tenantId,
                'customer_id' => $customerId,
                'total' => 0, // Calculate later
                'status' => $status
            ];

            $orderId = $db->insert('orders', $orderData);
            $total = 0;

            // Add order items
            foreach ($_POST['items'] as $item) {
                $product = $db->fetchOne(
                    "SELECT * FROM products WHERE id = ? AND tenant_id = ?",
                    [$item['product_id'], $tenantId]
                );

                if (!$product) continue;

                $quantity = (int)$item['quantity'];
                if ($quantity <= 0) continue;

                if ($status === 'completed') {
                    $stock = (int)($product['stock_quantity'] ?? 0);
                    if ($quantity > $stock && Tenant::getPosLevel() >= 2) {
                        throw new Exception('Insufficient stock for: ' . ($product['name'] ?? 'product') . '. Upgrade to Standard ($50) for inventory management.');
                    }
                }
                $unitPrice = (float)$product['price'];
                $itemTotal = $quantity * $unitPrice;

                $orderItemData = [
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total' => $itemTotal
                ];

                $db->insert('order_items', $orderItemData);

                $total += $itemTotal;

                if ($status === 'completed') {
                    // Update stock
                    $newStock = $product['stock_quantity'] - $quantity;
                    $db->update('products', ['stock_quantity' => $newStock], 'id = ? AND tenant_id = ?', [$item['product_id'], $tenantId]);

                    // Log stock change
                    $db->insert('stock_logs', [
                        'tenant_id' => $tenantId,
                        'product_id' => $item['product_id'],
                        'change_quantity' => -$quantity,
                        'reason' => 'sale',
                        'order_id' => $orderId
                    ]);
                }
            }

            // Update order total
            $db->update('orders', ['total' => $total], 'id = ? AND tenant_id = ?', [$orderId, $tenantId]);

            if ($status === 'completed') {
                // Process payment
                $paymentMethod = $_POST['payment_method'] ?? 'cash';
                $paymentData = [
                    'order_id' => $orderId,
                    'amount' => $total,
                    'method' => $paymentMethod,
                    'status' => 'completed'
                ];

                $db->insert('payments', $paymentData);
            }

            $db->getConnection()->commit();

            $prefix = mc_base_path();

            // Redirect to receipt if completed, else to orders
            if ($status === 'completed') {
                header("Location: " . $prefix . "/" . Tenant::getCurrent()['subdomain'] . "/pos/orders/{$orderId}/receipt?autoprint=1");
            } else {
                header("Location: " . $prefix . "/" . Tenant::getCurrent()['subdomain'] . "/pos/holds");
            }
            exit;

        } catch (Exception $e) {
            $db->getConnection()->rollBack();
            die('Order creation failed: ' . $e->getMessage());
        }
    }

    private function showForm() {
        // Load products for the form
        $products = Product::getAll();
        $customers = $this->getCustomers();

        include __DIR__ . '/../views/order_form.php';
    }

    private function getCustomers() {
        $db = Database::getInstance();
        $tenantId = Tenant::getId();
        return $db->fetchAll("SELECT * FROM customers WHERE tenant_id = ? ORDER BY name", [$tenantId]);
    }
}
?>