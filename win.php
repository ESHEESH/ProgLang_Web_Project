<?php require 'db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>You Made It!</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{background:#fff;display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:100vh;padding-bottom:80px;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;gap:32px;overflow:hidden}
h1{font-size:2.5rem;font-weight:700;color:#12142B;letter-spacing:-.02em;position:relative;z-index:10}
.media-row{display:flex;align-items:center;justify-content:center;gap:32px;flex-wrap:wrap;position:relative;z-index:10}
video{width:min(700px,60vw);height:360px;object-fit:cover;border-radius:12px}
.main-gif{width:min(520px,48vw);border-radius:12px}
.qr-code{width:min(300px,28vw);border-radius:8px;image-rendering:pixelated}
a.play-again{padding:12px 28px;background:#5856d6;color:#fff;border-radius:10px;font-size:1rem;font-weight:600;text-decoration:none;position:relative;z-index:10}
a.play-again:hover{background:#4543b5}
.logout-btn{position:fixed;top:16px;right:20px;padding:8px 18px;background:#5856d6;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;text-decoration:none;z-index:200}
.logout-btn:hover{background:#4543b5}

/* Bouncing DVDs */
.dvd{position:fixed;width:120px;pointer-events:none;z-index:5;border-radius:8px}

/* Footer */
.footer{position:fixed;bottom:24px;left:50%;transform:translateX(-50%);z-index:100;display:flex;align-items:center;gap:16px;background:#181818;border-radius:40px;padding:10px 20px;box-shadow:0 8px 32px rgba(0,0,0,.35);min-width:340px}
.footer-label{display:flex;align-items:center;gap:6px;font-size:.8rem;font-weight:600;color:#b3b3b3;white-space:nowrap}
.vol-circle{width:40px;height:40px;border-radius:50%;background:#282828;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#fff;flex-shrink:0;transition:background .15s,transform .1s}
.vol-circle:hover{background:#5856d6;transform:scale(1.08)}
.vol-track{flex:1;height:4px;background:#535353;border-radius:2px;overflow:hidden;cursor:pointer}
.vol-track-fill{height:100%;background:#1db954;border-radius:2px;transition:width .3s}
.vol-val{font-size:.78rem;font-weight:600;color:#b3b3b3;min-width:34px;text-align:center}
.vol-val{font-size:.85rem;color:#666;min-width:38px;text-align:center}
.vol-btn{display:flex;align-items:center;gap:6px;padding:6px 14px;border:none;border-radius:8px;font-size:.85rem;font-weight:600;cursor:pointer;background:#5856d6;color:#fff}
.vol-btn:hover{background:#4543b5}
/* Modal */
.modal-bg{position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:300;display:none;align-items:center;justify-content:center}
.modal-bg.open{display:flex}
.modal{background:#fff;border-radius:14px;padding:28px 32px;width:min(480px,92vw);display:flex;flex-direction:column;gap:14px}
.modal-label{font-size:.85rem;color:#666}
.modal-q{font-size:1.15rem;font-weight:700;color:#12142B;line-height:1.5;font-family:'Courier New',monospace;background:#f7f7f7;padding:12px;border-radius:8px}
.modal input{height:44px;padding:0 14px;font-size:1rem;border:1.5px solid #ddd;border-radius:8px;outline:none;width:100%}
.modal input:focus{border-color:#5856d6}
.modal-row{display:flex;gap:10px}
.modal-btn{flex:1;height:42px;border:none;border-radius:8px;font-size:.95rem;font-weight:600;cursor:pointer}
.modal-btn.ok{background:#5856d6;color:#fff}
.modal-btn.ok:hover{background:#4543b5}
.modal-btn.cancel{background:#eee;color:#444}
.modal-btn.cancel:hover{background:#ddd}
.modal-btn.refresh{background:#f0f0f0;color:#444;display:flex;align-items:center;justify-content:center;gap:5px;flex:0 0 70px}
.modal-btn.refresh:hover{background:#ddd}
.modal-err{font-size:.82rem;color:#c0392b;min-height:1em}
</style>
</head>
<body>

<a href="/badux/index.php" class="logout-btn">Log out</a>

<!-- Bouncing DVD images -->
<img class="dvd" id="dvd1" src="ASSETS/rene2.jpg" alt="">
<img class="dvd" id="dvd2" src="ASSETS/lerios.jpg" alt="">

<h1>You made it!</h1>
<div class="media-row">
  <video src="ASSETS/damn.mp4" autoplay loop muted playsinline></video>
  <img class="main-gif" src="ASSETS/graphics-men-196416.gif" alt="Celebration">
  <img class="qr-code" src="ASSETS/shi.png" alt="QR Code">
</div>
<a href="main.php" class="play-again">Play again</a>

<footer class="footer">
  <div class="footer-label">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M15.54 8.46a5 5 0 0 1 0 7.07"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14"/></svg>
  </div>
  <button class="vol-circle" id="btn-down" title="Lower volume">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/></svg>
  </button>
  <div class="vol-track"><div class="vol-track-fill" id="vol-fill"></div></div>
  <span class="vol-val" id="vol-val">80%</span>
  <button class="vol-circle" id="btn-up" title="Raise volume">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
  </button>
</footer>

<!-- Lower modal: calculus/trig/laplace -->
<div class="modal-bg" id="modal-down">
  <div class="modal">
    <p class="modal-label">Solve this to lower the volume:</p>
    <p class="modal-q" id="q-down"></p>
    <input type="text" id="ans-down" placeholder="Your answer" autocomplete="off">
    <div class="modal-row">
      <button class="modal-btn ok" id="submit-down">Submit</button>
      <button class="modal-btn refresh" id="refresh-down">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
        New
      </button>
      <button class="modal-btn cancel" id="cancel-down">Cancel</button>
    </div>
    <p class="modal-err" id="err-down"></p>
  </div>
</div>

<!-- Raise modal: easy 1+1 -->
<div class="modal-bg" id="modal-up">
  <div class="modal">
    <p class="modal-label">Solve this to raise the volume:</p>
    <p class="modal-q" id="q-up">What is 1 + 1?</p>
    <input type="text" id="ans-up" placeholder="Your answer" autocomplete="off">
    <div class="modal-row">
      <button class="modal-btn ok" id="submit-up">Submit</button>
      <button class="modal-btn refresh" id="refresh-up">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
        New
      </button>
      <button class="modal-btn cancel" id="cancel-up">Cancel</button>
    </div>
    <p class="modal-err" id="err-up"></p>
  </div>
</div>

<audio id="bg-audio" src="ASSETS/damnsong.mp3" autoplay></audio>

<script>
// Volume
const audio  = document.getElementById('bg-audio');
const fill   = document.getElementById('vol-fill');
const volVal = document.getElementById('vol-val');
let vol = 0.8;
audio.volume = vol;

// Chain: damnsong -> peak (looped)
const playlist = ['ASSETS/damnsong.mp3', 'ASSETS/peak.mp3'];
let trackIndex = 0;
audio.addEventListener('ended', () => {
  trackIndex++;
  if (trackIndex < playlist.length) {
    audio.src = playlist[trackIndex];
    audio.play();
  } else {
    // Loop peak.mp3 forever after
    audio.loop = true;
    audio.src = 'ASSETS/peak.mp3';
    audio.play();
  }
});
function updateBar() {
  fill.style.width = Math.round(vol*100)+'%';
  volVal.textContent = Math.round(vol*100)+'%';
}
updateBar();

// Calculus / Trig / Laplace questions (display + exact answer string)
const hardQuestions = [
  // Derivatives
  { q: "d/dx [ x⁴ − 3x² + 7 ] = ?\n(write as: ax^n + bx^m form, e.g. 4x^3-6x)", a: "4x^3-6x" },
  { q: "d/dx [ sin(x) · cos(x) ] = ?\n(use double angle identity, answer: cos(2x))", a: "cos(2x)" },
  { q: "d/dx [ e^(3x) ] = ?", a: "3e^(3x)" },
  { q: "d/dx [ ln(x²) ] = ?", a: "2/x" },
  { q: "d/dx [ tan(x) ] = ?", a: "sec^2(x)" },
  // Integrals
  { q: "∫ 6x² dx = ?\n(include +C)", a: "2x^3+c" },
  { q: "∫ cos(x) dx = ?\n(include +C)", a: "sin(x)+c" },
  { q: "∫ e^x dx = ?\n(include +C)", a: "e^x+c" },
  { q: "∫ 1/x dx = ?\n(include +C)", a: "ln(x)+c" },
  { q: "∫₀^π sin(x) dx = ?", a: "2" },
  // Trig
  { q: "sin²(x) + cos²(x) = ?", a: "1" },
  { q: "What is sin(30°)?", a: "1/2" },
  { q: "What is cos(60°)?", a: "1/2" },
  { q: "What is tan(45°)?", a: "1" },
  { q: "What is sin(90°)?", a: "1" },
  // Laplace
  { q: "L{ e^(at) } = ?\n(in terms of s and a)", a: "1/(s-a)" },
  { q: "L{ 1 } = ?\n(in terms of s)", a: "1/s" },
  { q: "L{ t } = ?\n(in terms of s)", a: "1/s^2" },
  { q: "L{ sin(at) } = ?\n(in terms of s and a)", a: "a/(s^2+a^2)" },
  { q: "L{ cos(at) } = ?\n(in terms of s and a)", a: "s/(s^2+a^2)" },
];

function getHardQuestion() {
  return hardQuestions[Math.floor(Math.random() * hardQuestions.length)];
}
function normalize(s) {
  return s.toLowerCase().replace(/\s+/g,'').replace(/×/g,'*');
}

let downAnswer;
document.getElementById('btn-down').addEventListener('click', () => {
  const q = getHardQuestion();
  downAnswer = q.a;
  document.getElementById('q-down').textContent = q.q;
  document.getElementById('ans-down').value = '';
  document.getElementById('err-down').textContent = '';
  document.getElementById('modal-down').classList.add('open');
  document.getElementById('ans-down').focus();
});
document.getElementById('cancel-down').addEventListener('click', () => {
  document.getElementById('modal-down').classList.remove('open');
});
document.getElementById('refresh-down').addEventListener('click', () => {
  const q = getHardQuestion();
  downAnswer = q.a;
  document.getElementById('q-down').textContent = q.q;
  document.getElementById('ans-down').value = '';
  document.getElementById('err-down').textContent = '';
  document.getElementById('ans-down').focus();
});
document.getElementById('submit-down').addEventListener('click', () => {
  const ans = normalize(document.getElementById('ans-down').value);
  if (ans === normalize(downAnswer)) {
    vol = Math.max(0, vol - 0.15);
    audio.volume = vol;
    updateBar();
    document.getElementById('modal-down').classList.remove('open');
  } else {
    document.getElementById('err-down').textContent = `Wrong! Answer: ${downAnswer}. New question incoming...`;
    setTimeout(() => {
      const q = getHardQuestion();
      downAnswer = q.a;
      document.getElementById('q-down').textContent = q.q;
      document.getElementById('ans-down').value = '';
      document.getElementById('err-down').textContent = '';
    }, 1800);
  }
});

document.getElementById('btn-up').addEventListener('click', () => {
  document.getElementById('ans-up').value = '';
  document.getElementById('err-up').textContent = '';
  document.getElementById('modal-up').classList.add('open');
  document.getElementById('ans-up').focus();
});
document.getElementById('cancel-up').addEventListener('click', () => {
  document.getElementById('modal-up').classList.remove('open');
});
document.getElementById('refresh-up').addEventListener('click', () => {
  document.getElementById('ans-up').value = '';
  document.getElementById('err-up').textContent = '';
  document.getElementById('ans-up').focus();
});
document.getElementById('submit-up').addEventListener('click', () => {
  const ans = document.getElementById('ans-up').value.trim();
  if (ans === '2') {
    vol = Math.min(1, vol + 0.15);
    audio.volume = vol;
    updateBar();
    document.getElementById('modal-up').classList.remove('open');
  } else {
    document.getElementById('err-up').textContent = 'Wrong. 1 + 1 = 2. Come on.';
  }
});

document.getElementById('ans-down').addEventListener('keydown', e => { if(e.key==='Enter') document.getElementById('submit-down').click(); });
document.getElementById('ans-up').addEventListener('keydown',   e => { if(e.key==='Enter') document.getElementById('submit-up').click(); });

// Bouncing DVD logos
function dvd(el) {
  let x = Math.random() * (window.innerWidth  - 120);
  let y = Math.random() * (window.innerHeight - 120);
  let dx = (Math.random() > 0.5 ? 1 : -1) * (1.5 + Math.random());
  let dy = (Math.random() > 0.5 ? 1 : -1) * (1.5 + Math.random());
  const W = 120, H = 120;
  function step() {
    x += dx; y += dy;
    if (x <= 0)                        { x = 0;                        dx = Math.abs(dx); }
    if (x >= window.innerWidth  - W)   { x = window.innerWidth  - W;   dx = -Math.abs(dx); }
    if (y <= 0)                        { y = 0;                        dy = Math.abs(dy); }
    if (y >= window.innerHeight - H)   { y = window.innerHeight - H;   dy = -Math.abs(dy); }
    el.style.left = x + 'px';
    el.style.top  = y + 'px';
    requestAnimationFrame(step);
  }
  el.style.position = 'fixed';
  requestAnimationFrame(step);
}
dvd(document.getElementById('dvd1'));
dvd(document.getElementById('dvd2'));
</script>
</body>
</html>
