<?php

require 'db.php';
require 'chaos.php';

if (isset($_SESSION['user_id'])) { header('Location: dashboard.php'); exit; }

$error = '';
$won = false;
$reels = null;
$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);
$_SESSION['spins'] = $_SESSION['spins'] ?? 0;   // lifetime spins wasted

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login    = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    $answer   = trim($_POST['puzzle'] ?? '');

    // Gate 1: the backwards puzzle
    if (!check_puzzle($answer)) {
        $error = 'Puzzle failed. Did you type it backwards? Forwards? Who knows. No spin for you.';
    } else {
        // Gate 2: the slot machine decides if you are even allowed to try...
        // ...unless you know the secret staff code (hidden button on the page)
        $bypass = hash_equals(BYPASS_CODE, (string)($_POST['bypass'] ?? ''));

        if ($bypass) {
            $jackpot = true;                  // staff entrance: no spin at all
        } else {
            $_SESSION['spins']++;
            $spin    = spin_slots();
            $reels   = $spin['reels'];
            $jackpot = $spin['jackpot'];
            if (!$jackpot) $error = random_taunt();
        }

        if ($jackpot) {
            // Gate 3: NOW we check your credentials
            $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? OR email = ?');
            $stmt->execute([$login, $login]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true); // prevent session fixation
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                if ($bypass) { header('Location: dashboard.php'); exit; }   // no animation needed
                $won = true;
            } elseif ($bypass) {
                $error = 'Staff entrance accepted, but your username or password is wrong.';
            } else {
                $error = 'JACKPOT! ...but your username or password is wrong. The jackpot has been revoked.';
            }
        }
    }
}

$puzzle = $won ? '' : new_puzzle();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Lucky Login</title>
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
      ProgLang
    </div>
    <div class="pitch">
      <h2>The house always wins.</h2>
    </div>
    <div class="shape big" aria-hidden="true"></div>
    <div class="shape arch" aria-hidden="true"></div>
    <div class="shape sun" aria-hidden="true"></div>
  </aside>

  <main class="main">
    <div class="form-wrap">
      <h1>Login</h1>
      <p class="lead">Entry is not guaranteed.</strong></p>

      </div>

      <?php if ($flash): ?><div class="alert success" role="status"><?= e($flash) ?></div><?php endif; ?>
      <?php if ($error && !$reels): ?>
        <div class="alert error" role="alert"><?= e($error) ?></div>
      <?php endif; ?>

      <form method="post" autocomplete="off">
        <div class="field">
          <label for="login">Username or email</label>
          <!-- BAD UX: the username is MASKED -->
          <input type="password" id="login" name="login" required>
          <p class="hint">Typed in secret. Type carefully.</p>
        </div>
        <div class="field">
          <label for="password">Password</label>
          <!-- BAD UX: the password is shown in PLAIN TEXT -->
          <input type="text" id="password" name="password" required>
          <p class="hint">Displayed to everyone behind you. For safety.</p>
        </div>
        <div class="field">
          <label for="puzzle"><?= e($puzzle) ?></label>
          <input type="text" id="puzzle" name="puzzle" required>
        </div>
        <input type="hidden" name="bypass" id="bypass" value="">
        <button type="submit" class="submit">LOGIN</button>
        <button type="button" class="staff" id="staff" hidden>🔑 Staff entrance</button>
      </form>

      <p class="alt">No account yet? <a href="register.php">Register (if you dare)</a></p>
    </div>
  </main>
</div>

<?php if ($reels): ?>
<!-- Hidden until the login button is pressed: the slot machine overlay -->
<div class="casino-overlay" id="casino" role="dialog" aria-modal="true" aria-label="Slot machine">
  <div class="casino-box">
    <h2 id="casino-title">🎰 Pulling the lever...</h2>
    <div class="slot">
      <?php for ($i = 0; $i < 3; $i++): ?><div class="reel">🎰</div><?php endfor; ?>
    </div>
    <div id="result" style="display:none">
      <?php if ($won): ?>
        <div class="alert success" role="status">🎉 JACKPOT after <?= (int)$_SESSION['spins'] ?> spin(s)! Letting you in...</div>
      <?php else: ?>
        <div class="alert error" role="alert"><?= e($error) ?></div>
        <button type="button" class="submit" id="close-casino">Try again (retype everything)</button>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<script src="chaos.js"></script>
<?php if ($reels): ?>
<script>
runSlots(<?= json_encode($reels) ?>, function () {
  document.getElementById('casino-title').textContent = <?= json_encode($won ? '🎉 JACKPOT!' : '💀 No luck') ?>;
  document.getElementById('result').style.display = 'block';
  <?php if ($won): ?>setTimeout(function () { location.href = 'dashboard.php'; }, 1800);<?php endif; ?>
});
var closeBtn = document.getElementById('close-casino');
if (closeBtn) closeBtn.addEventListener('click', function () {
  document.getElementById('casino').remove();   // unblur the page
});
</script>
<?php endif; ?>
</body>
</html>
