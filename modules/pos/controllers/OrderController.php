<?php
// modules/pos/controllers/OrderController.php
require_once __DIR__ . '/../../../core/classes/Database.php';
require_once __DIR__ . '/../../../core/classes/Tenant.php';
require_once __DIR__ . '/../../../core/classes/Settings.php';
require_once __DIR__ . '/../../../core/classes/Store.php';
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

        $activeSession = $db->fetchOne("SELECT id FROM pos_sessions WHERE tenant_id = ? AND status = 'open'", [$tenantId]);
        if (!$activeSession) {
            die('No active POS session. Please open a session first.');
        }

        // Update order status to completed and set session_id
        $result = $db->update('orders', ['status' => 'completed', 'session_id' => $activeSession['id']], 'id = ? AND tenant_id = ? AND status = ?', [$id, $tenantId, 'pending']);

        if ($result) {
            $prefix = mc_base_path();
            header("Location: " . $prefix . "/" . Tenant::getCurrent()['subdomain'] . "/pos/orders");
            exit;
        } else {
            die('Order not found or already completed');
        }
    }

    /**
     * Check if store_stock table exists (cached per request)
     */
    private static $hasStoreStock = null;
    private function hasStoreStockTable(Database $db): bool {
        if (self::$hasStoreStock !== null) return self::$hasStoreStock;
        try {
            $db->fetchAll("SELECT 1 FROM store_stock LIMIT 1");
            self::$hasStoreStock = true;
        } catch (\Throwable $e) {
            self::$hasStoreStock = false;
        }
        return self::$hasStoreStock;
    }

    /**
     * Check if ingredient_store_stock table exists (cached per request)
     */
    private static $hasIngStoreStock = null;
    private function hasIngStoreStockTable(Database $db): bool {
        if (self::$hasIngStoreStock !== null) return self::$hasIngStoreStock;
        try {
            $db->fetchAll("SELECT 1 FROM ingredient_store_stock LIMIT 1");
            self::$hasIngStoreStock = true;
        } catch (\Throwable $e) {
            self::$hasIngStoreStock = false;
        }
        return self::$hasIngStoreStock;
    }

    /**
     * Deduct ingredient from global stock AND per-store stock if available.
     */
    private function deductIngredient(
        Database $db,
        int $ingredientId,
        float $qty,
        string $reason,
        int $orderId,
        int $tenantId,
        ?int $storeId
    ): void {
        require_once __DIR__ . '/../models/Ingredient.php';

        // Global deduction (existing logic)
        Ingredient::deductStock($ingredientId, $qty, $reason, $orderId, $tenantId);

        // Per-store deduction if table exists
        if ($storeId && $this->hasIngStoreStockTable($db)) {
            try {
                $db->query(
                    "INSERT INTO ingredient_store_stock (tenant_id, store_id, ingredient_id, quantity)
                     VALUES (?, ?, ?, GREATEST(0, ? - ?))
                     ON DUPLICATE KEY UPDATE quantity = GREATEST(0, quantity - ?)",
                    [$tenantId, $storeId, $ingredientId,
                     $this->getIngStoreQty($db, $storeId, $ingredientId, $tenantId), $qty, $qty]
                );
                // Also log with store context
                $db->query(
                    "INSERT INTO ingredient_stock_logs (tenant_id, store_id, ingredient_id, change_quantity, reason, order_id)
                     VALUES (?, ?, ?, ?, ?, ?)",
                    [$tenantId, $storeId, $ingredientId, -$qty, $reason, $orderId]
                );
            } catch (\Throwable $e) {
                error_log('Ingredient store stock log error: ' . $e->getMessage());
            }
        }
    }

    private function getIngStoreQty(Database $db, int $storeId, int $ingredientId, int $tenantId): float {
        $row = $db->fetchOne(
            "SELECT quantity FROM ingredient_store_stock WHERE store_id = ? AND ingredient_id = ? AND tenant_id = ?",
            [$storeId, $ingredientId, $tenantId]
        );
        return $row ? (float)$row['quantity'] : 0.0;
    }

    /**
     * Deduct stock from the active store's store_stock (and globally from products).
     * Falls back to global-only deduction if store_stock table doesn't exist.
     */
    private function deductStock(
        Database $db,
        int $tenantId,
        int $productId,
        int $quantity,
        ?int $storeId,
        int $orderId,
        array $product
    ): void {
        if ($this->hasStoreStockTable($db) && $storeId) {
            // ── Per-store deduction ──────────────────────────────────────────
            $storeRow = $db->fetchOne(
                "SELECT quantity FROM store_stock WHERE store_id = ? AND product_id = ? AND tenant_id = ?",
                [$storeId, $productId, $tenantId]
            );
            $currentStoreQty = $storeRow ? (int)$storeRow['quantity'] : 0;
            $newStoreQty     = max(0, $currentStoreQty - $quantity);

            $db->query(
                "INSERT INTO store_stock (tenant_id, store_id, product_id, quantity)
                 VALUES (?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE quantity = GREATEST(0, quantity - ?)",
                [$tenantId, $storeId, $productId, $newStoreQty, $quantity]
            );

            // Also keep global stock_quantity in sync (sum of all stores not easily maintained,
            // so we deduct from global too to keep it as a reference)
            $newGlobal = max(0, (int)($product['stock_quantity'] ?? 0) - $quantity);
            $db->update('products', ['stock_quantity' => $newGlobal], 'id = ? AND tenant_id = ?', [$productId, $tenantId]);

            // Log with store context
            try {
                $db->query(
                    "INSERT INTO stock_logs (tenant_id, store_id, product_id, change_quantity, reason, order_id, created_at)
                     VALUES (?, ?, ?, ?, 'sale', ?, NOW())",
                    [$tenantId, $storeId, $productId, -$quantity, $orderId]
                );
            } catch (\Throwable $e) {
                // Fallback log without store_id
                try {
                    $db->insert('stock_logs', [
                        'tenant_id'       => $tenantId,
                        'product_id'      => $productId,
                        'change_quantity' => -$quantity,
                        'reason'          => 'sale',
                        'order_id'        => $orderId,
                    ]);
                } catch (\Throwable $e2) { /* ignore */ }
            }
        } else {
            // ── Fallback: global-only deduction ─────────────────────────────
            $newStock = (int)($product['stock_quantity'] ?? 0) - $quantity;
            $db->update('products', ['stock_quantity' => $newStock], 'id = ? AND tenant_id = ?', [$productId, $tenantId]);
            try {
                $db->insert('stock_logs', [
                    'tenant_id'       => $tenantId,
                    'product_id'      => $productId,
                    'change_quantity' => -$quantity,
                    'reason'          => 'sale',
                    'order_id'        => $orderId,
                ]);
            } catch (\Throwable $e) { /* ignore */ }
        }
    }

    private function processOrder() {
        $db = Database::getInstance();
        $tenantId = Tenant::getId();

        // Enforce active session check
        $activeSession = $db->fetchOne("SELECT id FROM pos_sessions WHERE tenant_id = ? AND status = 'open'", [$tenantId]);
        if (!$activeSession) {
            die('Order creation failed: No active POS session. Please open a session first.');
        }

        // Get current store for per-store stock deduction
        $currentStore   = Store::getCurrent($tenantId);
        $currentStoreId = $currentStore ? (int)$currentStore['id'] : null;

        // Detect business type (coffee = ingredient-based; mart = product stock-based)
        require_once __DIR__ . '/../../../core/classes/Settings.php';
        $businessType = Settings::get('business_type', $tenantId, 'coffee');

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

            // ── Resume (Held) Order ──────────────────────────────────────────
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

                    $sizeName = !empty($item['size_name']) ? trim($item['size_name']) : null;
                    $sizeId   = !empty($item['size_id']) ? (int)$item['size_id'] : null;

                    if ($status === 'completed') {
                        if ($businessType === 'coffee') {
                            // ── Coffee Mode: Check Recipe & Ingredient Stock ──────────
                            $recipe = [];
                            if ($sizeId) {
                                $recipe = $db->fetchAll(
                                    "SELECT pr.*, i.name as ingredient_name, i.unit
                                     FROM product_recipes pr
                                     JOIN ingredients i ON pr.ingredient_id = i.id
                                     WHERE pr.product_id = ? AND pr.product_size_id = ? AND pr.tenant_id = ?",
                                    [$item['product_id'], $sizeId, $tenantId]
                                );
                            }
                            if (empty($recipe)) {
                                $recipe = $db->fetchAll(
                                    "SELECT pr.*, i.name as ingredient_name, i.unit
                                     FROM product_recipes pr
                                     JOIN ingredients i ON pr.ingredient_id = i.id
                                     WHERE pr.product_id = ? AND pr.product_size_id IS NULL AND pr.tenant_id = ?",
                                    [$item['product_id'], $tenantId]
                                );
                            }

                            if (empty($recipe)) {
                                throw new Exception('ផលិតផល "' . ($product['name'] ?? 'product') . '" មិនទាន់មាន Recipe/គ្រឿងផ្សំ ទេ មិនអាចលក់បានឡើយ (Please set up a recipe for this product).');
                            }

                            // Check availability of each ingredient in current store
                            foreach ($recipe as $r) {
                                $neededQty = (float)$r['quantity'] * $quantity;
                                $availableQty = $this->getIngStoreQty($db, (int)$currentStoreId, (int)$r['ingredient_id'], $tenantId);
                                if ($neededQty > $availableQty) {
                                    $unitStr = !empty($r['unit']) ? ' ' . $r['unit'] : '';
                                    throw new Exception('គ្រឿងផ្សំមិនគ្រាន់គ្រាន់សម្រាប់លក់ឡើយ! / Insufficient ingredient "' . $r['ingredient_name'] . '" for ' . ($product['name'] ?? '') . ' (Required: ' . $neededQty . $unitStr . ', Available in store: ' . $availableQty . $unitStr . ').');
                                }
                            }
                        } else {
                            // ── Mart Mode: Check Product Stock ────────────────────────
                            if ($this->hasStoreStockTable($db) && $currentStoreId) {
                                $storeRow = $db->fetchOne(
                                    "SELECT quantity FROM store_stock WHERE store_id = ? AND product_id = ?",
                                    [$currentStoreId, $item['product_id']]
                                );
                                $availableStock = $storeRow ? (int)$storeRow['quantity'] : 0;
                            } else {
                                $availableStock = (int)($product['stock_quantity'] ?? 0);
                            }
                            if ($quantity > $availableStock && Tenant::getPosLevel() >= 2) {
                                throw new Exception('Insufficient stock for: ' . ($product['name'] ?? 'product') . " (available: {$availableStock}).");
                            }
                        }
                    }

                    $unitPrice = isset($item['unit_price']) ? (float)$item['unit_price'] : (float)$product['price'];
                    $itemTotal = $quantity * $unitPrice;

                    $db->insert('order_items', [
                        'order_id'        => $resumeOrderId,
                        'product_id'      => $item['product_id'],
                        'size_name'       => $sizeName,
                        'product_size_id' => $sizeId,
                        'quantity'        => $quantity,
                        'unit_price'      => $unitPrice,
                        'total'           => $itemTotal
                    ]);

                    $total += $itemTotal;

                    if ($status === 'completed') {
                        // Check recipe
                        $recipe = [];
                        if ($sizeId) {
                            $recipe = $db->fetchAll(
                                "SELECT * FROM product_recipes WHERE product_id = ? AND product_size_id = ? AND tenant_id = ?",
                                [$item['product_id'], $sizeId, $tenantId]
                            );
                        }
                        if (empty($recipe)) {
                            $recipe = $db->fetchAll(
                                "SELECT * FROM product_recipes WHERE product_id = ? AND product_size_id IS NULL AND tenant_id = ?",
                                [$item['product_id'], $tenantId]
                            );
                        }

                        if (!empty($recipe)) {
                            // Coffee mode: deduct ingredients (per-store + global)
                            foreach ($recipe as $r) {
                                $deductQty = (float)$r['quantity'] * $quantity;
                                $this->deductIngredient($db, (int)$r['ingredient_id'], $deductQty, 'sale', $resumeOrderId, $tenantId, $currentStoreId);
                            }
                        } elseif ($businessType !== 'coffee') {
                            // Mart mode only: deduct product stock when no recipe
                            $this->deductStock($db, $tenantId, (int)$item['product_id'], $quantity, $currentStoreId, $resumeOrderId, $product);
                        }
                        // Coffee mode with no recipe = skip (no stock deduction)
                    }
                }

                $db->update('orders', [
                    'customer_id' => $customerId,
                    'total'       => $total,
                    'status'      => $status,
                    'session_id'  => $activeSession['id']
                ], 'id = ? AND tenant_id = ?', [$resumeOrderId, $tenantId]);

                // Keep payments clean
                $db->delete('payments', 'order_id = ?', [$resumeOrderId]);

                if ($status === 'completed') {
                    $paymentMethod = $_POST['payment_method'] ?? 'cash';
                    $bankName      = $_POST['bank_name'] ?? null;
                    $currency      = $_POST['currency'] ?? 'USD';
                    $db->insert('payments', [
                        'order_id'  => $resumeOrderId,
                        'amount'    => $total,
                        'currency'  => $currency,
                        'method'    => $paymentMethod,
                        'bank_name' => $bankName,
                        'status'    => 'completed'
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

            // ── New Order ────────────────────────────────────────────────────
            $orderData = [
                'tenant_id'   => $tenantId,
                'customer_id' => $customerId,
                'total'       => 0,
                'status'      => $status,
                'session_id'  => $activeSession['id']
            ];

            // Attach store_id to order if available
            if ($currentStoreId) {
                $orderData['store_id'] = $currentStoreId;
            }

            $orderId = $db->insert('orders', $orderData);
            $total   = 0;

            // Add order items
            foreach ($_POST['items'] as $item) {
                $product = $db->fetchOne(
                    "SELECT * FROM products WHERE id = ? AND tenant_id = ?",
                    [$item['product_id'], $tenantId]
                );

                if (!$product) continue;

                $quantity = (int)$item['quantity'];
                if ($quantity <= 0) continue;

                $sizeName = !empty($item['size_name']) ? trim($item['size_name']) : null;
                $sizeId   = !empty($item['size_id']) ? (int)$item['size_id'] : null;

                if ($status === 'completed') {
                    if ($businessType === 'coffee') {
                        // ── Coffee Mode: Check Recipe & Ingredient Stock ──────────
                        $recipe = [];
                        if ($sizeId) {
                            $recipe = $db->fetchAll(
                                "SELECT pr.*, i.name as ingredient_name, i.unit
                                 FROM product_recipes pr
                                 JOIN ingredients i ON pr.ingredient_id = i.id
                                 WHERE pr.product_id = ? AND pr.product_size_id = ? AND pr.tenant_id = ?",
                                [$item['product_id'], $sizeId, $tenantId]
                            );
                        }
                        if (empty($recipe)) {
                            $recipe = $db->fetchAll(
                                "SELECT pr.*, i.name as ingredient_name, i.unit
                                 FROM product_recipes pr
                                 JOIN ingredients i ON pr.ingredient_id = i.id
                                 WHERE pr.product_id = ? AND pr.product_size_id IS NULL AND pr.tenant_id = ?",
                                [$item['product_id'], $tenantId]
                            );
                        }

                        if (empty($recipe)) {
                            throw new Exception('ផលិតផល "' . ($product['name'] ?? 'product') . '" មិនទាន់មាន Recipe/គ្រឿងផ្សំ ទេ មិនអាចលក់បានឡើយ (Please set up a recipe for this product).');
                        }

                        // Check availability of each ingredient in current store
                        foreach ($recipe as $r) {
                            $neededQty = (float)$r['quantity'] * $quantity;
                            $availableQty = $this->getIngStoreQty($db, (int)$currentStoreId, (int)$r['ingredient_id'], $tenantId);
                            if ($neededQty > $availableQty) {
                                $unitStr = !empty($r['unit']) ? ' ' . $r['unit'] : '';
                                throw new Exception('គ្រឿងផ្សំមិនគ្រាន់គ្រាន់សម្រាប់លក់ឡើយ! / Insufficient ingredient "' . $r['ingredient_name'] . '" for ' . ($product['name'] ?? '') . ' (Required: ' . $neededQty . $unitStr . ', Available in store: ' . $availableQty . $unitStr . ').');
                            }
                        }
                    } else {
                        // ── Mart Mode: Check Product Stock ────────────────────────
                        if ($this->hasStoreStockTable($db) && $currentStoreId) {
                            $storeRow = $db->fetchOne(
                                "SELECT quantity FROM store_stock WHERE store_id = ? AND product_id = ?",
                                [$currentStoreId, $item['product_id']]
                            );
                            $availableStock = $storeRow ? (int)$storeRow['quantity'] : 0;
                        } else {
                            $availableStock = (int)($product['stock_quantity'] ?? 0);
                        }
                        if ($quantity > $availableStock && Tenant::getPosLevel() >= 2) {
                            throw new Exception('Insufficient stock for: ' . ($product['name'] ?? 'product') . " (available: {$availableStock}).");
                        }
                    }
                }

                $unitPrice = isset($item['unit_price']) ? (float)$item['unit_price'] : (float)$product['price'];
                $itemTotal = $quantity * $unitPrice;

                $orderItemData = [
                    'order_id'        => $orderId,
                    'product_id'      => $item['product_id'],
                    'size_name'       => $sizeName,
                    'product_size_id' => $sizeId,
                    'quantity'        => $quantity,
                    'unit_price'      => $unitPrice,
                    'total'           => $itemTotal
                ];

                $db->insert('order_items', $orderItemData);
                $total += $itemTotal;

                if ($status === 'completed') {
                    // Check recipe
                    $recipe = [];
                    if ($sizeId) {
                        $recipe = $db->fetchAll(
                            "SELECT * FROM product_recipes WHERE product_id = ? AND product_size_id = ? AND tenant_id = ?",
                            [$item['product_id'], $sizeId, $tenantId]
                        );
                    }
                    if (empty($recipe)) {
                        $recipe = $db->fetchAll(
                            "SELECT * FROM product_recipes WHERE product_id = ? AND product_size_id IS NULL AND tenant_id = ?",
                            [$item['product_id'], $tenantId]
                        );
                    }

                    if (!empty($recipe)) {
                        // Coffee mode: deduct ingredients (per-store + global)
                        foreach ($recipe as $r) {
                            $deductQty = (float)$r['quantity'] * $quantity;
                            $this->deductIngredient($db, (int)$r['ingredient_id'], $deductQty, 'sale', $orderId, $tenantId, $currentStoreId);
                        }
                    } elseif ($businessType !== 'coffee') {
                        // Mart mode only: deduct product stock when no recipe
                        $this->deductStock($db, $tenantId, (int)$item['product_id'], $quantity, $currentStoreId, $orderId, $product);
                    }
                    // Coffee mode with no recipe = skip (no stock deduction)
                }
            }

            // Update order total
            $db->update('orders', ['total' => $total], 'id = ? AND tenant_id = ?', [$orderId, $tenantId]);

            if ($status === 'completed') {
                $paymentMethod = $_POST['payment_method'] ?? 'cash';
                $bankName      = $_POST['bank_name'] ?? null;
                $currency      = $_POST['currency'] ?? 'USD';
                $paymentData   = [
                    'order_id'  => $orderId,
                    'amount'    => $total,
                    'currency'  => $currency,
                    'method'    => $paymentMethod,
                    'bank_name' => $bankName,
                    'status'    => 'completed'
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
            error_log('Order creation failed for tenant ' . $tenantId . ': ' . $e->getMessage());
            die('<div style="font-family: sans-serif; padding: 30px; max-width: 600px; margin: 50px auto; border-radius: 16px; background: #fff5f5; border: 2px solid #feb2b2; color: #9b2c2c;">
                <h3 style="margin-top:0;">⚠️ មិនអាចបង្កើត Order បានឡើយ / Order Creation Blocked</h3>
                <p style="font-size: 16px; font-weight: bold; line-height: 1.5;">' . htmlspecialchars($e->getMessage()) . '</p>
                <a href="javascript:history.back()" style="display: inline-block; margin-top: 15px; padding: 10px 20px; background: #e53e3e; color: white; border-radius: 8px; text-decoration: none; font-weight: bold;">← ត្រឡប់ក្រោយ / Go Back</a>
            </div>');
        }
    }

    private function showForm() {
        $db = Database::getInstance();
        $tenantId = Tenant::getId();

        require_once __DIR__ . '/../../../core/classes/Store.php';
        $businessType = Settings::get('business_type', 'coffee');
        $currentStore = Store::getCurrent($tenantId);
        $currentStoreId = $currentStore ? (int)$currentStore['id'] : null;

        $products  = Product::getAll();

        $getIngQty = function(int $storeId, int $ingId) use ($db, $tenantId): float {
            $r = $db->fetchOne("SELECT quantity FROM ingredient_store_stock WHERE store_id = ? AND ingredient_id = ? AND tenant_id = ?", [$storeId, $ingId, $tenantId]);
            return $r ? (float)$r['quantity'] : 0.0;
        };

        foreach ($products as &$p) {
            if ($businessType === 'coffee') {
                $recipes = $db->fetchAll(
                    "SELECT pr.*, i.name as ingredient_name, i.unit
                     FROM product_recipes pr
                     JOIN ingredients i ON pr.ingredient_id = i.id
                     WHERE pr.product_id = ? AND pr.tenant_id = ?",
                    [$p['id'], $tenantId]
                );

                if (empty($recipes)) {
                    $p['can_sell'] = false;
                    $p['stock_quantity'] = 0;
                    $p['stock_error'] = 'ផលិតផល "' . $p['name'] . '" មិនទាន់មាន Recipe/គ្រឿងផ្សំ ទេ មិនអាចលក់បានឡើយ';
                } else {
                    $canSell = true;
                    $err = '';
                    foreach ($recipes as $r) {
                        $needed = (float)$r['quantity'];
                        $avail  = $currentStoreId ? $getIngQty($currentStoreId, (int)$r['ingredient_id']) : 0.0;
                        if ($needed > $avail) {
                            $canSell = false;
                            $unitStr = !empty($r['unit']) ? ' ' . $r['unit'] : '';
                            $err = 'ខ្វះគ្រឿងផ្សំ "' . $r['ingredient_name'] . '" ក្នុង Store (ត្រូវការ ' . $needed . $unitStr . ' ប៉ុន្តែមាន ' . $avail . $unitStr . ')';
                            break;
                        }
                    }
                    if ($canSell) {
                        $p['can_sell'] = true;
                        $p['stock_quantity'] = 9999;
                        $p['stock_error'] = '';
                    } else {
                        $p['can_sell'] = false;
                        $p['stock_quantity'] = 0;
                        $p['stock_error'] = $err;
                    }
                }
            } else {
                $storeRow = null;
                if ($currentStoreId) {
                    try {
                        $storeRow = $db->fetchOne("SELECT quantity FROM store_stock WHERE store_id = ? AND product_id = ?", [$currentStoreId, $p['id']]);
                    } catch (\Throwable $e) {}
                }
                $availStock = $storeRow ? (int)$storeRow['quantity'] : (int)($p['stock_quantity'] ?? 0);
                if ($availStock <= 0) {
                    $p['can_sell'] = false;
                    $p['stock_quantity'] = 0;
                    $p['stock_error'] = 'ផលិតផល "' . $p['name'] . '" អស់ស្តុកហើយ';
                } else {
                    $p['can_sell'] = true;
                    $p['stock_quantity'] = $availStock;
                    $p['stock_error'] = '';
                }
            }
        }
        unset($p);

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
