<?php
require_once __DIR__ . '/../middleware/SuperAdminMiddleware.php';
SuperAdminMiddleware::handle();
require_once __DIR__ . '/../core/classes/Database.php';

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Create system_modules table
    $sql = "CREATE TABLE IF NOT EXISTS system_modules (
        id INT AUTO_INCREMENT PRIMARY KEY,
        system_id INT NOT NULL,
        module_name VARCHAR(50) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (system_id) REFERENCES systems(id) ON DELETE CASCADE,
        UNIQUE KEY unique_system_module (system_id, module_name)
    )";
    $conn->exec($sql);

    echo "Migration successful: system_modules table created.\n";

    // check if systems table has the expected plans from register.php
    $systems = $db->fetchAll("SELECT * FROM systems");
    $systemNames = array_column($systems, 'name');

    $requiredPlans = [
        ['name' => 'Starter POS', 'price' => 0.10, 'description' => 'Product Management, Single User, Basic Reports'],
        ['name' => 'Professional', 'price' => 50.00, 'description' => 'Inventory, 5 Users'],
        ['name' => 'Enterprise', 'price' => 100.00, 'description' => 'Unlimited, All Features']
    ];

    foreach ($requiredPlans as $plan) {
        if (!in_array($plan['name'], $systemNames)) {
            $db->insert('systems', $plan);
            echo "Added plan: {$plan['name']}\n";
        }
    }

} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
