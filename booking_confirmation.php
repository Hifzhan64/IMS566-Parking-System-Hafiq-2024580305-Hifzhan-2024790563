<?php
require_once __DIR__ . '/config/database.php';

// Check login
if (!isLoggedIn()) {
    // Store intended Booking details in session to redirect back after login? 
    // For simplicity, redirect to login with a message or just login.
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    echo "<script>alert('Please login to complete your booking.'); window.location='login.php';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

$user_id = $_SESSION['user_id'];
$slot_id = $_POST['slot_id'];
$entry_time = $_POST['entry_time'];
// exit_time and total_price are calculated upon checkout

// Double check availability (Race condition check)
// ... (Skipping complex race check for assignment level, but good to note)

// Create Reservation
try {
    $stmt = $pdo->prepare("
        INSERT INTO reservations (user_id, slot_id, entry_time, exit_time, total_price, status) 
        VALUES (?, ?, ?, NULL, NULL, 'confirmed')
    ");
    $stmt->execute([$user_id, $slot_id, $entry_time]);
    
    $reservation_id = $pdo->lastInsertId();
    
    // Redirect to Success/Receipt page
    header("Location: booking_success.php?id=" . $reservation_id);
    exit();

} catch (PDOException $e) {
    die("Booking Error: " . $e->getMessage());
}
?>
