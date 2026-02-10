<?php
require_once __DIR__ . '/config/database.php';

// Access Control
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized");
}

if (isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM locations WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        header("Location: admin-locations.php");
        exit;
    } catch (Exception $e) {
        die("Error deleting location. It might have associated reservations.");
    }
}
