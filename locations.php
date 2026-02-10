<?php
require_once __DIR__ . '/config/database.php';
$page_title = "Our Locations";
include __DIR__ . '/includes/header.php';

// Fetch all locations with available slot count (Real-time check)
// logic: count slots that are 'available' AND not currently in an active reservation window
$current_time = date('Y-m-d H:i:s');
$stmt = $pdo->query("
    SELECT l.*, 
    (
        SELECT COUNT(*) 
        FROM parking_slots p 
        WHERE p.location_id = l.id 
        AND p.status = 'available'
        AND p.id NOT IN (
            SELECT r.slot_id 
            FROM reservations r 
            WHERE r.status IN ('confirmed', 'active', 'pending')
            AND r.entry_time <= NOW() 
            AND r.exit_time > NOW()
        )
    ) as available_slots 
    FROM locations l
");
$locations = $stmt->fetchAll();
?>

<div class="container py-5">
    <div class="text-center mb-5">
        <h2 class="fw-bold">Our Locations</h2>
        <p class="text-muted">Convenient parking spots across Johor Bahru</p>
    </div>

    <div class="row">
        <?php foreach($locations as $loc): ?>
        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="row g-0">
                    <div class="col-md-4">
                        <?php if(!empty($loc['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($loc['image_url']); ?>" class="img-fluid rounded-start h-100" style="object-fit: cover; min-height: 200px;" alt="<?php echo htmlspecialchars($loc['name']); ?>">
                        <?php else: ?>
                            <div class="bg-secondary text-white d-flex align-items-center justify-content-center h-100" style="min-height: 200px;">
                                <i class="fas fa-parking fa-3x"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-8">
                        <div class="card-body">
                            <h5 class="card-title fw-bold"><?php echo htmlspecialchars($loc['name']); ?></h5>
                            <p class="card-text text-muted small"><i class="fas fa-map-marker-alt text-danger"></i> <?php echo htmlspecialchars($loc['address']); ?></p>
                            <p class="card-text"><?php echo htmlspecialchars($loc['description'] ?? ''); ?></p>
                            
                            <!-- Check Availability Button triggers Modal -->
                            <button onclick="showAvailability('<?php echo addslashes($loc['name']); ?>', <?php echo $loc['available_slots']; ?>)" class="btn btn-sm btn-outline-danger">
                                Check Availability
                            </button>
                             <a href="index.php?location=<?php echo $loc['id']; ?>" class="btn btn-sm btn-danger ms-2">Book Now</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Availability Modal -->
<div class="modal fade" id="availabilityModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold" id="modalLocationName">Location Name</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center py-4">
        <div class="display-1 fw-bold text-success mb-2" id="modalSlotCount">0</div>
        <p class="text-muted text-uppercase fw-bold">Available Slots</p>
        <div id="availabilityMessage" class="mt-3"></div>
      </div>
      <div class="modal-footer border-0 justify-content-center">
        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Close</button>
        <a href="index.php" class="btn btn-danger px-4">Book Now</a>
      </div>
    </div>
  </div>
</div>

<script>
function showAvailability(name, count) {
    // Set Name
    document.getElementById('modalLocationName').innerText = name;
    
    // Set Count
    const countEl = document.getElementById('modalSlotCount');
    countEl.innerText = count;
    
    // Optional: Color coding
    if(count > 10) {
        countEl.className = 'display-1 fw-bold text-success mb-2';
    } else if (count > 0) {
        countEl.className = 'display-1 fw-bold text-warning mb-2';
    } else {
        countEl.className = 'display-1 fw-bold text-danger mb-2';
    }

    // Show Modal
    var myModal = new bootstrap.Modal(document.getElementById('availabilityModal'));
    myModal.show();
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
