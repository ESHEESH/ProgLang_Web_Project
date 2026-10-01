<?php session_start();
if (!empty($_SESSION['survey_done']) && empty($_SESSION['captcha_ok'])) { header('Location: survey.php'); exit; }
$done = !empty($_SESSION['survey_done']) && !empty($_SESSION['captcha_ok']);
?>
<!doctype html><html><head><meta charset="utf-8"><title>My Grades</title>
<style>
html,body{margin:0;height:100%;overflow:hidden;font-family:system-ui,sans-serif;background:#fff}
#bg{position:fixed;inset:-30px;background:url(grades-bg.png) top center/100% auto no-repeat;transition:filter .5s}
#bg.blur{filter:blur(7px)}
#veil{position:fixed;inset:0;background:rgba(80,0,15,.35);display:none;place-items:center}
#veil.show{display:grid}
.modal{background:#fff;width:min(440px,90vw);border-radius:6px;overflow:hidden;box-shadow:0 10px 40px #0008;animation:pop .2s ease-out}
.modal header{background:#9b1b30;color:#fff;padding:.9rem 1.2rem;font-weight:700}
.modal .body{padding:1.4rem 1.2rem;color:#222;line-height:1.5}
.modal footer{padding:0 1.2rem 1.2rem;text-align:right}
.modal a{background:#9b1b30;color:#fff;text-decoration:none;padding:.65rem 1.3rem;border-radius:4px;display:inline-block;font-weight:600}
@keyframes pop{from{transform:scale(.9);opacity:0}to{transform:none;opacity:1}}
</style></head><body>
<div id="bg"></div>
<div id="veil"><div class="modal">
<?php if ($done): ?>
  <header>Grades Unavailable</header>
  <div class="body">Thank you for completing the course assessment.<br><br><strong>Your teacher hasn't submitted your grades yet.</strong> Please check back later.</div>
  <footer><a href="reset.php">Close</a></footer>
<?php else: ?>
  <header>Grades Blocked</header>
  <div class="body">You must <strong>assess the course</strong> before you can view your grades.</div>
  <footer><a href="survey.php?why=assess">Assess Course</a></footer>
<?php endif; ?>
</div></div>
<script>
// sharp for a split second, then blur + popup
setTimeout(()=>{document.getElementById('bg').classList.add('blur');document.getElementById('veil').classList.add('show')},600);
</script></body></html>
