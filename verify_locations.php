<?php
require_once __DIR__ . '/config/database.php';

try {
    $stmt = $pdo->query("SELECT id, name FROM locations ORDER BY id");
    $locations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Total Locations: " . count($locations) . "\n";
    echo "List:\n";
    foreach ($locations as $loc) {
        echo "[{$loc['id']}] {$loc['name']}\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
