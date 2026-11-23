<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/helpers.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';
$identifier = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $identifier = trim($_POST['identifier'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($identifier === '' || $password === '') {
        $error = 'Please enter both your username/email and password.';
    } else {
        $stmt = $pdo->prepare('SELECT id, username, password_hash, role FROM users WHERE email = ? OR username = ?');
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Regla de seguridad del PDF: regenerar ID de sesión tras login
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid credentials. Please try again.';
        }
    }
}

$pageTitle = 'Login';
require_once __DIR__ . '/../src/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h3 mb-3">Login</h1>

                <?php if (isset($_GET['registered'])): ?>
                    <div class="alert alert-success">Account created! You can now log in.</div>
                <?php endif; ?>

                <?php if (isset($_GET['logged_out'])): ?>
                    <div class="alert alert-info">You have logged out safely.</div>
                <?php endif; ?>

                <?php if ($error !== ''): ?>
                    <div class="alert alert-danger"><?= e($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="login.php">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

                    <div class="mb-3">
                        <label for="identifier" class="form-label">Username or Email</label>
                        <input type="text" id="identifier" name="identifier" class="form-control"
                               value="<?= e($identifier) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../src/footer.php'; ?>