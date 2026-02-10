<?php
require_once __DIR__ . '/config/database.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Database Check
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            if ($user['role'] === 'admin') {
                redirect('admin.php');
            } else {
                redirect('dashboard.php');
            }
        } else {
            // Fallback for hardcoded demo (if DB is empty or fails)
            if ($email == 'admin@parking.com' && $password == 'admin123') {
                $_SESSION['user_id'] = 1;
                $_SESSION['name'] = 'Admin';
                $_SESSION['role'] = 'admin';
                redirect('admin.php');
            } else {
                $error = "Invalid email or password!";
            }
        }
    } catch (Exception $e) {
        $error = "System error: " . $e->getMessage();
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-lg border-0 rounded-lg mt-5">
                <div class="card-header bg-danger text-white text-center py-4">
                    <h3 class="mb-0 fw-bold">Login</h3>
                </div>
                <div class="card-body p-4">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="form-floating mb-3">
                            <input type="email" class="form-control" name="email" id="inputEmail" placeholder="name@example.com" required>
                            <label for="inputEmail">Email address</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="password" class="form-control" name="password" id="inputPassword" placeholder="Password" required>
                            <label for="inputPassword">Password</label>
                        </div>
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-danger btn-lg">Login</button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center py-3 bg-light">
                    <div class="small"><a href="register.php" class="text-decoration-none">Need an account? Sign up!</a></div>
                    <div class="small text-muted mt-2">Demo Admin: admin@parking.com / admin123</div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
