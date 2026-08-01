<?php
// modules/pos/models/Ingredient.php
require_once __DIR__ . '/../../../core/classes/Database.php';
require_once __DIR__ . '/../../../core/classes/Tenant.php';

class Ingredient {
    private static $db;

    public static function init() {
        self::$db = Database::getInstance();
    }

    public static function getAll($tenantId = null) {
        if (!$tenantId) $tenantId = Tenant::getId();
        return self::$db->fetchAll(
            "SELECT * FROM ingredients WHERE tenant_id = ? ORDER BY name",
            [$tenantId]
        );
    }

    public static function getById($id, $tenantId = null) {
        if (!$tenantId) $tenantId = Tenant::getId();
        return self::$db->fetchOne(
            "SELECT * FROM ingredients WHERE id = ? AND tenant_id = ?",
            [$id, $tenantId]
        );
    }

    public static function create($data, $tenantId = null) {
        if (!$tenantId) $tenantId = Tenant::getId();
        $data['tenant_id'] = $tenantId;
        return self::$db->insert('ingredients', $data);
    }

    public static function update($id, $data, $tenantId = null) {
        if (!$tenantId) $tenantId = Tenant::getId();
        return self::$db->update('ingredients', $data, 'id = ? AND tenant_id = ?', [$id, $tenantId]);
    }

    public static function delete($id, $tenantId = null) {
        if (!$tenantId) $tenantId = Tenant::getId();
        return self::$db->delete('ingredients', 'id = ? AND tenant_id = ?', [$id, $tenantId]);
    }

    // Recipe logic
    public static function getRecipesByProduct($productId, $tenantId = null) {
        if (!$tenantId) $tenantId = Tenant::getId();
        return self::$db->fetchAll(
            "SELECT pr.*, i.name as ingredient_name, i.unit 
             FROM product_recipes pr 
             JOIN ingredients i ON pr.ingredient_id = i.id 
             WHERE pr.product_id = ? AND pr.tenant_id = ?",
            [$productId, $tenantId]
        );
    }

    public static function saveRecipe($productId, $recipeItems, $tenantId = null) {
        if (!$tenantId) $tenantId = Tenant::getId();
        
        // Remove existing recipe elements for this product
        self::$db->delete('product_recipes', 'product_id = ? AND tenant_id = ?', [$productId, $tenantId]);

        foreach ($recipeItems as $item) {
            if (empty($item['ingredient_id']) || empty($item['quantity']) || (float)$item['quantity'] <= 0) {
                continue;
            }
            self::$db->insert('product_recipes', [
                'tenant_id'       => $tenantId,
                'product_id'      => $productId,
                'product_size_id' => !empty($item['product_size_id']) ? (int)$item['product_size_id'] : null,
                'ingredient_id'   => (int)$item['ingredient_id'],
                'quantity'        => (float)$item['quantity']
            ]);
        }
        return true;
    }

    // Stock deduction & logs
    public static function deductStock($ingredientId, $quantity, $reason, $orderId = null, $tenantId = null) {
        if (!$tenantId) $tenantId = Tenant::getId();
        
        $ingredient = self::getById($ingredientId, $tenantId);
        if (!$ingredient) return false;

        $newQty = (float)$ingredient['stock_quantity'] - (float)$quantity;
        
        // Update stock
        self::$db->update('ingredients', ['stock_quantity' => $newQty], 'id = ? AND tenant_id = ?', [$ingredientId, $tenantId]);

        // Insert log
        self::$db->insert('ingredient_stock_logs', [
            'tenant_id' => $tenantId,
            'ingredient_id' => $ingredientId,
            'change_quantity' => -(float)$quantity,
            'reason' => $reason,
            'order_id' => $orderId
        ]);

        return true;
    }

    public static function logTopup($ingredientId, $quantity, $storeId = null, $tenantId = null) {
        if (!$tenantId) $tenantId = Tenant::getId();
        
        $ingredient = self::getById($ingredientId, $tenantId);
        if (!$ingredient) return false;

        $newQty = (float)$ingredient['stock_quantity'] + (float)$quantity;
        self::$db->update('ingredients', ['stock_quantity' => $newQty], 'id = ? AND tenant_id = ?', [$ingredientId, $tenantId]);

        if ($storeId) {
            try {
                self::$db->query(
                    "INSERT INTO ingredient_store_stock (tenant_id, store_id, ingredient_id, quantity)
                     VALUES (?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE quantity = quantity + ?",
                    [$tenantId, $storeId, $ingredientId, (float)$quantity, (float)$quantity]
                );
            } catch (\Throwable $e) {}
        }

        try {
            self::$db->insert('ingredient_stock_logs', [
                'tenant_id' => $tenantId,
                'store_id'  => $storeId,
                'ingredient_id' => $ingredientId,
                'change_quantity' => (float)$quantity,
                'reason' => 'topup'
            ]);
        } catch (\Throwable $e) {
            self::$db->insert('ingredient_stock_logs', [
                'tenant_id' => $tenantId,
                'ingredient_id' => $ingredientId,
                'change_quantity' => (float)$quantity,
                'reason' => 'topup'
            ]);
        }

        return true;
    }

    public static function getStockLogs($tenantId = null) {
        if (!$tenantId) $tenantId = Tenant::getId();
        return self::$db->fetchAll(
            "SELECT isl.*, i.name as ingredient_name, i.unit, o.id as order_number 
             FROM ingredient_stock_logs isl 
             JOIN ingredients i ON isl.ingredient_id = i.id 
             LEFT JOIN orders o ON isl.order_id = o.id 
             WHERE isl.tenant_id = ? 
             ORDER BY isl.created_at DESC LIMIT 100",
            [$tenantId]
        );
    }
}

Ingredient::init();
