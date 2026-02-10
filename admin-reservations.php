<?php
require_once __DIR__ . '/config/database.php';

// Access Control
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include __DIR__ . '/includes/admin-header.php';

// Fetch Reservations
$sql = "
    SELECT r.*, u.name as user_name, u.email as user_email, l.name as loc_name, s.slot_number 
    FROM reservations r
    LEFT JOIN users u ON r.user_id = u.id
    LEFT JOIN parking_slots s ON r.slot_id = s.id
    LEFT JOIN locations l ON s.location_id = l.id
    ORDER BY r.created_at DESC
";
$stmt = $pdo->query($sql);
$reservations = $stmt->fetchAll();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Reservations</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                <i class="fas fa-print"></i> Print Report
            </button>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Location</th>
                            <th>Slot</th>
                            <th>Entry</th>
                            <th>Exit</th>
                            <th>Price</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($reservations as $res): ?>
                        <tr>
                            <td>#<?php echo $res['id']; ?></td>
                            <td>
                                <div class="fw-bold"><?php echo htmlspecialchars($res['user_name'] ?? 'Guest'); ?></div>
                                <div class="text-muted small"><?php echo htmlspecialchars($res['user_email'] ?? '-'); ?></div>
                            </td>
                            <td><?php echo htmlspecialchars($res['loc_name'] ?? 'N/A'); ?></td>
                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($res['slot_number'] ?? '?'); ?></span></td>
                            <td><?php echo date('d M Y, h:i A', strtotime($res['entry_time'])); ?></td>
                            <td><?php echo date('d M Y, h:i A', strtotime($res['exit_time'])); ?></td>
                            <td class="fw-bold">RM <?php echo number_format($res['total_price'], 2); ?></td>
                            <td>
                                <?php 
                                $statusClass = match($res['status']) {
                                    'confirmed' => 'success',
                                    'pending' => 'warning',
                                    'cancelled' => 'danger',
                                    'completed' => 'primary',
                                    default => 'secondary'
                                };
                                ?>
                                <span class="badge bg-<?php echo $statusClass; ?> text-uppercase"><?php echo $res['status']; ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
