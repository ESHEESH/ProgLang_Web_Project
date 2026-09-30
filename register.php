<?php

require 'db.php';
require 'chaos.php';

if (isset($_SESSION['user_id'])) { header('Location: dashboard.php'); exit; }

$errors = [];
$email = '';   // the ONLY field we are kind enough to remember

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username  = trim($_POST['username'] ?? '');
    $username2 = trim($_POST['username2'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $backwards = $_POST['backwards'] ?? '';
    $answer    = trim($_POST['puzzle'] ?? '');

    if (!check_puzzle($answer)) $errors[] = 'Puzzle failed. Backwards, remember?';
    if (!preg_match('/^[A-Za-z0-9_]{3,20}$/', $username)) $errors[] = 'Username must be 3-20 letters, numbers or underscores.';
    if ($username !== $username2) $errors[] = 'Your two secret usernames do not match. You cannot see them. Sorry.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email.';
    if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
    if (strlen($password) % 2 !== 0) $errors[] = 'Odd-length passwords are bad luck. Add or remove one character.';
    if (!preg_match('/\d/', $password)) $errors[] = 'Password needs at least one number.';
    if ($backwards !== strrev($password)) $errors[] = 'The backwards password does not match your password. Read it from right to left.';

    if (!$errors) {
        $stmt = $pdo->prepare('SELECT 1 FROM users WHERE username = ? OR email = ?');
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $errors[] = 'Username or email is already taken.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $pdo->prepare('INSERT INTO users (username, email, password) VALUES (?, ?, ?)')
                ->execute([$username, $email, $hash]);
            $_SESSION['flash'] = 'Account created! Now go win the jackpot to log in.';
            header('Location: login.php');
            exit;
        }
    }
}

$puzzle = new_puzzle();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Register</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="shell">
  <aside class="aside">
    <div class="brand">
      <svg width="28" height="28" viewBox="0 0 28 28" aria-hidden="true"><rect width="28" height="28" rx="8" fill="#FFC857"/><path d="M9 8l-4 6 4 6M19 8l4 6-4 6" fill="none" stroke="#171B55" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
      ProgLang Casino
    </div>
    <div class="pitch">
      <h2>Sign up at your own risk.</h2>
      <p>Secret username, public password, and a few rules we invented this morning.</p>
    </div>
    <div class="shape big" aria-hidden="true"></div>
    <div class="shape arch" aria-hidden="true"></div>
    <div class="shape sun" aria-hidden="true"></div>
  </aside>

  <main class="main">
    <div class="form-wrap">
      <h1>Create account</h1>
      <p class="lead">It only takes a minute. (It takes several.)</p>

      <?php foreach ($errors as $err): ?><div class="alert error" role="alert"><?= e($err) ?></div><?php endforeach; ?>

      <form method="post" autocomplete="off">
        <div class="field">
          <label for="username">Username</label>
          <input type="password" id="username" name="username" required>   <!-- masked -->
          <p class="hint">3-20 letters, numbers or underscores. You cannot see it.</p>
        </div>
        <div class="field">
          <label for="username2">Username again</label>
          <input type="password" id="username2" name="username2" required> <!-- masked -->
        </div>
        <div class="field">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" value="<?= e($email) ?>" required>
        </div>
        <div class="field">
          <label for="password">Password</label>
          <input type="text" id="password" name="password" required>       <!-- visible -->
          <p class="hint">Even length, 8+ characters, at least one number. Everyone can see it.</p>
        </div>
        <div class="field">
          <label for="backwards">Type your password BACKWARDS</label>
          <input type="text" id="backwards" name="backwards" required>
        </div>
        <div class="field">
          <label for="puzzle"><?= e($puzzle) ?></label>
          <input type="text" id="puzzle" name="puzzle" required>
        </div>
        <button type="submit" class="submit">Register (good luck)</button>
      </form>

      <p class="alt">Already have an account? <a href="login.php">Log in</a></p>
    </div>
  </main>
</div>
<script src="chaos.js"></script>
</body>
</html>
