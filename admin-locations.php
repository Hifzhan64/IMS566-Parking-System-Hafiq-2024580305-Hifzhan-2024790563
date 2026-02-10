<?php
require_once __DIR__ . '/config/database.php';

// Access Control
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include __DIR__ . '/includes/admin-header.php';

// Fetch Locations with Slot Counts
$sql = "
    SELECT l.*, COUNT(s.id) as slot_count 
    FROM locations l 
    LEFT JOIN parking_slots s ON l.id = s.location_id 
    GROUP BY l.id 
    ORDER BY l.id DESC
";
$stmt = $pdo->query($sql);
$locations = $stmt->fetchAll();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Locations</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="admin-location-form.php" class="btn btn-sm btn-danger">
                <i class="fas fa-plus"></i> Add New Location
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Address</th>
                            <th>Slots</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($locations as $loc): ?>
                        <tr>
                            <td>#<?php echo $loc['id']; ?></td>
                            <td>
                                <?php if($loc['image_url']): ?>
                                    <img src="<?php echo htmlspecialchars($loc['image_url']); ?>" alt="" width="50" height="50" class="rounded object-fit-cover">
                                <?php else: ?>
                                    <div class="bg-secondary rounded text-white d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                        <i class="fas fa-image"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="fw-bold"><?php echo htmlspecialchars($loc['name']); ?></td>
                            <td class="text-muted small"><?php echo htmlspecialchars(substr($loc['address'], 0, 50)) . '...'; ?></td>
                            <td><span class="badge bg-info text-dark"><?php echo $loc['slot_count']; ?> Slots</span></td>
                            <td>
                                <a href="admin-location-form.php?id=<?php echo $loc['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                <a href="admin-location-delete.php?id=<?php echo $loc['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure? This will delete all associated slots too!');"><i class="fas fa-trash"></i></a>
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
