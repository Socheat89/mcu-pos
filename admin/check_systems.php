<?php
require_once __DIR__ . '/../middleware/SuperAdminMiddleware.php';
SuperAdminMiddleware::handle();
require_once __DIR__ . '/../core/classes/Database.php';
$db = Database::getInstance();
$systems = $db->fetchAll("SELECT * FROM systems");
echo json_encode($systems, JSON_PRETTY_PRINT);
