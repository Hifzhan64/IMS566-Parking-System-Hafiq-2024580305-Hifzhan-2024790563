<?php
require_once __DIR__ . '/config/database.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reservation_id'])) {
    $reservation_id = $_POST['reservation_id'];
    $user_id = $_SESSION['user_id'];
    
    // Fetch reservation to ensure ownership and get details
    $stmt = $pdo->prepare("
        SELECT r.*, p.price_per_hour 
        FROM reservations r
        JOIN parking_slots p ON r.slot_id = p.id
        WHERE r.id = ? AND r.user_id = ? AND r.status IN ('active', 'confirmed')
    ");
    $stmt->execute([$reservation_id, $user_id]);
    $reservation = $stmt->fetch();
    
    if ($reservation) {
        $exit_time = date('Y-m-d H:i:s');
        $entry_time = $reservation['entry_time'];
        
        // Calculate duration and price
        $start_ts = strtotime($entry_time);
        $end_ts = strtotime($exit_time);
        $duration_hours = ceil(abs($end_ts - $start_ts) / 3600);
        // Minimum 1 hour charge
        if ($duration_hours < 1) $duration_hours = 1;
        
        $total_price = $duration_hours * $reservation['price_per_hour'];
        
        // Update Reservation
        $updateStmt = $pdo->prepare("
            UPDATE reservations 
            SET exit_time = ?, total_price = ?, status = 'completed' 
            WHERE id = ?
        ");
        $updateStmt->execute([$exit_time, $total_price, $reservation_id]);
        
        // Release Slot
        // Check availability logic: if we use 'occupied' status in slots table, update it.
        // Assuming slots table has status.
        $pdo->prepare("UPDATE parking_slots SET status = 'available' WHERE id = ?")
            ->execute([$reservation['slot_id']]);
        
        // Redirect to success/receipt
        $_SESSION['success'] = "Checkout successful! Total amount: RM " . number_format($total_price, 2);
        header("Location: booking_success.php?id=" . $reservation_id);
        exit;
    } else {
        echo "<script>alert('Invalid reservation or already completed.'); window.location='dashboard.php';</script>";
    }
} else {
    redirect('dashboard.php');
}
