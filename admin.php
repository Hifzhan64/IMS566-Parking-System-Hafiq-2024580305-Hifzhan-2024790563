<?php
require_once __DIR__ . '/config/database.php';

// Access Control
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include __DIR__ . '/includes/admin-header.php';

// Fetch Stats
try {
    // Total Locations
    $stmt = $pdo->query("SELECT COUNT(*) FROM locations");
    $totalLocations = $stmt->fetchColumn();

    // Total Bookings
    $stmt = $pdo->query("SELECT COUNT(*) FROM reservations");
    $totalBookings = $stmt->fetchColumn();

    // Total Users
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role != 'admin'");
    $totalUsers = $stmt->fetchColumn();

    // Total Revenue (Confirmed/Completed)
    $stmt = $pdo->query("SELECT SUM(total_price) FROM reservations WHERE status IN ('confirmed', 'completed')");
    $totalRevenue = $stmt->fetchColumn() ?: 0;

} catch (Exception $e) {
    $totalLocations = $totalBookings = $totalUsers = $totalRevenue = 0;
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Dashboard</h1>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card card-stat bg-white text-dark h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                        <i class="fas fa-map-marker-alt fa-2x text-primary"></i>
                    </div>
                    <div>
                        <h6 class="card-subtitle mb-1 text-muted">Total Locations</h6>
                        <h2 class="card-title mb-0 fw-bold"><?php echo $totalLocations; ?></h2>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
             <div class="card card-stat bg-white text-dark h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 p-3 rounded-circle me-3">
                        <i class="fas fa-calendar-check fa-2x text-success"></i>
                    </div>
                     <div>
                        <h6 class="card-subtitle mb-1 text-muted">Total Bookings</h6>
                        <h2 class="card-title mb-0 fw-bold"><?php echo $totalBookings; ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
             <div class="card card-stat bg-white text-dark h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-warning bg-opacity-10 p-3 rounded-circle me-3">
                        <i class="fas fa-users fa-2x text-warning"></i>
                    </div>
                     <div>
                        <h6 class="card-subtitle mb-1 text-muted">Registered Users</h6>
                        <h2 class="card-title mb-0 fw-bold"><?php echo $totalUsers; ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
             <div class="card card-stat bg-white text-dark h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-danger bg-opacity-10 p-3 rounded-circle me-3">
                        <i class="fas fa-wallet fa-2x text-danger"></i>
                    </div>
                     <div>
                        <h6 class="card-subtitle mb-1 text-muted">Total Revenue</h6>
                        <h2 class="card-title mb-0 fw-bold">RM <?php echo number_format($totalRevenue, 2); ?></h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Bookings Table Preview -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0 fw-bold">Recent Bookings</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Location</th>
                            <th>Slot</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $pdo->query("
                            SELECT r.*, u.name as user_name, l.name as loc_name, s.slot_number 
                            FROM reservations r
                            LEFT JOIN users u ON r.user_id = u.id
                            LEFT JOIN parking_slots s ON r.slot_id = s.id
                            LEFT JOIN locations l ON s.location_id = l.id
                            ORDER BY r.created_at DESC LIMIT 5
                        ");
                        while($row = $stmt->fetch()):
                        ?>
                        <tr>
                            <td>#<?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['user_name'] ?? 'Guest'); ?></td>
                            <td><?php echo htmlspecialchars($row['loc_name'] ?? 'N/A'); ?></td>
                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($row['slot_number'] ?? 'N/A'); ?></span></td>
                            <td><?php echo date('d M Y', strtotime($row['entry_time'])); ?></td>
                            <td>
                                <?php if($row['status'] == 'confirmed'): ?>
                                    <span class="badge bg-success">Confirmed</span>
                                <?php elseif($row['status'] == 'pending'): ?>
                                    <span class="badge bg-warning text-dark">Pending</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><?php echo ucfirst($row['status']); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
             <div class="text-end mt-3">
                <a href="admin-reservations.php" class="btn btn-primary btn-sm">View All</a>
            </div>
        </div>
    </div>

</div>

<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
