<?php
require 'db.php';

if (isset($_SESSION['user_id'])) { header('Location: dashboard.php'); exit; }

$errors = [];
$username = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if (!preg_match('/^[A-Za-z0-9_]{3,20}$/', $username)) $errors[] = 'Username must be 3-20 letters, numbers or underscores.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))        $errors[] = 'Please enter a valid email.';
    if (strlen($password) < 8)                             $errors[] = 'Password must be at least 8 characters.';
    if ($password !== $confirm)                            $errors[] = 'Passwords do not match.';

    if (!$errors) {
        $stmt = $pdo->prepare('SELECT 1 FROM users WHERE username = ? OR email = ?');
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $errors[] = 'Username or email is already taken.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $pdo->prepare('INSERT INTO users (username, email, password) VALUES (?, ?, ?)')
                ->execute([$username, $email, $hash]);
            $_SESSION['flash'] = 'Account created! You can now log in.';
            header('Location: login.php');
            exit;
        }
    }
}

page_start('Register');
?>
<h2>Create account</h2>
<?php foreach ($errors as $err): ?><div class="error"><?= e($err) ?></div><?php endforeach; ?>
<form method="post">
    <label>Username</label>
    <input type="text" name="username" value="<?= e($username) ?>" required>
    <label>Email</label>
    <input type="email" name="email" value="<?= e($email) ?>" required>
    <label>Password</label>
    <input type="password" name="password" required>
    <label>Confirm password</label>
    <input type="password" name="confirm" required>
    <button type="submit">Register</button>
</form>
<p class="alt">Already have an account? <a href="login.php">Log in</a></p>
<?php page_end(); ?>
