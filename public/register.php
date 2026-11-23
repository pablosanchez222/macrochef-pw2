<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/helpers.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$errors = [];
$username = '';
$email = '';
$role = 'user';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'user';

    if (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username)) {
        $errors['username'] = 'Username must be 3-30 characters (letters, numbers, underscore only).';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 120) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters long.';
    }

    if ($password !== $confirmPassword) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    // Seguridad: solo permitimos registrarse como 'user' o 'student' (nunca 'admin')
    if (!in_array($role, ['user', 'student'], true)) {
        $errors['role'] = 'Invalid account plan selected.';
    }

    // Comprobar si el usuario o el correo ya existen
    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $errors['general'] = 'Username or email is already registered.';
        }
    }

    // Si todo es válido, encriptar contraseña y guardar en MySQL
    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)');
        $stmt->execute([$username, $email, $hash, $role]);

        header('Location: login.php?registered=1');
        exit;
    }
}

$pageTitle = 'Register';
require_once __DIR__ . '/../src/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h3 mb-3">Create an Account</h1>

                <?php if (!empty($errors['general'])): ?>
                    <div class="alert alert-danger"><?= e($errors['general']) ?></div>
                <?php endif; ?>

                <form method="POST" action="register.php">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" id="username" name="username"
                               class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>"
                               value="<?= e($username) ?>" required minlength="3" maxlength="30">
                        <?php if (isset($errors['username'])): ?>
                            <div class="invalid-feedback"><?= e($errors['username']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email"
                               class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                               value="<?= e($email) ?>" required maxlength="120">
                        <?php if (isset($errors['email'])): ?>
                            <div class="invalid-feedback"><?= e($errors['email']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label">Account Plan (Simulated Membership)</label>
                        <select id="role" name="role" class="form-select <?= isset($errors['role']) ? 'is-invalid' : '' ?>">
                            <option value="user" <?= $role === 'user' ? 'selected' : '' ?>>
                                Fitness / Regular Plan (9.99 EUR/month - Free Demo)
                            </option>
                            <option value="student" <?= $role === 'student' ? 'selected' : '' ?>>
                                Student Plan - 50% Student Discount (4.99 EUR/month - Free Demo)
                            </option>
                        </select>
                        <?php if (isset($errors['role'])): ?>
                            <div class="invalid-feedback"><?= e($errors['role']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password (min 8 characters)</label>
                        <input type="password" id="password" name="password"
                               class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                               required minlength="8">
                        <?php if (isset($errors['password'])): ?>
                            <div class="invalid-feedback"><?= e($errors['password']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password"
                               class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>"
                               required minlength="8">
                        <?php if (isset($errors['confirm_password'])): ?>
                            <div class="invalid-feedback"><?= e($errors['confirm_password']) ?></div>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Register</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../src/footer.php'; ?>