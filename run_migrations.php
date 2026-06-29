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

    echo "Migrations completed successfully!";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage();
}
?>
