<?php
require_once __DIR__ . '/config/database.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reservation_id = $_POST['reservation_id'];
    $user_id = $_SESSION['user_id'];

    // 1. Verify Ownership
    $stmt = $pdo->prepare("SELECT id FROM reservations WHERE id = ? AND user_id = ?");
    $stmt->execute([$reservation_id, $user_id]);
    
    if ($stmt->fetch()) {
        // 2. Update Status
        $stmt = $pdo->prepare("UPDATE reservations SET status = 'cancelled' WHERE id = ?");
        $stmt->execute([$reservation_id]);
        
        // Redirect back with success
        echo "<script>alert('Reservation cancelled successfully.'); window.location.href='dashboard.php';</script>";
    } else {
        echo "<script>alert('Error: Invalid reservation.'); window.location.href='dashboard.php';</script>";
    }
} else {
    redirect('dashboard.php');
}
?>
