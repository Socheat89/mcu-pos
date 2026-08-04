<?php
require_once __DIR__ . '/core/classes/Database.php';
$db = Database::getInstance();

try {
    echo "Starting migrations...<br>";

    // 1. Add expires_at to tenant_systems
    echo "Checking tenant_systems table...<br>";
    $columns = $db->fetchAll("SHOW COLUMNS FROM tenant_systems LIKE 'expires_at'");
    if (empty($columns)) {
        echo "Adding 'expires_at' column to 'tenant_systems'...<br>";
        $db->query("ALTER TABLE tenant_systems ADD COLUMN expires_at DATETIME NULL AFTER subscribed_at");
    }

    // 2. system_modules table with feature control
    echo "Ensuring 'system_modules' table exists...<br>";
    
    // Check if table exists first
    $tableExists = $db->fetchAll("SHOW TABLES LIKE 'system_modules'");
    if (empty($tableExists)) {
        $db->query("CREATE TABLE system_modules (
            id INT AUTO_INCREMENT PRIMARY KEY,
            system_id INT NOT NULL,
            module_name VARCHAR(50) NOT NULL,
            feature_key VARCHAR(50) NULL,
            FOREIGN KEY (system_id) REFERENCES systems(id) ON DELETE CASCADE
        )");
    } else {
        // Table exists, check if 'id' is the primary key
        $primaryKeys = $db->fetchAll("SHOW KEYS FROM system_modules WHERE Key_name = 'PRIMARY'");
        $isIdPrimary = false;
        if (count($primaryKeys) === 1 && $primaryKeys[0]['Column_name'] === 'id') {
            $isIdPrimary = true;
        }

        if (!$isIdPrimary) {
            echo "Repairing primary key for 'system_modules'...<br>";
            // Check if 'id' column exists at all
            $idCol = $db->fetchAll("SHOW COLUMNS FROM system_modules LIKE 'id'");
            if (empty($idCol)) {
                // If there's a composite primary key, we must drop it first
                if (!empty($primaryKeys)) {
                    $db->query("ALTER TABLE system_modules DROP PRIMARY KEY");
                }
                $db->query("ALTER TABLE system_modules ADD COLUMN id INT AUTO_INCREMENT PRIMARY KEY FIRST");
            } else {
                // 'id' exists but isn't primary. This is rare but possible.
                if (!empty($primaryKeys)) {
                    $db->query("ALTER TABLE system_modules DROP PRIMARY KEY");
                }
                $db->query("ALTER TABLE system_modules MODIFY COLUMN id INT AUTO_INCREMENT PRIMARY KEY");
            }
        }
    }


    // Fix columns: check if feature_key exists
    $columns = $db->fetchAll("SHOW COLUMNS FROM system_modules LIKE 'feature_key'");
    if (empty($columns)) {
        echo "Adding 'feature_key' to 'system_modules'...<br>";
        $db->query("ALTER TABLE system_modules ADD COLUMN feature_key VARCHAR(50) NULL AFTER module_name");
    }

    // IMPORTANT: Add new index FIRST so MySQL always has an index for the Foreign Key
    $indexes = $db->fetchAll("SHOW INDEX FROM system_modules WHERE Key_name = 'unique_system_feature'");
    if (empty($indexes)) {
        echo "Adding new feature-level unique index...<br>";
        $db->query("ALTER TABLE system_modules ADD UNIQUE KEY unique_system_feature (system_id, module_name, feature_key)");
    }

    // Now it is safe to drop the old index
    $indexes = $db->fetchAll("SHOW INDEX FROM system_modules WHERE Key_name = 'unique_system_module'");
    if (!empty($indexes)) {
        echo "Cleaning up old index...<br>";
        $db->query("ALTER TABLE system_modules DROP INDEX unique_system_module");
    }

    // 3. Add 'notes' column to 'orders' table
    echo "Checking 'orders' table for 'notes' column...<br>";
    $columns = $db->fetchAll("SHOW COLUMNS FROM orders LIKE 'notes'");
    if (empty($columns)) {
        echo "Adding 'notes' column to 'orders'...<br>";
        $db->query("ALTER TABLE orders ADD COLUMN notes TEXT NULL AFTER status");
    }

    // 4. Create 'tenant_features' table for overrides
    echo "Ensuring 'tenant_features' table exists...<br>";
    $tableExists = $db->fetchAll("SHOW TABLES LIKE 'tenant_features'");
    if (empty($tableExists)) {
        $db->query("CREATE TABLE tenant_features (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT NOT NULL,
            module_name VARCHAR(50) NOT NULL,
            feature_key VARCHAR(50) NOT NULL,
            action ENUM('grant', 'deny') NOT NULL DEFAULT 'grant',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
            UNIQUE KEY unique_tenant_feature (tenant_id, module_name, feature_key)
        )");
        echo "'tenant_features' table created.<br>";
    }

    // 5. Create password reset token table
    echo "Ensuring 'password_resets' table exists...<br>";
    $tableExists = $db->fetchAll("SHOW TABLES LIKE 'password_resets'");
    if (empty($tableExists)) {
        $db->query("CREATE TABLE password_resets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            token_hash CHAR(64) NOT NULL,
            expires_at DATETIME NOT NULL,
            used_at DATETIME NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_password_reset_token (token_hash),
            INDEX idx_password_reset_user (user_id),
            INDEX idx_password_reset_expires (expires_at)
        )");
        echo "'password_resets' table created.<br>";
    }

    // 6. Create 'pos_sessions' table
    echo "Ensuring 'pos_sessions' table exists...<br>";
    $tableExists = $db->fetchAll("SHOW TABLES LIKE 'pos_sessions'");
    if (empty($tableExists)) {
        $db->query("CREATE TABLE pos_sessions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT NOT NULL,
            user_id INT NOT NULL,
            opening_balance DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            closing_balance DECIMAL(10,2) NULL DEFAULT NULL,
            total_sales DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            status ENUM('open', 'closed') DEFAULT 'open',
            opened_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            closed_at TIMESTAMP NULL DEFAULT NULL,
            FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_tenant_id (tenant_id),
            INDEX idx_status (status)
        )");
        echo "'pos_sessions' table created.<br>";
    }

    // 7. Add 'session_id' column to 'orders' table
    echo "Checking 'orders' table for 'session_id' column...<br>";
    $columns = $db->fetchAll("SHOW COLUMNS FROM orders LIKE 'session_id'");
    if (empty($columns)) {
        echo "Adding 'session_id' column to 'orders'...<br>";
        $db->query("ALTER TABLE orders ADD COLUMN session_id INT NULL AFTER tenant_id");
        $db->query("ALTER TABLE orders ADD CONSTRAINT fk_orders_session_id FOREIGN KEY (session_id) REFERENCES pos_sessions(id) ON DELETE SET NULL");
        echo "'session_id' column added.<br>";
    }

    // 8. Add 'password_changed_at' column to 'users' table
    echo "Checking 'users' table for 'password_changed_at' column...<br>";
    $columns = $db->fetchAll("SHOW COLUMNS FROM users LIKE 'password_changed_at'");
    if (empty($columns)) {
        echo "Adding 'password_changed_at' column to 'users'...<br>";
        $db->query("ALTER TABLE users ADD COLUMN password_changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER password_hash");
        echo "'password_changed_at' column added.<br>";
    }

    // 9. Multi-Store: Create 'stores' table
    echo "Checking 'stores' table...<br>";
    $tableExists = $db->fetchAll("SHOW TABLES LIKE 'stores'");
    if (empty($tableExists)) {
        $db->query("CREATE TABLE stores (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            code VARCHAR(50) DEFAULT NULL,
            address TEXT DEFAULT NULL,
            phone VARCHAR(50) DEFAULT NULL,
            email VARCHAR(255) DEFAULT NULL,
            is_default TINYINT(1) DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
            INDEX idx_tenant_id (tenant_id),
            INDEX idx_is_default (tenant_id, is_default)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "'stores' table created.<br>";
    }

    // 10. Add store_id to products
    echo "Checking 'products.store_id'...<br>";
    $columns = $db->fetchAll("SHOW COLUMNS FROM products LIKE 'store_id'");
    if (empty($columns)) {
        $db->query("ALTER TABLE products ADD COLUMN store_id INT DEFAULT NULL AFTER tenant_id, ADD INDEX idx_store_id (store_id)");
        echo "'products.store_id' added.<br>";
    }

    // 11. Add store_id to orders
    echo "Checking 'orders.store_id'...<br>";
    $columns = $db->fetchAll("SHOW COLUMNS FROM orders LIKE 'store_id'");
    if (empty($columns)) {
        $db->query("ALTER TABLE orders ADD COLUMN store_id INT DEFAULT NULL AFTER tenant_id, ADD INDEX idx_store_id (store_id)");
        echo "'orders.store_id' added.<br>";
    }

    // 12. Add store_id to customers
    echo "Checking 'customers.store_id'...<br>";
    $columns = $db->fetchAll("SHOW COLUMNS FROM customers LIKE 'store_id'");
    if (empty($columns)) {
        $db->query("ALTER TABLE customers ADD COLUMN store_id INT DEFAULT NULL AFTER tenant_id, ADD INDEX idx_store_id (store_id)");
        echo "'customers.store_id' added.<br>";
    }

    // 13. Add store_id to categories
    echo "Checking 'categories.store_id'...<br>";
    $columns = $db->fetchAll("SHOW COLUMNS FROM categories LIKE 'store_id'");
    if (empty($columns)) {
        $db->query("ALTER TABLE categories ADD COLUMN store_id INT DEFAULT NULL AFTER tenant_id, ADD INDEX idx_store_id (store_id)");
        echo "'categories.store_id' added.<br>";
    }

    // 14. Add store_id to stock_logs
    echo "Checking 'stock_logs.store_id'...<br>";
    $columns = $db->fetchAll("SHOW COLUMNS FROM stock_logs LIKE 'store_id'");
    if (empty($columns)) {
        $db->query("ALTER TABLE stock_logs ADD COLUMN store_id INT DEFAULT NULL AFTER tenant_id, ADD INDEX idx_store_id (store_id)");
        echo "'stock_logs.store_id' added.<br>";
    }

    // 15. Add current_store_id to users
    echo "Checking 'users.current_store_id'...<br>";
    $columns = $db->fetchAll("SHOW COLUMNS FROM users LIKE 'current_store_id'");
    if (empty($columns)) {
        $db->query("ALTER TABLE users ADD COLUMN current_store_id INT DEFAULT NULL AFTER role_id");
        echo "'users.current_store_id' added.<br>";
    }

    // 16. Seed default stores for existing tenants
    echo "Seeding default stores for existing tenants...<br>";
    $tenants = $db->fetchAll("SELECT id, name FROM tenants WHERE status = 'active'");
    foreach ($tenants as $tenant) {
        $existing = $db->fetchOne("SELECT COUNT(*) as cnt FROM stores WHERE tenant_id = ?", [$tenant['id']]);
        if ($existing['cnt'] == 0) {
            $db->insert('stores', [
                'tenant_id'  => $tenant['id'],
                'name'       => $tenant['name'] . ' Main',
                'code'       => 'MAIN',
                'is_default' => 1,
                'is_active'  => 1,
            ]);
            echo "→ Default store created for: {$tenant['name']}<br>";
        }
    }

    // 17. Add 'store_limit' to systems (plans)
    echo "Checking 'systems.store_limit'...<br>";
    $columns = $db->fetchAll("SHOW COLUMNS FROM systems LIKE 'store_limit'");
    if (empty($columns)) {
        $db->query("ALTER TABLE systems ADD COLUMN store_limit INT DEFAULT 1 COMMENT 'Max stores allowed (0=unlimited)' AFTER price");
        echo "'systems.store_limit' added.<br>";
    }

    // 18. Add 'cashier_limit' to systems (plans)
    echo "Checking 'systems.cashier_limit'...<br>";
    $columns = $db->fetchAll("SHOW COLUMNS FROM systems LIKE 'cashier_limit'");
    if (empty($columns)) {
        $db->query("ALTER TABLE systems ADD COLUMN cashier_limit INT DEFAULT 1 COMMENT 'Max cashiers allowed (0=unlimited)' AFTER store_limit");
        echo "'systems.cashier_limit' added.<br>";
    }

    // 19. Set default limits for existing plans
    echo "Setting default plan limits...<br>";
    $systems = $db->fetchAll("SELECT id, name, price FROM systems");
    foreach ($systems as $s) {
        $price = (float)$s['price'];
        if ($price <= 5) {
            // $5 plan → 1 store, 1 cashier
            $db->query("UPDATE systems SET store_limit = 1, cashier_limit = 1 WHERE id = ? AND store_limit IS NULL", [$s['id']]);
        } elseif ($price <= 50) {
            // $30 plan → 5 stores, 5 cashiers
            $db->query("UPDATE systems SET store_limit = 5, cashier_limit = 5 WHERE id = ? AND store_limit IS NULL", [$s['id']]);
        } else {
            // $99.99+ → unlimited
            $db->query("UPDATE systems SET store_limit = 0, cashier_limit = 0 WHERE id = ? AND store_limit IS NULL", [$s['id']]);
        }
    }
    echo "Plan limits updated.<br>";

    // 20. Add store_id to pos_sessions
    echo "Checking 'pos_sessions.store_id'...<br>";
    $columns = $db->fetchAll("SHOW COLUMNS FROM pos_sessions LIKE 'store_id'");
    if (empty($columns)) {
        $db->query("ALTER TABLE pos_sessions ADD COLUMN store_id INT DEFAULT NULL AFTER tenant_id, ADD INDEX idx_store_id (store_id)");
        echo "'pos_sessions.store_id' added.<br>";
    }

    // 21. Add 'is_trial' column to tenant_systems
    echo "Checking 'tenant_systems.is_trial'...<br>";
    $columns = $db->fetchAll("SHOW COLUMNS FROM tenant_systems LIKE 'is_trial'");
    if (empty($columns)) {
        $db->query("ALTER TABLE tenant_systems ADD COLUMN is_trial TINYINT(1) DEFAULT 0 COMMENT '1=trial, 0=paid' AFTER status");
        echo "'tenant_systems.is_trial' added.<br>";
    }

    // 22. Seed Free Trial plan
    echo "Seeding Free Trial plan...<br>";
    $existing = $db->fetchOne("SELECT id FROM systems WHERE name = 'Free Trial'");
    if (!$existing) {
        $trialId = $db->insert('systems', [
            'name'           => 'Free Trial',
            'description'    => '7-day free trial with basic POS features. No credit card required.',
            'price'          => 0.00,
            'status'         => 'active',
            'store_limit'    => 1,
            'cashier_limit'  => 1,
        ]);
        echo "→ Free Trial plan created (ID: {$trialId}).<br>";

        // Seed basic POS features for trial
        $trialFeatures = [
            ['pos', 'core'],
            ['pos', 'orders'],
            ['pos', 'inventory'],
            ['pos', 'customers'],
            ['pos', 'settings'],
        ];
        foreach ($trialFeatures as $feat) {
            $db->insert('system_modules', [
                'system_id'   => $trialId,
                'module_name' => $feat[0],
                'feature_key' => $feat[1],
            ]);
        }
        echo "→ Free Trial features seeded.<br>";
    } else {
        echo "→ Free Trial plan already exists.<br>";
    }

    // 11. Create 'product_sizes' table for coffee shops & variable-price products
    echo "Ensuring 'product_sizes' table exists...<br>";
    $tableExists = $db->fetchAll("SHOW TABLES LIKE 'product_sizes'");
    if (empty($tableExists)) {
        $db->query("CREATE TABLE product_sizes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            size_name VARCHAR(50) NOT NULL COMMENT 'e.g. Small, Medium, Large',
            price DECIMAL(10,2) NOT NULL,
            tenant_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
            UNIQUE KEY unique_product_size (product_id, size_name),
            INDEX idx_product_sizes_product (product_id),
            INDEX idx_product_sizes_tenant (tenant_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        echo "→ 'product_sizes' table created.<br>";
    } else {
        echo "→ 'product_sizes' table already exists.<br>";
    }

    // 12. Add 'cost_price' column to products for purchase cost tracking
    echo "Checking 'products.cost_price'...<br>";
    $columns = $db->fetchAll("SHOW COLUMNS FROM products LIKE 'cost_price'");
    if (empty($columns)) {
        $db->query("ALTER TABLE products ADD COLUMN cost_price DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Purchase/cost price' AFTER price");
        echo "→ 'products.cost_price' added.<br>";
    } else {
        echo "→ 'products.cost_price' already exists.<br>";
    }

    // 13. Expand payments.method to VARCHAR + add bank_name + currency
    echo "Updating 'payments' table for bank & currency support...<br>";
    $cols = $db->fetchAll("SHOW COLUMNS FROM payments LIKE 'bank_name'");
    if (empty($cols)) {
        $db->query("ALTER TABLE payments MODIFY COLUMN method VARCHAR(50) NOT NULL DEFAULT 'cash' COMMENT 'cash, aba, acleda, wing, truemoney, card, other'");
        $db->query("ALTER TABLE payments ADD COLUMN bank_name VARCHAR(50) DEFAULT NULL COMMENT 'Bank name if payment via bank transfer' AFTER method");
        echo "→ 'payments': method→VARCHAR, bank_name added.<br>";
    } else {
        echo "→ 'payments' already has bank_name.<br>";
    }
    $cols2 = $db->fetchAll("SHOW COLUMNS FROM payments LIKE 'currency'");
    if (empty($cols2)) {
        $db->query("ALTER TABLE payments ADD COLUMN currency VARCHAR(3) DEFAULT 'USD' COMMENT 'USD or KHR' AFTER amount");
        echo "→ 'payments': currency column added.<br>";
    } else {
        echo "→ 'payments' already has currency.<br>";
    }
    $indexes = $db->fetchAll("SHOW INDEX FROM payments WHERE Key_name = 'idx_bank_name'");
    if (empty($indexes)) {
        $db->query("ALTER TABLE payments ADD INDEX idx_bank_name (bank_name)");
    }

    // 14. Create 'ingredients' table
    echo "Ensuring 'ingredients' table exists...<br>";
    $tableExists = $db->fetchAll("SHOW TABLES LIKE 'ingredients'");
    if (empty($tableExists)) {
        $db->query("CREATE TABLE ingredients (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            stock_quantity DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            unit VARCHAR(50) NOT NULL,
            min_stock_alert DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
            INDEX idx_ingredients_tenant (tenant_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        echo "→ 'ingredients' table created.<br>";
    } else {
        echo "→ 'ingredients' table already exists.<br>";
    }

    // 15. Create 'product_recipes' table
    echo "Ensuring 'product_recipes' table exists...<br>";
    $tableExists = $db->fetchAll("SHOW TABLES LIKE 'product_recipes'");
    if (empty($tableExists)) {
        $db->query("CREATE TABLE product_recipes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT NOT NULL,
            product_id INT NOT NULL,
            product_size_id INT DEFAULT NULL,
            ingredient_id INT NOT NULL,
            quantity DECIMAL(10,2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            FOREIGN KEY (product_size_id) REFERENCES product_sizes(id) ON DELETE SET NULL,
            FOREIGN KEY (ingredient_id) REFERENCES ingredients(id) ON DELETE CASCADE,
            INDEX idx_recipes_product (product_id),
            INDEX idx_recipes_size (product_size_id),
            INDEX idx_recipes_ingredient (ingredient_id),
            INDEX idx_recipes_tenant (tenant_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        echo "→ 'product_recipes' table created.<br>";
    } else {
        echo "→ 'product_recipes' table already exists.<br>";
    }

    // 16. Alter 'order_items' for size tracking
    echo "Updating 'order_items' for size support...<br>";
    $cols = $db->fetchAll("SHOW COLUMNS FROM order_items LIKE 'size_name'");
    if (empty($cols)) {
        $db->query("ALTER TABLE order_items ADD COLUMN size_name VARCHAR(50) DEFAULT NULL AFTER product_id");
        echo "→ 'order_items.size_name' added.<br>";
    } else {
        echo "→ 'order_items' already has size_name.<br>";
    }

    $cols2 = $db->fetchAll("SHOW COLUMNS FROM order_items LIKE 'product_size_id'");
    if (empty($cols2)) {
        $db->query("ALTER TABLE order_items ADD COLUMN product_size_id INT DEFAULT NULL AFTER size_name");
        $db->query("ALTER TABLE order_items ADD CONSTRAINT fk_order_items_size_id FOREIGN KEY (product_size_id) REFERENCES product_sizes(id) ON DELETE SET NULL");
        echo "→ 'order_items.product_size_id' added.<br>";
    } else {
        echo "→ 'order_items' already has product_size_id.<br>";
    }

    // 17. Create 'ingredient_stock_logs' table
    echo "Ensuring 'ingredient_stock_logs' table exists...<br>";
    $tableExists = $db->fetchAll("SHOW TABLES LIKE 'ingredient_stock_logs'");
    if (empty($tableExists)) {
        $db->query("CREATE TABLE ingredient_stock_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT NOT NULL,
            ingredient_id INT NOT NULL,
            change_quantity DECIMAL(10,2) NOT NULL,
            reason VARCHAR(100) NOT NULL COMMENT 'topup, adjust, sale',
            order_id INT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
            FOREIGN KEY (ingredient_id) REFERENCES ingredients(id) ON DELETE CASCADE,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
            INDEX idx_ing_logs_ing (ingredient_id),
            INDEX idx_ing_logs_tenant (tenant_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        echo "→ 'ingredient_stock_logs' table created.<br>";
    } else {
        echo "→ 'ingredient_stock_logs' table already exists.<br>";
    }

    echo "Migrations completed successfully!";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage();
}

// ── Run addon migrations (non-breaking) ──────────────────
try {
    // 18. Add locked_store_id to users (store locking for cashiers)
    echo "<br>Running addon migrations...<br>";
    echo "Checking 'users.locked_store_id'...<br>";
    $columns = $db->fetchAll("SHOW COLUMNS FROM users LIKE 'locked_store_id'");
    if (empty($columns)) {
        $db->query("ALTER TABLE users ADD COLUMN locked_store_id INT DEFAULT NULL COMMENT 'User is locked to this store (NULL=can switch freely)'");
        echo "→ 'users.locked_store_id' added.<br>";
    } else {
        echo "→ 'users.locked_store_id' already exists.<br>";
    }

    echo "Addon migrations completed!";
} catch (Exception $e) {
    echo "Addon migration failed: " . $e->getMessage();
}
?>
