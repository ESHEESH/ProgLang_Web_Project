<?php
require 'db.php';

if (isset($_SESSION['user_id'])) { header('Location: dashboard.php'); exit; }

$error = '';
$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login    = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? OR email = ?');
    $stmt->execute([$login, $login]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);   // prevent session fixation
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        header('Location: dashboard.php');
        exit;
    }
    $error = 'Invalid username/email or password.';
}

page_start('Login');
?>
<h2>Log in</h2>
<?php if ($flash): ?><div class="success"><?= e($flash) ?></div><?php endif; ?>
<?php if ($error): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
<form method="post">
    <label>Username or email</label>
    <input type="text" name="login" required>
    <label>Password</label>
    <input type="password" name="password" required>
    <button type="submit">Log in</button>
</form>
<p class="alt">No account yet? <a href="register.php">Register</a></p>
<?php page_end(); ?>
