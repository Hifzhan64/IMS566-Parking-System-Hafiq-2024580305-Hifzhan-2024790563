<?php
echo "<h1>Parking System - Installation Test</h1>";
echo "<h3>Checking File Structure:</h3>";

// Check folders
$folders = [
    'config' => 'Configuration files',
    'includes' => 'Template files',
    'assets/css' => 'CSS styles'
];

foreach ($folders as $folder => $description) {
    if (is_dir($folder)) {
        echo "<div style='color: green;'>✅ Folder exists: $folder/ ($description)</div>";
    } else {
        echo "<div style='color: red;'>❌ Missing folder: $folder/</div>";
    }
}

echo "<h3>Checking Required Files:</h3>";

$files = [
    'config/database.php' => 'Database configuration',
    'includes/header.php' => 'Header template',
    'includes/footer.php' => 'Footer template',
    'index.php' => 'Home page'
];

foreach ($files as $file => $description) {
    if (file_exists($file)) {
        echo "<div style='color: green;'>✅ File exists: $file</div>";
    } else {
        echo "<div style='color: red;'>❌ Missing file: $file</div>";
    }
}

echo "<h3>Database Connection Test:</h3>";

try {
    $pdo = new PDO("mysql:host=localhost;dbname=parking_system_db", 'root', '');
    echo "<div style='color: green;'>✅ Database connected successfully</div>";
    
    // Check tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($tables) > 0) {
        echo "<div style='color: green;'>✅ Database tables found: " . count($tables) . "</div>";
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>$table</li>";
        }
        echo "</ul>";
    } else {
        echo "<div style='color: orange;'>⚠️ No tables found in database</div>";
        echo "<p>Run the SQL script in phpMyAdmin to create tables.</p>";
    }
    
} catch(PDOException $e) {
    echo "<div style='color: red;'>❌ Database error: " . $e->getMessage() . "</div>";
    echo "<p>Make sure database 'parking_system_db' exists.</p>";
}

echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>Run SQL in phpMyAdmin to create database tables</li>";
echo "<li>Create other PHP files (login.php, register.php, etc.)</li>";
echo "<li>Test the system</li>";
echo "</ol>";

echo '<a href="index.php" class="btn btn-primary">Go to Home Page</a>';
?>