<?php
require_once __DIR__ . '/config/database.php';

// Access Control
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? null;
    $name = trim($_POST['name']);
    $address = trim($_POST['address']);
    $description = trim($_POST['description']);
    
    // Handle Image Upload
    $imageUrl = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $uploadDir = 'assets/images/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        
        $filename = uniqid() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $filename;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $imageUrl = $targetPath;
        }
    }

    try {
        if ($id) {
            // Update
            $sql = "UPDATE locations SET name = ?, address = ?, description = ?";
            $params = [$name, $address, $description];
            
            if ($imageUrl) {
                $sql .= ", image_url = ?";
                $params[] = $imageUrl;
            }
            
            $sql .= " WHERE id = ?";
            $params[] = $id;
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
        } else {
            // Create
            $stmt = $pdo->prepare("INSERT INTO locations (name, address, description, image_url) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $address, $description, $imageUrl]);
        }
        
        header("Location: admin-locations.php");
        exit;
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
}
