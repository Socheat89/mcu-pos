<?php
// admin/logout.php
require_once __DIR__ . '/../core/bootstrap_session.php';
session_destroy();
header("Location: login.php?success=" . urlencode('Master session ended.'));
exit;
?>
