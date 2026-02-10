<?php
require_once __DIR__ . '/config/database.php';

try {
    $updates = [
        'IKEA Tebrau' => 'assets/images/ikea_tebrau.jpg',
        'Toppen Shopping Centre' => 'assets/images/toppen_center.jpg',
        'AEON Tebrau City' => 'assets/images/aeon_tebrau.jpg',
        'Sutera Mall' => 'assets/images/sutera_mall.jpg',
        'Larkin Sentral' => 'assets/images/larkin_sentral.jpg'
    ];

    echo "Updating location images...\n";
    $stmt = $pdo->prepare("UPDATE locations SET image_url = ? WHERE name = ?");

    foreach ($updates as $name => $path) {
        $stmt->execute([$path, $name]);
        if ($stmt->rowCount() > 0) {
            echo " + Updated image for '$name' to '$path'\n";
        } else {
            echo " - Could not find or update '$name'\n";
        }
    }

    echo "Done.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
