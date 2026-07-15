<?php
require 'core/classes/Database.php';
$db = Database::getInstance();
$plans = $db->fetchAll("SELECT * FROM systems");
file_put_contents('scratch_plans.json', json_encode($plans, JSON_PRETTY_PRINT));
echo "Done\n";
