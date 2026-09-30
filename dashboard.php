<?php
require 'db.php';

// Protected page: redirect guests to login
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$stmt = $pdo->prepare('SELECT username, email, created_at FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

page_start('Dashboard');
?>
<h2>Welcome, <?= e($user['username']) ?>!</h2>
<p>Email: <?= e($user['email']) ?><br>Member since: <?= e($user['created_at']) ?></p>
<p class="alt"><a href="logout.php">Log out</a></p>
<?php page_end(); ?>


