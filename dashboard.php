<?php
require_once __DIR__ . '/config/database.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'];

// Get active reservations
// Note: schema uses 'entry_time', 'exit_time', 'total_price'
// status: 'pending', 'confirmed', 'completed', 'cancelled'
$stmt = $pdo->prepare("
    SELECT r.*, p.slot_number, l.name as location_name, l.image_url 
    FROM reservations r 
    JOIN parking_slots p ON r.slot_id = p.id 
    JOIN locations l ON p.location_id = l.id
    WHERE r.user_id = ? AND r.status IN ('pending', 'confirmed') 
    ORDER BY r.entry_time ASC
");
$stmt->execute([$user_id]);
$active_reservations = $stmt->fetchAll();

// Get statistics
$active_count = count($active_reservations);
$current_time = date('Y-m-d H:i:s');
$available_slots = $pdo->query("
    SELECT COUNT(*) 
    FROM parking_slots p 
    WHERE p.status = 'available' 
    AND p.id NOT IN (
        SELECT r.slot_id 
        FROM reservations r 
        WHERE r.status IN ('confirmed', 'active', 'pending')
        AND (
            (r.exit_time IS NULL) OR
            (r.entry_time <= NOW() AND r.exit_time > NOW())
        )
    )
")->fetchColumn();

// Get user info
$stmt = $pdo->prepare("SELECT email, phone FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user_info = $stmt->fetch();
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<div class="container py-5">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h2 class="fw-bold">Welcome back, <span class="text-danger"><?php echo htmlspecialchars($user_name); ?></span>!</h2>
            <p class="text-muted">Manage your bookings and profile here.</p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="index.php" class="btn btn-danger"><i class="fas fa-plus-circle"></i> New Booking</a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-danger text-white rounded-circle p-3 me-3">
                        <i class="fas fa-ticket-alt fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="card-title text-muted mb-0">Active Bookings</h5>
                        <h2 class="mb-0 fw-bold"><?php echo $active_count; ?></h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
             <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-success text-white rounded-circle p-3 me-3">
                        <i class="fas fa-parking fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="card-title text-muted mb-0">Total Available Slots</h5>
                        <h2 class="mb-0 fw-bold"><?php echo $available_slots; ?></h2>
                    </div>
                </div>
            </div>
        </div>
         <div class="col-md-4">
             <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-primary text-white rounded-circle p-3 me-3">
                        <i class="fas fa-user fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="card-title text-muted mb-0">My Account</h5>
                        <small class="d-block text-muted"><?php echo htmlspecialchars($user_info['email']); ?></small>
                        <small class="d-block text-muted"><?php echo htmlspecialchars($user_info['phone'] ?? 'No phone'); ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Reservations Table -->
    <div class="card border-0 shadow-lg">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold"><i class="fas fa-list text-danger"></i> Your Reservations</h5>
        </div>
        <div class="card-body p-0">
             <?php if (empty($active_reservations)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                    <h4>No active reservations found.</h4>
                    <a href="index.php" class="btn btn-primary mt-2">Book a Spot Now</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Location</th>
                                <th>Slot</th>
                                <th>Dates</th>
                                <th>Status</th>
                                <th>Total</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($active_reservations as $res): ?>
                            <tr>
                                <td class="ps-4 fw-bold">
                                    <i class="fas fa-map-marker-alt text-danger me-2"></i>
                                    <?php echo htmlspecialchars($res['location_name']); ?>
                                </td>
                                <td><span class="badge bg-dark"><?php echo $res['slot_number']; ?></span></td>
                                <td>
                                    <div class="small">
                                        <div class="text-success"><i class="fas fa-arrow-right"></i> <?php echo date('M d, H:i', strtotime($res['entry_time'])); ?></div>
                                        <div class="text-danger"><i class="fas fa-arrow-left"></i> <?php echo $res['exit_time'] ? date('M d, H:i', strtotime($res['exit_time'])) : 'Ongoing'; ?></div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $res['status'] == 'confirmed' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($res['status']); ?>
                                    </span>
                                </td>
                                <td class="fw-bold"><?php echo $res['total_price'] ? 'RM ' . number_format($res['total_price'], 2) : 'Pay on Exit'; ?></td>
                                <td>
                                    <form action="checkout.php" method="POST" style="display:inline;" onsubmit="return confirm('Check out now? This will calculate final price and end reservation.')">
                                        <input type="hidden" name="reservation_id" value="<?php echo $res['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="fas fa-sign-out-alt"></i> Check Out
                                        </button>
                                    </form>
                                    <a href="reservation_details.php?id=<?php echo $res['id']; ?>" class="btn btn-sm btn-outline-danger">Details/Cancel</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
