<?php
require_once __DIR__ . '/config/database.php';

try {
    $stmt = $pdo->prepare("UPDATE locations SET image_url = ? WHERE name LIKE ?");
    $stmt->execute(['assets/images/ksl_city.jpg', '%KSL City%']);
    
    echo "<h1>Image Updated Successfully!</h1>";
    echo "<p>KSL City Mall now uses the new image.</p>";
    echo "<a href='locations.php'>Check Locations Page</a>";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
