<?php
require_once __DIR__ . '/core/classes/Database.php';
$db = Database::getInstance();
try {
    $db->query("ALTER TABLE orders ADD COLUMN notes TEXT NULL AFTER status");
    echo "SUCCESS: 'notes' column added to 'orders' table.";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
