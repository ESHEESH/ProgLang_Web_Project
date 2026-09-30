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

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root{
  --ink:#12142B; --muted:#5E6384; --line:#D5D9EA; --canvas:#F6F7FB; --white:#fff;
  --brand:#3D4BE0; --brand-dark:#2C38B5; --sky:#8FA2FF; --sun:#FFC857; --panel:#171B55;
  --err-bg:#FDECEC; --err:#9B1C1C; --ok-bg:#E3F5EA; --ok:#0F6B3A;
}
*{box-sizing:border-box}
html,body{margin:0}
body{font-family:'DM Sans',system-ui,-apple-system,'Segoe UI',sans-serif;color:var(--ink);background:var(--canvas);-webkit-font-smoothing:antialiased}
.shell{display:grid;grid-template-columns:minmax(0,5fr) minmax(0,6fr);min-height:100vh}

/* Left panel */
.aside{position:sticky;top:0;height:100vh;overflow:hidden;background:var(--panel);color:#fff;padding:44px 52px;display:flex;flex-direction:column;justify-content:flex-start}
.brand{display:flex;align-items:center;gap:10px;font-family:'Bricolage Grotesque','DM Sans',system-ui,sans-serif;font-weight:700;font-size:1.25rem;letter-spacing:-.01em;position:relative;z-index:2}
.brand svg{display:block}
.pitch{position:relative;z-index:2;max-width:26rem;margin-top:clamp(40px,13vh,130px)}
.pitch h2{font-family:'Bricolage Grotesque','DM Sans',system-ui,sans-serif;font-weight:700;font-size:clamp(2.1rem,3.4vw,3.2rem);line-height:1.04;letter-spacing:-.025em;margin:0 0 14px}
.pitch p{margin:0;color:#C9CFFF;font-size:1.05rem;line-height:1.5}
.shape{position:absolute;z-index:1;animation:rise .9s cubic-bezier(.2,.7,.2,1) both}
.shape.big{width:460px;height:460px;border-radius:50%;background:var(--brand);right:-150px;bottom:-170px}
.shape.arch{width:250px;height:290px;border-radius:250px 250px 0 0;background:var(--sky);left:44px;bottom:0;animation-delay:.12s}
.shape.sun{width:92px;height:92px;border-radius:50%;background:var(--sun);right:96px;bottom:250px;animation-delay:.24s}

/* Right side */
.main{display:grid;place-items:center;padding:48px 24px}
.form-wrap{width:100%;max-width:400px}
h1{font-family:'Bricolage Grotesque','DM Sans',system-ui,sans-serif;font-weight:700;font-size:2.25rem;letter-spacing:-.025em;line-height:1.1;margin:0 0 8px}
.lead{margin:0 0 28px;color:var(--muted);font-size:1rem;line-height:1.5}
.field{margin-bottom:18px}
label{display:block;font-size:.9rem;font-weight:600;margin-bottom:7px}
input{width:100%;height:50px;padding:0 14px;font:inherit;font-size:1rem;color:var(--ink);background:var(--white);border:1.5px solid var(--line);border-radius:10px;transition:border-color .15s,box-shadow .15s}
input:hover{border-color:#B9BEDA}
input:focus{outline:none;border-color:var(--brand);box-shadow:0 0 0 4px rgba(61,75,224,.16)}
.hint{margin:6px 0 0;font-size:.82rem;color:var(--muted)}
.pw{position:relative}
.pw input{padding-right:52px}
.pw-toggle{position:absolute;top:7px;right:7px;width:36px;height:36px;display:grid;place-items:center;border:0;border-radius:8px;background:transparent;color:var(--muted);cursor:pointer}
.pw-toggle:hover{background:#EEF0FA;color:var(--ink)}
.pw-toggle:focus-visible{outline:2px solid var(--brand);outline-offset:1px}
.pw-toggle .off{display:none}
.pw-toggle[aria-pressed="true"] .on{display:none}
.pw-toggle[aria-pressed="true"] .off{display:block}
button.submit{width:100%;height:52px;margin-top:8px;font:inherit;font-size:1rem;font-weight:600;color:#fff;background:var(--brand);border:0;border-radius:10px;cursor:pointer;transition:background .15s,transform .05s}
button.submit:hover{background:var(--brand-dark)}
button.submit:active{transform:translateY(1px)}
button.submit:focus-visible{outline:3px solid rgba(61,75,224,.35);outline-offset:2px}
.alert{padding:12px 14px;border-radius:10px;font-size:.92rem;line-height:1.45;margin-bottom:14px;border-left:4px solid}
.alert.error{background:var(--err-bg);color:var(--err);border-color:var(--err)}
.alert.success{background:var(--ok-bg);color:var(--ok);border-color:var(--ok)}
.alt{margin:26px 0 0;text-align:center;color:var(--muted);font-size:.95rem}
.alt a{color:var(--brand);font-weight:600;text-decoration:none}
.alt a:hover{text-decoration:underline}
.alt a:focus-visible{outline:2px solid var(--brand);outline-offset:3px;border-radius:3px}

@keyframes rise{from{opacity:0;transform:translateY(40px)}to{opacity:1;transform:none}}
@media (prefers-reduced-motion:reduce){.shape{animation:none}input,button.submit{transition:none}}

@media (max-width:860px){
  .shell{grid-template-columns:1fr}
  .aside{position:relative;height:auto;padding:20px 24px;min-height:0}
  .pitch,.shape.arch,.shape.sun{display:none}
  .shape.big{width:200px;height:200px;right:-60px;bottom:-110px}
  .main{padding:32px 20px 48px;place-items:start center}
}
</style>
</head>
<body>
<div class="shell">
  <aside class="aside">
    <div class="brand">
      <svg width="28" height="28" viewBox="0 0 28 28" aria-hidden="true"><rect width="28" height="28" rx="8" fill="#FFC857"/><path d="M9 8l-4 6 4 6M19 8l4 6-4 6" fill="none" stroke="#171B55" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
      ProgLang
    </div>
    <div class="pitch">
      <h2>Good to see you again.</h2>
      <p>Your dashboard is one step away.</p>
    </div>
    <div class="shape big" aria-hidden="true"></div>
    <div class="shape arch" aria-hidden="true"></div>
    <div class="shape sun" aria-hidden="true"></div>
  </aside>
  <main class="main">
    <div class="form-wrap">
      <h1>Welcome back</h1>
      <p class="lead">Log in with your username or email.</p>
      <?php if ($flash): ?><div class="alert success" role="status"><?= e($flash) ?></div><?php endif; ?>
      <?php if ($error): ?><div class="alert error" role="alert"><?= e($error) ?></div><?php endif; ?>
      <form method="post">
        <div class="field">
          <label for="login">Username or email</label>
          <input type="text" id="login" name="login" autocomplete="username" required>
        </div>
        <div class="field">
          <label for="password">Password</label>
          <div class="pw">
            <input type="password" id="password" name="password" autocomplete="current-password" required>
            <button type="button" class="pw-toggle" aria-label="Show password" aria-pressed="false"><svg class="on" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg><svg class="off" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17.9 17.9A10.9 10.9 0 0112 19C5 19 1 12 1 12a18.5 18.5 0 015.1-5.9M9.9 4.2A10.9 10.9 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.2 3.2M1 1l22 22"/></svg></button>
          </div>
        </div>
        <button type="submit" class="submit">Log in</button>
      </form>
      <p class="alt">No account yet? <a href="register.php">Register</a></p>
    </div>
  </main>
</div>
<script>
document.querySelectorAll('.pw-toggle').forEach(function (btn) {
  btn.addEventListener('click', function () {
    var input = btn.parentElement.querySelector('input');
    var show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    btn.setAttribute('aria-pressed', show ? 'true' : 'false');
    btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
  });
});
</script>
</body>
</html>
