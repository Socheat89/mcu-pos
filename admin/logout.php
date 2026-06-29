<?php
// admin/logout.php
session_start();
session_destroy();
header("Location: login.php?success=" . urlencode('Master session ended.'));
exit;
?>
