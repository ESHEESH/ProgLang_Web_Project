<?php session_start(); ?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Click the button</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;cursor:none}
body{background:#fff;width:100vw;height:100vh;overflow:hidden;font-family:system-ui,sans-serif}

/* Real cursor + fake cursors */
.cursor{position:fixed;pointer-events:none;z-index:9999;width:20px;height:20px;top:0;left:0}
.cursor svg{display:block}

/* The button */
#evil-btn{
  position:fixed;
  padding:16px 36px;
  background:#ff0000;
  color:#fff;
  border:none;
  border-radius:12px;
  font-size:1.2rem;
  font-weight:700;
  cursor:none;
  box-shadow:0 4px 20px rgba(255,0,0,.4);
  z-index:100;
  transition:none;
}
#evil-btn:hover{background:#cc0000}

/* Click count display */
#counter{
  position:fixed;
  top:20px;left:50%;
  transform:translateX(-50%);
  font-size:.9rem;
  color:#aaa;
  z-index:200;
}
</style>
</head>
<body>

<div id="counter">Clicks: 0 — Find the real button</div>
<button id="evil-btn">Click me</button>

<script>
const CURSOR_SVG = `<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 2L16 10L9.5 11.5L7 18L4 2Z" fill="black" stroke="white" stroke-width="1"/></svg>`;

let clicks = 0;
let fakeCursors = [];
let mouseX = window.innerWidth / 2;
let mouseY = window.innerHeight / 2;

// Real cursor
const realCursor = document.createElement('div');
realCursor.className = 'cursor';
realCursor.innerHTML = CURSOR_SVG;
document.body.appendChild(realCursor);

document.addEventListener('mousemove', e => {
  mouseX = e.clientX;
  mouseY = e.clientY;
  realCursor.style.transform = `translate(${mouseX}px,${mouseY}px)`;

  // Move fake cursors — same delta as real cursor but offset
  const dx = e.movementX;
  const dy = e.movementY;
  fakeCursors.forEach(fc => {
    fc.ox += dx;
    fc.oy += dy;
    // Wrap around edges
    if (fc.ox < 0) fc.ox += window.innerWidth;
    if (fc.ox > window.innerWidth)  fc.ox -= window.innerWidth;
    if (fc.oy < 0) fc.oy += window.innerHeight;
    if (fc.oy > window.innerHeight) fc.oy -= window.innerHeight;
    fc.el.style.transform = `translate(${fc.ox}px,${fc.oy}px)`;
  });
});

function spawnFakeCursors(count) {
  for (let i = 0; i < count; i++) {
    const el = document.createElement('div');
    el.className = 'cursor';
    el.innerHTML = CURSOR_SVG;
    el.style.opacity = 0.85;
    document.body.appendChild(el);
    const ox = Math.random() * window.innerWidth;
    const oy = Math.random() * window.innerHeight;
    el.style.transform = `translate(${ox}px,${oy}px)`;
    fakeCursors.push({ el, ox, oy });
  }
}

function moveButton() {
  const btn = document.getElementById('evil-btn');
  const bw = btn.offsetWidth;
  const bh = btn.offsetHeight;
  const x = Math.random() * (window.innerWidth  - bw);
  const y = Math.random() * (window.innerHeight - bh - 60) + 60;
  btn.style.left = x + 'px';
  btn.style.top  = y + 'px';
}

// Place button randomly on load
moveButton();

// Spawn initial clones scattered across screen
spawnFakeCursors(12);

document.getElementById('evil-btn').addEventListener('click', () => {
  clicks++;
  document.getElementById('counter').textContent = `Clicks: ${clicks}/15 — Find the real button`;

  if (clicks >= 15) {
    window.location.href = 'evil2.php';
    return;
  }

  // Move button to new random position
  moveButton();

  // Spawn more fake cursors (doubles each click, up to 64)
  const toSpawn = Math.min(Math.pow(2, clicks), 64);
  spawnFakeCursors(toSpawn);
});
</script>
</body>
</html>
