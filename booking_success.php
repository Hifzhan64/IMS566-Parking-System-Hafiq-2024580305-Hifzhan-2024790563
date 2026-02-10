<?php
require_once __DIR__ . '/config/database.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$reservation_id = $_GET['id'] ?? 0;

// Fetch Reservation Details
$stmt = $pdo->prepare("
    SELECT r.*, p.slot_number, l.name as location_name, l.address 
    FROM reservations r
    JOIN parking_slots p ON r.slot_id = p.id
    JOIN locations l ON p.location_id = l.id
    WHERE r.id = ? AND r.user_id = ?
");
$stmt->execute([$reservation_id, $_SESSION['user_id']]);
$res = $stmt->fetch();

if (!$res) {
    echo "Reservation not found.";
    exit;
}

include __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow border-0 text-center p-4">
                <div class="mb-4 text-success">
                    <i class="fas fa-check-circle fa-5x"></i>
                </div>
                <h2 class="fw-bold mb-3">Booking Confirmed!</h2>
                <p class="text-muted">Your parking space has been successfully reserved.</p>
                
                <hr>
                
                <div class="row text-start mt-4">
                     <div class="col-md-6 mb-3">
                        <small class="text-muted d-block uppercase">Location</small>
                        <strong><?php echo htmlspecialchars($res['location_name']); ?></strong>
                    </div>
                     <div class="col-md-6 mb-3">
                        <small class="text-muted d-block uppercase">Slot Number</small>
                        <span class="badge bg-dark fs-6"><?php echo $res['slot_number']; ?></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <small class="text-muted d-block uppercase">Entry Time</small>
                        <strong><?php echo date('d M Y, h:i A', strtotime($res['entry_time'])); ?></strong>
                    </div>
                    <div class="col-md-6 mb-3">
                        <small class="text-muted d-block uppercase">Exit Time</small>
                        <strong><?php echo $res['exit_time'] ? date('d M Y, h:i A', strtotime($res['exit_time'])) : 'Ongoing (Open-ended)'; ?></strong>
                    </div>
                     <div class="col-md-12 text-center bg-light p-3 rounded">
                         <span class="text-muted">Total Amount Paid</span>
                         <h3 class="fw-bold text-success mb-0"><?php echo $res['total_price'] ? 'RM ' . number_format($res['total_price'], 2) : 'Pay on Exit'; ?></h3>
                     </div>
                </div>
                
                <div class="d-grid gap-2 mt-4">
                    <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
                    <a href="receipt.php?id=<?php echo $reservation_id; ?>" target="_blank" class="btn btn-outline-secondary">Download Receipt (PDF)</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
