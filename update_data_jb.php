<?php
require_once __DIR__ . '/config/database.php';

echo "<h1>Updating Data to Johor Bahru...</h1>";
echo "<pre>";

try {
    // 1. Disable FK checks to allow truncation
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // 2. Clear old data (keep Users, clear reservations/slots/locations)
    echo "Clearing old location data...\n";
    $pdo->exec("TRUNCATE TABLE reservations");
    $pdo->exec("TRUNCATE TABLE parking_slots");
    $pdo->exec("TRUNCATE TABLE locations");
    
    // 3. Insert New Locations (JB)
    $sql_loc = "INSERT INTO locations (id, name, address) VALUES 
    (1, 'JB City Square', '106-108, Jalan Wong Ah Fook, Bandar Johor Bahru'),
    (2, 'Mid Valley Southkey', '1, Persiaran Southkey 1, Kota Southkey'),
    (3, 'Paradigm Mall JB', 'Jalan Bertingkat Skudai, Taman Bukit Mewah'),
    (4, 'KSL City Mall', '33, Jalan Seladang, Taman Abad')";
    $pdo->exec($sql_loc);
    echo "Inserted locations: JB City Square, Mid Valley, Paradigm, KSL.\n";

    // 4. Insert Lots of Slots
    echo "Generating parking slots...\n";
    $slots_sql = "INSERT INTO parking_slots (location_id, slot_number, price_per_hour, is_covered) VALUES ";
    $values = [];
    
    // Helper to generate slots
    // JB City Square: 20 slots ($5/hr)
    for($i=1; $i<=20; $i++) {
        $num = sprintf("A-%02d", $i);
        $values[] = "(1, '$num', 5.00, 1)";
    }
    // Mid Valley: 30 slots ($6/hr)
    for($i=1; $i<=30; $i++) {
        $num = sprintf("MV-%02d", $i);
        $values[] = "(2, '$num', 6.00, 1)";
    }
    // Paradigm: 15 slots ($3/hr)
    for($i=1; $i<=15; $i++) {
        $num = sprintf("P-%02d", $i);
        $values[] = "(3, '$num', 3.00, 1)";
    }
    // KSL: 15 slots ($2/hr)
    for($i=1; $i<=15; $i++) {
        $num = sprintf("K-%02d", $i);
        $values[] = "(4, '$num', 2.00, 0)";
    }
    
    $chunked = array_chunk($values, 50); // Insert in chunks
    foreach($chunked as $chunk) {
        $pdo->exec($slots_sql . implode(',', $chunk));
    }
    
    echo "Inserted 80 new parking slots.\n";
    
    // Re-enable FK
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    echo "\nSUCCESS: System locations updated to Johor Bahru.\n";
    echo "<a href='index.php'>Go to Home</a>";

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage();
}
echo "</pre>";
?>
