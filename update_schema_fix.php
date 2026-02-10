<?php
require_once __DIR__ . '/config/database.php';

try {
    echo "Updating database schema...\n";

    // Allow NULL for exit_time
    $sql1 = "ALTER TABLE reservations MODIFY exit_time DATETIME NULL";
    $pdo->exec($sql1);
    echo "✔ Modified exit_time to be NULLABLE.\n";

    // Allow NULL for total_price
    $sql2 = "ALTER TABLE reservations MODIFY total_price DECIMAL(10, 2) NULL";
    $pdo->exec($sql2);
    echo "✔ Modified total_price to be NULLABLE.\n";

    echo "Database schema updated successfully!\n";

} catch (PDOException $e) {
    echo "❌ Error updating database: " . $e->getMessage() . "\n";
}
?>
