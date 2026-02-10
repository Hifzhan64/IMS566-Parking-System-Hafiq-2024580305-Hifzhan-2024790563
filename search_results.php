<?php
require_once __DIR__ . '/config/database.php';
$page_title = "Search Results";

// Get search parameters
$location_id = $_GET['location'] ?? '';
$entry_date = $_GET['entry_date'] ?? date('Y-m-d');
$entry_time = $_GET['entry_time'] ?? date('H:i');
$entry_time = $_GET['entry_time'] ?? date('H:i');

// Combine to datetime
$entry_datetime = $entry_date . ' ' . $entry_time;

// Duration/Price calculation removed as it's now pay-per-hour upon exit

// 1. Get Location Details
$stmt = $pdo->prepare("SELECT * FROM locations WHERE id = ?");
$stmt->execute([$location_id]);
$location = $stmt->fetch();

if (!$location) {
    // If no location selected, just fetch all or redirect. For now, redirect.
    // header("Location: index.php"); // Optional
}

// 2. Find Available Slots
// Logic: Select slots in this location that are NOT booked at the requested entry_time.
// New Logic: 
// - If existing reservation has NO exit_time (ongoing), it blocks the slot.
// - If existing reservation has exit_time, check if it overlaps our entry_time.
$sql = "
    SELECT p.*, l.name as location_name 
    FROM parking_slots p
    JOIN locations l ON p.location_id = l.id
    WHERE p.location_id = ? 
    AND p.status = 'available'
    AND p.id NOT IN (
        SELECT r.slot_id 
        FROM reservations r 
        WHERE r.status IN ('pending', 'confirmed')
        AND (
            (r.exit_time IS NULL) OR  -- Ongoing reservation
            (r.entry_time <= ? AND r.exit_time >= ?) -- Overlaps our entry time
        )
    )
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    $location_id, 
    $entry_datetime, $entry_datetime
]);
$available_slots = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Search Results</li>
                </ol>
            </nav>
            <h2 class="fw-bold">Available Spaces at <span class="text-danger"><?php echo htmlspecialchars($location['name'] ?? 'Selected Location'); ?></span></h2>
            <div class="alert alert-light border shadow-sm">
                <div class="row text-center">
                    <div class="col-md-4 border-end">
                        <small class="text-muted">Entry</small><br>
                        <strong><?php echo date('M d, Y H:i', strtotime($entry_datetime)); ?></strong>
                    </div>
                     <div class="col-md-4 border-end">
                        <small class="text-muted">Mode</small><br>
                        <strong>Open-Ended (Pay on Exit)</strong>
                    </div>
                     <div class="col-md-4">
                        <a href="index.php" class="btn btn-sm btn-outline-secondary mt-2">Change Search</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <?php if (empty($available_slots)): ?>
            <div class="col-md-12 text-center py-5">
                <div class="display-1 text-muted"><i class="fas fa-parking"></i></div>
                <h3 class="mt-3">No Parking Spots Available</h3>
                <p class="text-muted">Sorry, all spots at this location are booked for the selected time.</p>
                <a href="index.php" class="btn btn-primary">Try Another Time/Location</a>
            </div>
        <?php else: ?>
            <?php foreach ($available_slots as $slot): 
            ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm border-0 hover-effect">
                    <div class="card-header bg-white border-bottom-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                         <span class="badge bg-dark rounded-pill px-3 py-2">Slot <?php echo $slot['slot_number']; ?></span>
                         <?php if ($slot['is_covered']): ?>
                             <span class="text-muted small"><i class="fas fa-umbrella"></i> Covered</span>
                         <?php else: ?>
                             <span class="text-muted small"><i class="fas fa-sun"></i> Open Air</span>
                         <?php endif; ?>
                    </div>
                    <div class="card-body px-4">
                        <div class="text-center py-3">
                             <h4 class="fw-bold text-success mb-0">RM <?php echo number_format($slot['price_per_hour'], 2); ?>/hr</h4>
                             <small class="text-muted">Hourly Rate</small>
                        </div>
                        <ul class="list-unstyled text-muted small mt-3">
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Instant Booking</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i> CCTV Surveillance</li>
                            <li><i class="fas fa-check text-success me-2"></i> 24/7 Access</li>
                        </ul>
                    </div>
                    <div class="card-footer bg-white border-top-0 px-4 pb-4">
                        <form action="booking_confirmation.php" method="POST">
                            <input type="hidden" name="slot_id" value="<?php echo $slot['id']; ?>">
                            <input type="hidden" name="entry_time" value="<?php echo $entry_datetime; ?>">
                            
                            <button type="submit" class="btn btn-danger w-100 fw-bold py-2">BOOK NOW</button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<style>
    .hover-effect:hover {
        transform: translateY(-5px);
        transition: transform 0.3s ease;
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>
