<?php
require_once 'config/database.php';
$page_title = "Manage Users";

if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

// Display messages
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

// Handle actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];
    
    if ($action == 'delete') {
        // Check if user has reservations
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE user_id = ?");
        $stmt->execute([$id]);
        $res_count = $stmt->fetchColumn();
        
        if ($res_count == 0) {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND id != ?");
            $stmt->execute([$id, $_SESSION['user_id']]);
            $_SESSION['success'] = "User deleted successfully.";
        } else {
            $_SESSION['error'] = "Cannot delete user with active reservations.";
        }
        redirect('admin-users.php');
    }
    
    if ($action == 'toggle_role') {
        // Get current role
        $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        
        if ($user) {
            $new_role = ($user['role'] == 'admin') ? 'user' : 'admin';
            $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ? AND id != ?");
            $stmt->execute([$new_role, $id, $_SESSION['user_id']]);
            $_SESSION['success'] = "User role updated to " . $new_role . ".";
        }
        redirect('admin-users.php');
    }
}

// Get all users except current admin
$stmt = $pdo->prepare("SELECT * FROM users WHERE id != ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$users = $stmt->fetchAll();

// Get admin count
$admin_count = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
?>

<?php include 'includes/header.php'; ?>

<h2 class="mb-4"><i class="bi bi-people"></i> Manage Users</h2>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?php echo $success; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Registered Users (<?php echo count($users); ?>)</h5>
        <a href="admin.php" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Admin
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Vehicle</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="bi bi-people" style="font-size: 2rem;"></i>
                                <p class="mt-2">No users found</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $index => $user): 
                            // Get reservation count for this user
                            $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE user_id = ?");
                            $stmt->execute([$user['id']]);
                            $reservations = $stmt->fetchColumn();
                        ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                                <?php if ($reservations > 0): ?>
                                    <br><small class="text-muted"><?php echo $reservations; ?> reservation(s)</small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($user['vehicle_number'] ?? 'N/A'); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $user['role'] == 'admin' ? 'danger' : 'primary'; ?>">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" 
                                            data-bs-target="#userModal<?php echo $user['id']; ?>">
                                        <i class="bi bi-eye"></i> View
                                    </button>
                                    
                                    <a href="admin-users.php?action=toggle_role&id=<?php echo $user['id']; ?>" 
                                       class="btn btn-outline-warning"
                                       onclick="return confirm('Change role of <?php echo htmlspecialchars($user['name']); ?>?')">
                                        <i class="bi bi-arrow-repeat"></i> Role
                                    </a>
                                    
                                    <?php if ($reservations == 0): ?>
                                        <a href="admin-users.php?action=delete&id=<?php echo $user['id']; ?>" 
                                           class="btn btn-outline-danger"
                                           onclick="return confirm('Delete <?php echo htmlspecialchars($user['name']); ?>? This action cannot be undone.')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- User Details Modal -->
                                <div class="modal fade" id="userModal<?php echo $user['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">User Details</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <table class="table table-sm">
                                                    <tr><th>Name:</th><td><?php echo htmlspecialchars($user['name']); ?></td></tr>
                                                    <tr><th>Email:</th><td><?php echo htmlspecialchars($user['email']); ?></td></tr>
                                                    <tr><th>Phone:</th><td><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td></tr>
                                                    <tr><th>Vehicle:</th><td><?php echo htmlspecialchars($user['vehicle_number'] ?? 'N/A'); ?></td></tr>
                                                    <tr><th>Role:</th><td><?php echo ucfirst($user['role']); ?></td></tr>
                                                    <tr><th>Joined:</th><td><?php echo date('F d, Y H:i', strtotime($user['created_at'])); ?></td></tr>
                                                    <tr><th>Reservations:</th><td><?php echo $reservations; ?></td></tr>
                                                </table>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Summary -->
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6>Total Users</h6>
                        <h3><?php echo count($users); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6>Admins</h6>
                        <h3><?php echo $admin_count - 1; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6>Regular Users</h6>
                        <h3><?php echo count($users) - ($admin_count - 1); ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>