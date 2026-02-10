<?php
require_once __DIR__ . '/config/database.php';
$page_title = "My Reservations";

if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Handle cancellation
if (isset($_GET['cancel'])) {
    $reservation_id = intval($_GET['cancel']);
    
    // Verify ownership
    $stmt = $pdo->prepare("SELECT * FROM reservations WHERE id = ? AND user_id = ?");
    $stmt->execute([$reservation_id, $user_id]);
    $reservation = $stmt->fetch();
    
    if ($reservation) {
        // Update reservation status
        $stmt = $pdo->prepare("UPDATE reservations SET status = 'cancelled' WHERE id = ?");
        $stmt->execute([$reservation_id]);
        
        // Update slot status back to available
        $stmt = $pdo->prepare("UPDATE parking_slots SET status = 'available' WHERE id = ?");
        $stmt->execute([$reservation['slot_id']]);
        
        $_SESSION['success'] = "Reservation cancelled successfully.";
    }
    redirect('my-reservations.php');
}

// Get all reservations
$stmt = $pdo->prepare("
    SELECT r.*, p.slot_number, p.zone, p.slot_type 
    FROM reservations r 
    JOIN parking_slots p ON r.slot_id = p.id 
    WHERE r.user_id = ? 
    ORDER BY r.created_at DESC
");
$stmt->execute([$user_id]);
$reservations = $stmt->fetchAll();
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<h2 class="mb-4"><i class="bi bi-clock-history"></i> My Reservations</h2>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">All Reservations (<?php echo count($reservations); ?>)</h5>
        <a href="dashboard.php" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
    </div>
    <div class="card-body">
        <?php if (empty($reservations)): ?>
            <div class="text-center py-5">
                <i class="bi bi-calendar-x" style="font-size: 3rem; color: #6c757d;"></i>
                <h4 class="mt-3">No Reservations Found</h4>
                <p class="text-muted">You haven't made any reservations yet.</p>
                <a href="reserve.php" class="btn btn-primary">Make Your First Reservation</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Parking Slot</th>
                            <th>Zone</th>
                            <th>Vehicle</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Cost</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservations as $index => $res): 
                            $status_colors = [
                                'active' => 'success',
                                'completed' => 'secondary',
                                'cancelled' => 'danger'
                            ];
                        ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo $res['slot_number']; ?> (<?php echo $res['slot_type']; ?>)</td>
                            <td><?php echo $res['zone']; ?></td>
                            <td><?php echo $res['vehicle_number']; ?></td>
                            <td><?php echo date('M d, H:i', strtotime($res['start_time'])); ?></td>
                            <td><?php echo date('M d, H:i', strtotime($res['entry_time'])); ?></td>
                            <td><?php echo $res['exit_time'] ? date('M d, H:i', strtotime($res['exit_time'])) : 'Ongoing'; ?></td>
                            <td><?php echo $res['total_price'] ? 'RM ' . number_format($res['total_price'], 2) : 'Pay on Exit'; ?></td>
                            <td>
                                <span class="badge bg-<?php echo $status_colors[$res['status']]; ?>">
                                    <?php echo ucfirst($res['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="receipt.php?id=<?php echo $res['id']; ?>" 
                                       target="_blank" class="btn btn-outline-info">
                                        <i class="bi bi-receipt"></i> Receipt
                                    </a>
                                    <?php if ($res['status'] == 'active' || $res['status'] == 'confirmed'): ?>
                                        <!-- Checkout Form/Button -->
                                        <form action="checkout.php" method="POST" style="display:inline;" onsubmit="return confirm('Check out now? This will calculate final price and end reservation.')">
                                            <input type="hidden" name="reservation_id" value="<?php echo $res['id']; ?>">
                                            <button type="submit" class="btn btn-outline-success">
                                                <i class="bi bi-box-arrow-right"></i> Check Out
                                            </button>
                                        </form>
                                        
                                        <a href="my-reservations.php?cancel=<?php echo $res['id']; ?>" 
                                           class="btn btn-outline-danger"
                                           onclick="return confirm('Cancel this reservation?')">
                                            <i class="bi bi-x-circle"></i> Cancel
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>