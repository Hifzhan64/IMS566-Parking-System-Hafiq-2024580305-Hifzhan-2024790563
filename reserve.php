<?php
require_once __DIR__ . '/config/database.php';
$page_title = "Reserve Parking";

if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Get search parameters
$start_time = $_GET['start_time'] ?? date('Y-m-d H:i');
$vehicle = $_GET['vehicle'] ?? '';
$zone = $_GET['zone'] ?? '';

// Find available slots
$sql = "SELECT * FROM parking_slots WHERE status = 'available'";
$params = [];

if (!empty($zone)) {
    $sql .= " AND zone = ?";
    $params[] = $zone;
}

// Check for overlapping reservations (Open-ended logic)
// New Logic: 
// - If existing reservation has NO exit_time (ongoing), it blocks the slot.
// - If existing reservation has exit_time, check if it overlaps our start_time.
// - Effectively, check if slot is free at start_time.
$sql .= " AND id NOT IN (
    SELECT slot_id FROM reservations 
    WHERE status IN ('active', 'confirmed', 'pending')
    AND (
        (exit_time IS NULL) OR
        (entry_time <= ? AND exit_time > ?)
    )
)";

$params = array_merge($params, [$start_time, $start_time]);
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$available_slots = $stmt->fetchAll();

// Handle reservation submission
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reserve_slot'])) {
    $slot_id = $_POST['slot_id'];
    
    // Get slot details
    $stmt = $pdo->prepare("SELECT * FROM parking_slots WHERE id = ? AND status = 'available'");
    $stmt->execute([$slot_id]);
    $slot = $stmt->fetch();
    
    if ($slot) {
        // Create reservation
        $stmt = $pdo->prepare("
            INSERT INTO reservations (user_id, slot_id, vehicle_number, entry_time, exit_time, total_price, status) 
            VALUES (?, ?, ?, ?, NULL, NULL, 'confirmed')
        ");
        
        if ($stmt->execute([$user_id, $slot_id, $vehicle, $start_time])) {
            // Update slot status
            $pdo->prepare("UPDATE parking_slots SET status = 'occupied' WHERE id = ?")->execute([$slot_id]);
            
            $success = "Reservation successful! Slot {$slot['slot_number']} has been reserved.";
            // Redirect to success page or show message
        } else {
            $error = "Failed to create reservation. Please try again.";
        }
    } else {
        $error = "Selected slot is no longer available.";
    }
}
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-p-square"></i> Reserve Parking Slot</h2>
    <a href="dashboard.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to Dashboard
    </a>
</div>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?php echo $success; ?>
        <div class="mt-3">
            <a href="dashboard.php" class="btn btn-primary me-2">Go to Dashboard</a>
            <a href="my-reservations.php" class="btn btn-outline-primary">View My Reservations</a>
        </div>
    </div>
    <?php include __DIR__ . '/includes/footer.php'; exit(); ?>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?php echo $error; ?>
    </div>
<?php endif; ?>

<!-- Reservation Summary -->
<div class="card mb-4">
    <div class="card-body">
        <h5 class="card-title"><i class="bi bi-info-circle"></i> Reservation Details</h5>
        <div class="row">
            <div class="col-md-3">
                <p><strong>Start Time:</strong><br>
                <?php echo date('M d, Y H:i', strtotime($start_time)); ?></p>
            </div>
            <div class="col-md-3">
                <p><strong>Duration:</strong><br>
                Open-Ended</p>
            </div>
            <div class="col-md-3">
                <p><strong>End Time:</strong><br>
                Pay on Exit</p>
            </div>
            <div class="col-md-3">
                <p><strong>Vehicle:</strong><br>
                <?php echo htmlspecialchars($vehicle); ?></p>
            </div>
        </div>
        <a href="dashboard.php" class="btn btn-sm btn-outline-secondary">Change Details</a>
    </div>
</div>

<h4 class="mb-3">Available Parking Slots</h4>

<?php if (empty($available_slots)): ?>
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle"></i> No available slots found for the selected time and criteria.
        <div class="mt-2">
            <a href="dashboard.php" class="btn btn-sm btn-outline-warning">Try Different Time</a>
        </div>
    </div>
<?php else: ?>
    <div class="row">
        <?php foreach ($available_slots as $slot): 
            $slot_type_colors = [
                'regular' => 'primary',
                'premium' => 'warning',
                'ev' => 'success',
                'disabled' => 'info'
            ];
            
            $slot_type_icons = [
                'regular' => 'bi-car-front',
                'premium' => 'bi-star',
                'ev' => 'bi-lightning-charge',
                'disabled' => 'bi-wheelchair'
            ];
            
            $total_cost = $hours * $slot['hourly_rate'];
        ?>
        <div class="col-md-4 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h5 class="card-title mb-0">
                            <span class="badge bg-<?php echo $slot_type_colors[$slot['slot_type']]; ?>">
                                <i class="bi <?php echo $slot_type_icons[$slot['slot_type']]; ?>"></i>
                                <?php echo $slot['slot_number']; ?>
                            </span>
                        </h5>
                        <span class="badge bg-secondary"><?php echo $slot['zone']; ?></span>
                    </div>
                    
                        <strong>Type:</strong> <?php echo ucfirst($slot['slot_type'] ?? 'Standard'); ?><br>
                        <strong>Rate:</strong> RM <?php echo $slot['hourly_rate'] ?? $slot['price_per_hour']; ?>/hour<br>
                        <strong>Duration:</strong> Open-Ended<br>
                        <strong class="text-success">Pay on Exit</strong>
                    </p>
                    
                    <form method="POST">
                        <input type="hidden" name="slot_id" value="<?php echo $slot['id']; ?>">
                        <input type="hidden" name="start_time" value="<?php echo $start_time; ?>">
                        <input type="hidden" name="vehicle" value="<?php echo htmlspecialchars($vehicle); ?>">
                        <button type="submit" name="reserve_slot" class="btn btn-success w-100">
                            <i class="bi bi-check-circle"></i> Reserve This Slot
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>