<?php
require_once __DIR__ . '/config/database.php';

try {
    $pdo->beginTransaction();

    $newLocations = [
        [
            'name' => 'IKEA Tebrau',
            'address' => '33, Jalan Harmonium, Taman Desa Tebrau, 81100 Johor Bahru',
            'description' => 'Huge parking area with easy access to furniture shopping.',
            'image_url' => 'https://images.unsplash.com/photo-1517544845501-bb78cc886c9e?auto=format&fit=crop&w=600&q=80',
            'base_price' => 2.00
        ],
        [
            'name' => 'Toppen Shopping Centre',
            'address' => 'No. 33A, Jalan Harmonium, Taman Desa Tebrau, 81100 Johor Bahru',
            'description' => 'Attached to IKEA, offering ample covered parking.',
            'image_url' => 'https://images.unsplash.com/photo-1555529733-0e670560f7e1?auto=format&fit=crop&w=600&q=80',
            'base_price' => 3.00
        ],
        [
            'name' => 'AEON Tebrau City',
            'address' => '1, Jalan Desa Tebrau, Taman Desa Tebrau, 81100 Johor Bahru',
            'description' => 'Popular mall with multi-level parking.',
            'image_url' => 'https://images.unsplash.com/photo-1581235720704-06d3acfcb36f?auto=format&fit=crop&w=600&q=80',
            'base_price' => 3.00
        ],
        [
            'name' => 'Sutera Mall',
            'address' => '1, Jalan Sutera Tanjung 8/4, Taman Sutera Utama, 81300 Skudai',
            'description' => 'Classic mall parking in Skudai area.',
            'image_url' => 'https://images.unsplash.com/photo-1520108929780-87729f273060?auto=format&fit=crop&w=600&q=80',
            'base_price' => 2.00
        ],
        [
            'name' => 'Larkin Sentral',
            'address' => 'BT 5, Jalan Garuda, Larkin Jaya, 80350 Johor Bahru',
            'description' => 'Main bus terminal parking, high traffic.',
            'image_url' => 'https://images.unsplash.com/photo-1596423779774-8843c08cd47a?auto=format&fit=crop&w=600&q=80',
            'base_price' => 4.00
        ],
        [
            'name' => 'Johor Premium Outlets (JPO)',
            'address' => 'Jalan Premium Outlets, Indahpura, 81000 Kulai',
            'description' => 'Open air and covered parking for premium shoppers.',
            'image_url' => 'https://images.unsplash.com/photo-1605218427368-35b84386762e?auto=format&fit=crop&w=600&q=80',
            'base_price' => 5.00
        ]
    ];

    echo "Adding new locations...\n";

    $stmtLoc = $pdo->prepare("INSERT INTO locations (name, address, description, image_url) VALUES (?, ?, ?, ?)");
    $stmtSlot = $pdo->prepare("INSERT INTO parking_slots (location_id, slot_number, price_per_hour, is_covered) VALUES (?, ?, ?, ?)");

    foreach ($newLocations as $loc) {
        // Check if exists
        $check = $pdo->prepare("SELECT id FROM locations WHERE name = ?");
        $check->execute([$loc['name']]);
        if ($check->rowCount() > 0) {
            echo " - Location '{$loc['name']}' already exists. Skipping.\n";
            continue;
        }

        // Insert Location
        $stmtLoc->execute([$loc['name'], $loc['address'], $loc['description'], $loc['image_url']]);
        $locId = $pdo->lastInsertId();
        echo " + Added Location: {$loc['name']} (ID: $locId)\n";

        // Add 10 slots for this location
        for ($i = 1; $i <= 10; $i++) {
            $slotNum = sprintf("S-%02d", $i); // e.g. S-01
            $isCovered = ($i <= 5) ? 1 : 0; // First 5 covered
            $price = $loc['base_price'] + ($isCovered ? 1.00 : 0.00); // Covered costs +1
            
            $stmtSlot->execute([$locId, $slotNum, $price, $isCovered]);
        }
        echo "   -> Added 10 slots.\n";
    }

    $pdo->commit();
    echo "Done! All locations and slots added.\n";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
