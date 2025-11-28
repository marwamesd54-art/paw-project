<?php
require 'config/db.php';
try {
    $db = (new Database())->getConnection();
    echo 'Database connection: OK';
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>
