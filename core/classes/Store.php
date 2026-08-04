<?php
// core/classes/Store.php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Tenant.php';

class Store
{
    private static $currentStore = null;

    /**
     * Get all stores for a tenant
     */
    public static function getAll($tenantId = null)
    {
        if (!$tenantId) $tenantId = Tenant::getId();
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT * FROM stores WHERE tenant_id = ? AND is_active = 1 ORDER BY is_default DESC, name ASC",
            [$tenantId]
        );
    }

    /**
     * Get store by ID
     */
    public static function getById($id, $tenantId = null)
    {
        if (!$tenantId) $tenantId = Tenant::getId();
        $db = Database::getInstance();
        return $db->fetchOne(
            "SELECT * FROM stores WHERE id = ? AND tenant_id = ?",
            [$id, $tenantId]
        );
    }

    /**
     * Get the default store for a tenant
     */
    public static function getDefault($tenantId = null)
    {
        if (!$tenantId) $tenantId = Tenant::getId();
        $db = Database::getInstance();
        $store = $db->fetchOne(
            "SELECT * FROM stores WHERE tenant_id = ? AND is_default = 1 AND is_active = 1 LIMIT 1",
            [$tenantId]
        );
        // Fallback to first active store
        if (!$store) {
            $store = $db->fetchOne(
                "SELECT * FROM stores WHERE tenant_id = ? AND is_active = 1 ORDER BY id ASC LIMIT 1",
                [$tenantId]
            );
        }
        return $store;
    }

    /**
     * Create a new store
     */
    public static function create($data, $tenantId = null)
    {
        if (!$tenantId) $tenantId = Tenant::getId();
        $db = Database::getInstance();

        // If this is the first store, make it default
        $existingCount = $db->fetchOne(
            "SELECT COUNT(*) as cnt FROM stores WHERE tenant_id = ?",
            [$tenantId]
        );
        if ($existingCount['cnt'] == 0) {
            $data['is_default'] = 1;
        }

        $data['tenant_id'] = $tenantId;
        // Auto-generate code from name if not provided
        if (empty($data['code'])) {
            $data['code'] = self::generateCode($data['name']);
        }

        return $db->insert('stores', $data);
    }

    /**
     * Update a store
     */
    public static function update($id, $data, $tenantId = null)
    {
        if (!$tenantId) $tenantId = Tenant::getId();
        $db = Database::getInstance();

        // If setting as default, unset other defaults
        if (!empty($data['is_default'])) {
            $db->query(
                "UPDATE stores SET is_default = 0 WHERE tenant_id = ? AND id != ?",
                [$tenantId, $id]
            );
        }

        return $db->update('stores', $data, 'id = ? AND tenant_id = ?', [$id, $tenantId]);
    }

    /**
     * Delete (deactivate) a store
     */
    public static function delete($id, $tenantId = null)
    {
        if (!$tenantId) $tenantId = Tenant::getId();

        // Cannot delete default store
        $store = self::getById($id, $tenantId);
        if ($store && $store['is_default']) {
            return false;
        }

        // Soft delete - just deactivate
        $db = Database::getInstance();
        return $db->update('stores', ['is_active' => 0], 'id = ? AND tenant_id = ?', [$id, $tenantId]);
    }

    /**
     * Set current store in session
     */
    public static function setCurrent($storeId, $tenantId = null)
    {
        if (!$tenantId) $tenantId = Tenant::getId();
        $store = self::getById($storeId, $tenantId);

        if ($store) {
            // Check if user is locked to a specific store
            $lockedStoreId = self::getUserLockedStoreId();
            if ($lockedStoreId !== null && (int)$lockedStoreId !== (int)$storeId) {
                // User is locked to a different store — deny the switch
                return false;
            }

            $_SESSION['current_store_id'] = $storeId;
            self::$currentStore = $store;

            // Also update user's current_store_id (not locked_store_id)
            if (class_exists('Auth') && Auth::user()) {
                $userId = Auth::user()['id'];
                $db = Database::getInstance();
                $db->update('users', ['current_store_id' => $storeId], 'id = ?', [$userId]);
            }

            return true;
        }
        return false;
    }

    /**
     * Get current store from session, or default
     */
    public static function getCurrent($tenantId = null)
    {
        if (self::$currentStore !== null) {
            return self::$currentStore;
        }

        if (!$tenantId) $tenantId = Tenant::getId();

        // If user is locked to a specific store, ALWAYS use that store
        $lockedStoreId = self::getUserLockedStoreId();
        if ($lockedStoreId !== null) {
            $store = self::getById($lockedStoreId, $tenantId);
            if ($store) {
                self::$currentStore = $store;
                $_SESSION['current_store_id'] = $store['id'];
                return $store;
            }
        }

        // Try session
        if (isset($_SESSION['current_store_id'])) {
            $store = self::getById($_SESSION['current_store_id'], $tenantId);
            if ($store) {
                self::$currentStore = $store;
                return $store;
            }
        }

        // Try user's last store
        if (class_exists('Auth') && Auth::user()) {
            $user = Auth::user();
            if (!empty($user['current_store_id'])) {
                $store = self::getById($user['current_store_id'], $tenantId);
                if ($store) {
                    self::$currentStore = $store;
                    $_SESSION['current_store_id'] = $store['id'];
                    return $store;
                }
            }
        }

        // Fallback to default store
        $store = self::getDefault($tenantId);
        if ($store) {
            self::$currentStore = $store;
            $_SESSION['current_store_id'] = $store['id'];
        }

        return self::$currentStore;
    }

    /**
     * Get current store ID (convenience)
     */
    public static function getId()
    {
        $store = self::getCurrent();
        return $store ? $store['id'] : null;
    }

    /**
     * Generate a short code from store name
     */
    private static function generateCode($name)
    {
        $words = explode(' ', strtoupper(trim($name)));
        if (count($words) >= 2) {
            return substr($words[0], 0, 2) . substr(end($words), 0, 1);
        }
        return strtoupper(substr($name, 0, 4));
    }

    /**
     * Get the locked store ID for the current user (if any)
     * Returns store ID if user is locked, null if user can switch freely
     */
    public static function getUserLockedStoreId()
    {
        if (class_exists('Auth') && Auth::user()) {
            $user = Auth::user();
            if (!empty($user['locked_store_id'])) {
                return (int)$user['locked_store_id'];
            }
        }
        return null;
    }

    /**
     * Check if the current user is locked to a specific store
     */
    public static function isUserLocked()
    {
        return self::getUserLockedStoreId() !== null;
    }

    /**
     * Get stores the current user is allowed to access
     * For locked users: returns only their assigned store
     * For admin/unlocked users: returns all stores
     */
    public static function getAllowedStores($tenantId = null)
    {
        if (!$tenantId) $tenantId = Tenant::getId();

        $lockedStoreId = self::getUserLockedStoreId();
        if ($lockedStoreId !== null) {
            $store = self::getById($lockedStoreId, $tenantId);
            return $store ? [$store] : [];
        }

        return self::getAll($tenantId);
    }

    /**
     * Set a user's locked store. Only callable by admin.
     * Pass null to unlock the user (allow free switching).
     */
    public static function setUserLockedStore($userId, $storeId, $tenantId = null)
    {
        if (!$tenantId) $tenantId = Tenant::getId();
        $db = Database::getInstance();

        if ($storeId === null || $storeId === '' || $storeId === 0 || $storeId === '0') {
            // Unlock the user
            return $db->update('users', ['locked_store_id' => null], 'id = ? AND tenant_id = ?', [$userId, $tenantId]);
        }

        // Verify store belongs to this tenant
        $store = self::getById($storeId, $tenantId);
        if (!$store) {
            return false;
        }

        return $db->update('users', ['locked_store_id' => (int)$storeId], 'id = ? AND tenant_id = ?', [$userId, $tenantId]);
    }
}
