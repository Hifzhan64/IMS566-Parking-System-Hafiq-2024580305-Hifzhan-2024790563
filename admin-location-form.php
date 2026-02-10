<?php
require_once __DIR__ . '/config/database.php';

// Access Control
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$location = null;
$isEdit = false;

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM locations WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $location = $stmt->fetch();
    if ($location) $isEdit = true;
}

include __DIR__ . '/includes/admin-header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?php echo $isEdit ? 'Edit Location' : 'Add New Location'; ?></h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="admin-locations.php" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form action="admin-location-save.php" method="POST" enctype="multipart/form-data">
                        <?php if($isEdit): ?>
                            <input type="hidden" name="id" value="<?php echo $location['id']; ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Location Name</label>
                            <input type="text" class="form-control" name="name" required value="<?php echo $location['name'] ?? ''; ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Address</label>
                            <textarea class="form-control" name="address" rows="2" required><?php echo $location['address'] ?? ''; ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Description</label>
                            <textarea class="form-control" name="description" rows="3"><?php echo $location['description'] ?? ''; ?></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Image</label>
                            <?php if($isEdit && !empty($location['image_url'])): ?>
                                <div class="mb-2">
                                    <img src="<?php echo $location['image_url']; ?>" alt="Current" class="img-thumbnail" style="height: 150px;">
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" name="image" accept="image/*">
                            <div class="form-text">Leave empty to keep current image.</div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-danger btn-lg">
                                <?php echo $isEdit ? 'Update Location' : 'Create Location'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
