<?php
// modules/pos/models/Product.php
require_once __DIR__ . '/../../../core/classes/Database.php';
require_once __DIR__ . '/../../../core/classes/Tenant.php';

class Product {
    private static $db;

    public static function init() {
        self::$db = Database::getInstance();
    }

    public static function getAll($tenantId = null) {
        if (!$tenantId) $tenantId = Tenant::getId();
        return self::$db->fetchAll(
            "SELECT p.*, c.name as category_name FROM products p 
             LEFT JOIN categories c ON p.category_id = c.id 
             WHERE p.tenant_id = ? ORDER BY p.name",
            [$tenantId]
        );
    }

    public static function getById($id, $tenantId = null) {
        if (!$tenantId) $tenantId = Tenant::getId();
        return self::$db->fetchOne(
            "SELECT p.*, c.name as category_name FROM products p 
             LEFT JOIN categories c ON p.category_id = c.id 
             WHERE p.id = ? AND p.tenant_id = ?",
            [$id, $tenantId]
        );
    }

    public static function create($data, $tenantId = null) {
        if (!$tenantId) $tenantId = Tenant::getId();
        $data['tenant_id'] = $tenantId;
        return self::$db->insert('products', $data);
    }

    public static function update($id, $data, $tenantId = null) {
        if (!$tenantId) $tenantId = Tenant::getId();
        return self::$db->update('products', $data, 'id = ? AND tenant_id = ?', [$id, $tenantId]);
    }

    public static function delete($id, $tenantId = null) {
        if (!$tenantId) $tenantId = Tenant::getId();
        return self::$db->delete('products', 'id = ? AND tenant_id = ?', [$id, $tenantId]);
    }

    public static function updateStock($id, $quantity, $tenantId = null) {
        if (!$tenantId) $tenantId = Tenant::getId();
        $current = self::getById($id, $tenantId);
        if ($current) {
            $newStock = $current['stock_quantity'] + $quantity;
            return self::$db->update('products', ['stock_quantity' => $newStock], 'id = ? AND tenant_id = ?', [$id, $tenantId]);
        }
        return false;
    }

    public static function search($query, $tenantId = null) {
        if (!$tenantId) $tenantId = Tenant::getId();
        $searchTerm = "%{$query}%";
        return self::$db->fetchAll(
            "SELECT p.*, c.name as category_name FROM products p 
             LEFT JOIN categories c ON p.category_id = c.id 
             WHERE p.tenant_id = ? AND (p.name LIKE ? OR p.sku LIKE ? OR p.barcode LIKE ?) 
             ORDER BY p.name",
            [$tenantId, $searchTerm, $searchTerm, $searchTerm]
        );
    }

    // ─── Product Sizes ───────────────────────────────────────

    public static function getSizes($productId, $tenantId = null) {
        if (!$tenantId) $tenantId = Tenant::getId();
        return self::$db->fetchAll(
            "SELECT * FROM product_sizes WHERE product_id = ? AND tenant_id = ? ORDER BY id",
            [$productId, $tenantId]
        );
    }

    public static function saveSizes($productId, $sizes, $tenantId = null) {
        if (!$tenantId) $tenantId = Tenant::getId();
        // Delete existing sizes for this product
        self::$db->delete('product_sizes', 'product_id = ? AND tenant_id = ?', [$productId, $tenantId]);
        // Insert new sizes
        foreach ($sizes as $size) {
            if (!empty($size['size_name']) && isset($size['price'])) {
                self::$db->insert('product_sizes', [
                    'product_id' => $productId,
                    'size_name'  => $size['size_name'],
                    'price'      => (float)$size['price'],
                    'tenant_id'  => $tenantId
                ]);
            }
        }
    }

    public static function getAllWithSizes($tenantId = null) {
        if (!$tenantId) $tenantId = Tenant::getId();
        $products = self::getAll($tenantId);
        foreach ($products as &$product) {
            $product['sizes'] = self::getSizes($product['id'], $tenantId);
        }
        return $products;
    }
}

Product::init();
?>

