<?php
// Database configuration - duplicated here to allow connecting without DB first
$host = 'localhost';
$dbname = 'parking_system_db';
$username = 'root';
$password = '';

echo "<h1>Database Setup Diagnostic</h1>";
echo "<pre>";

try {
    // 1. Try to connect WITHOUT database selected to check/create DB
    echo "Attempting to connect to MySQL server...\n";
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected to MySQL server successfully.\n";

    // 2. Create Database if not exists
    echo "Creating database '$dbname' if it doesn't exist...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname`");
    echo "Database created/verified.\n";

    // 3. Connect to the specific database
    $pdo->exec("USE `$dbname`");
    echo "Switched to database '$dbname'.\n";

    // 4. Read and Split SQL file
    $sqlFile = __DIR__ . '/database.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("database.sql not found at " . $sqlFile);
    }
    
    $sqlContent = file_get_contents($sqlFile);
    
    // Remove comments to avoid issues with splitting
    $sqlContent = preg_replace('/--.*$/m', '', $sqlContent);
    
    // Split by semicolon
    $queries = array_filter(array_map('trim', explode(';', $sqlContent)));

    echo "Found " . count($queries) . " queries to execute.\n";

    foreach ($queries as $i => $query) {
        if (!empty($query)) {
            try {
                $pdo->exec($query);
                echo "Query " . ($i+1) . " executed successfully.\n";
            } catch (PDOException $e) {
                // Ignore "Table already exists" errors for robustness
                if (strpos($e->getMessage(), 'already exists') !== false) {
                     echo "Query " . ($i+1) . " skipped (Table exists).\n";
                } else {
                     echo "ERROR in Query " . ($i+1) . ": " . $e->getMessage() . "\n";
                     echo "Query: " . substr($query, 0, 100) . "...\n";
                }
            }
        }
    }

    echo "\n------------------------------------------------\n";
    echo "SETUP COMPLETED SUCCESSFULLY!\n";
    echo "You can now <a href='index.php'>Go to Homepage</a> or <a href='register.php'>Register</a>.";

} catch (PDOException $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
    if (strpos($e->getMessage(), 'Unknown database') !== false) {
        echo "It seems the database '$dbname' could not be created automatically.\n";
        echo "Please open phpMyAdmin and create the database '$dbname' manually.";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}

echo "</pre>";
?>
