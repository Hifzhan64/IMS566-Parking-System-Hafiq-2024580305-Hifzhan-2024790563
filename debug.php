<?php
require_once 'config/database.php';

echo "<h3>Database Connection Test</h3>";
echo "Connected to database: " . ($pdo ? "✅ Success" : "❌ Failed") . "<br>";

// Test query
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "Users in database: " . $result['count'] . "<br>";
} catch(PDOException $e) {
    echo "Query error: " . $e->getMessage() . "<br>";
}

// Check session
echo "<h3>Session Test</h3>";
echo "Session ID: " . session_id() . "<br>";
echo "Logged in: " . (isset($_SESSION['user_id']) ? "Yes" : "No") . "<br>";

// Check if table exists
$tables = $pdo->query("SHOW TABLES")->fetchAll();
echo "<h3>Database Tables:</h3>";
foreach ($tables as $table) {
    echo $table[0] . "<br>";
}
?>