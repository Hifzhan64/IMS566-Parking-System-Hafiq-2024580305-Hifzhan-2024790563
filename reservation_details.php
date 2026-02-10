<?php
require_once __DIR__ . '/config/database.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$reservation_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Fetch Reservation Details
$stmt = $pdo->prepare("
    SELECT r.*, p.slot_number, l.name as location_name, l.address, l.image_url
    FROM reservations r 
    JOIN parking_slots p ON r.slot_id = p.id 
    JOIN locations l ON p.location_id = l.id
    WHERE r.id = ? AND r.user_id = ?
");
$stmt->execute([$reservation_id, $user_id]);
$res = $stmt->fetch();

if (!$res) {
    echo "Reservation not found or access denied.";
    exit;
}

$can_cancel = in_array($res['status'], ['pending', 'confirmed']);

include __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Reservation #<?php echo $res['id']; ?></li>
                </ol>
            </nav>

            <div class="card shadow border-0 overflow-hidden">
                <div class="row g-0">
                    <div class="col-md-4 bg-light d-flex align-items-center justify-content-center p-3">
                        <div class="text-center">
                            <i class="fas fa-parking fa-5x text-secondary"></i>
                            <h5 class="mt-3 text-muted"><?php echo htmlspecialchars($res['location_name']); ?></h5>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h4 class="card-title fw-bold">Reservation Details</h4>
                                <span class="badge bg-<?php echo $res['status'] == 'confirmed' ? 'success' : ($res['status'] == 'cancelled' ? 'secondary' : 'warning'); ?> fs-6">
                                    <?php echo ucfirst($res['status']); ?>
                                </span>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-6">
                                    <small class="text-muted text-uppercase">Slot Number</small>
                                    <div class="fw-bold fs-5"><?php echo $res['slot_number']; ?></div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted text-uppercase">Total Price</small>
                                    <div class="fw-bold fs-5 text-success">$<?php echo number_format($res['total_price'], 2); ?></div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted text-uppercase">Entry Time</small>
                                    <div><i class="fas fa-clock text-primary me-1"></i> <?php echo date('M d, Y H:i', strtotime($res['entry_time'])); ?></div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted text-uppercase">Exit Time</small>
                                    <div><i class="fas fa-clock text-danger me-1"></i> <?php echo date('M d, Y H:i', strtotime($res['exit_time'])); ?></div>
                                </div>
                                 <div class="col-12">
                                    <small class="text-muted text-uppercase">Location Address</small>
                                    <div><i class="fas fa-map-marker-alt text-danger me-1"></i> <?php echo htmlspecialchars($res['address']); ?></div>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <?php if ($can_cancel): ?>
                                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                                        <i class="fas fa-times-circle"></i> Cancel Reservation
                                    </button>
                                <?php endif; ?>
                                <a href="receipt.php?id=<?php echo $res['id']; ?>" class="btn btn-dark" target="_blank"><i class="fas fa-file-pdf"></i> Download Receipt</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Cancellation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to cancel this reservation? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <form action="cancel_reservation.php" method="POST">
                    <input type="hidden" name="reservation_id" value="<?php echo $res['id']; ?>">
                    <button type="submit" class="btn btn-danger">Yes, Cancel It</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
