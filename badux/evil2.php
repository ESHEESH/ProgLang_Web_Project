<?php session_start(); ?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Level 2 — Date of Birth</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{background:#111;width:100vw;min-height:100vh;font-family:system-ui,sans-serif;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:24px;color:#fff;padding:40px 20px}
h1{font-size:1.8rem;font-weight:700;color:#fff;text-align:center}
p{color:#aaa;font-size:.95rem;text-align:center}

/* Solar system canvas */
#solar{display:block;border-radius:16px;background:#000;cursor:pointer}

/* Date display */
.date-display{font-size:1.6rem;font-weight:700;color:#FFC857;letter-spacing:.05em;text-align:center}
.date-hint{font-size:.78rem;color:#666;text-align:center;margin-top:4px}

/* Submit */
.submit-btn{padding:12px 36px;background:#ff0000;color:#fff;border:none;border-radius:10px;font-size:1rem;font-weight:700;cursor:pointer;margin-top:8px}
.submit-btn:hover{background:#cc0000}
</style>
</head>
<body>

<h1>What is your date of birth?</h1>
<p>Drag the Earth around the Sun to select your birthday.<br>The Earth's position = day of year.</p>

<div class="date-display" id="date-display">January 1</div>
<div class="date-hint">Drag Earth around the Sun — pass Dec 31 to change year</div>

<canvas id="solar" width="500" height="280"></canvas>

<button class="submit-btn" id="submit-btn">Submit Birthday</button>

<script>
const canvas = document.getElementById('solar');
const ctx    = canvas.getContext('2d');
const W = canvas.width, H = canvas.height;
const cx = W * 0.4, cy = H * 0.52;
const rx = W * 0.38, ry = H * 0.36; // orbit radii

const MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];
const MONTH_DAYS = [31,28,31,30,31,30,31,31,30,31,30,31];

let angle = -Math.PI / 2; // start at top = Jan 1
let dragging = false;
let currentYear = 2000;
let lastAngle = -Math.PI / 2;

function isLeap(y){ return (y%4===0&&y%100!==0)||y%400===0; }

function dayOfYearToDate(doy, year) {
  const days = [...MONTH_DAYS];
  if(isLeap(year)) days[1] = 29;
  let m = 0;
  while(doy > days[m]) { doy -= days[m]; m++; }
  return { month: m, day: doy };
}

function angleToDoy(a) {
  // normalize angle: 0 = Jan 1 (top = -PI/2)
  let norm = (a + Math.PI/2) / (2*Math.PI);
  norm = ((norm % 1) + 1) % 1;
  return Math.max(1, Math.round(norm * 365));
}

function earthPos(a) {
  return { x: cx + rx * Math.cos(a), y: cy + ry * Math.sin(a) };
}

function draw() {
  ctx.clearRect(0,0,W,H);

  // Stars
  ctx.fillStyle = '#000';
  ctx.fillRect(0,0,W,H);
  for(let i=0;i<120;i++){
    const sx = (i*237)%W, sy = (i*173)%H;
    ctx.fillStyle = `rgba(255,255,255,${0.2+((i*31)%10)/25})`;
    ctx.beginPath();
    ctx.arc(sx,sy,0.6,0,Math.PI*2);
    ctx.fill();
  }

  // Orbit ellipse
  ctx.beginPath();
  ctx.ellipse(cx, cy, rx, ry, 0, 0, Math.PI*2);
  ctx.strokeStyle = 'rgba(255,255,255,0.18)';
  ctx.lineWidth = 1;
  ctx.stroke();

  // Sun glow
  const grad = ctx.createRadialGradient(cx,cy,2,cx,cy,28);
  grad.addColorStop(0,'#fff7a0');
  grad.addColorStop(0.3,'#FFC857');
  grad.addColorStop(1,'rgba(255,150,0,0)');
  ctx.beginPath();
  ctx.arc(cx,cy,28,0,Math.PI*2);
  ctx.fillStyle = grad;
  ctx.fill();

  // Sun core
  ctx.beginPath();
  ctx.arc(cx,cy,10,0,Math.PI*2);
  ctx.fillStyle = '#FFC857';
  ctx.fill();

  // Earth
  const ep = earthPos(angle);
  const egrad = ctx.createRadialGradient(ep.x-2,ep.y-2,1,ep.x,ep.y,9);
  egrad.addColorStop(0,'#7ecfff');
  egrad.addColorStop(0.5,'#2277cc');
  egrad.addColorStop(1,'#0a3a6e');
  ctx.beginPath();
  ctx.arc(ep.x,ep.y,9,0,Math.PI*2);
  ctx.fillStyle = egrad;
  ctx.fill();
  ctx.strokeStyle='rgba(150,220,255,0.5)';
  ctx.lineWidth=1;
  ctx.stroke();
}

function updateDate() {
  const doy = angleToDoy(angle);
  const {month, day} = dayOfYearToDate(doy, currentYear);
  document.getElementById('date-display').textContent = `${MONTHS[month]} ${day}, ${currentYear}`;
}

draw();
updateDate();

// Drag Earth
function getAngle(e) {
  const rect = canvas.getBoundingClientRect();
  const mx = (e.clientX ?? e.touches[0].clientX) - rect.left;
  const my = (e.clientY ?? e.touches[0].clientY) - rect.top;
  return Math.atan2((my - cy) / ry, (mx - cx) / rx);
}

function handleDrag(e) {
  const newAngle = getAngle(e);
  // Detect crossing the top (Jan 1 = -PI/2) going clockwise (year++) or counter-clockwise (year--)
  const prev = lastAngle;
  const curr = newAngle;
  // Crossing from near +PI to near -PI = forward (Dec->Jan = year++)
  if (prev > Math.PI / 2 && curr < -Math.PI / 2) {
    currentYear = Math.min(2026, currentYear + 1);
  }
  // Crossing from near -PI to near +PI = backward (Jan->Dec = year--)
  if (prev < -Math.PI / 2 && curr > Math.PI / 2) {
    currentYear = Math.max(1924, currentYear - 1);
  }
  lastAngle = curr;
  angle = curr;
  draw();
  updateDate();
}

canvas.addEventListener('mousedown',  e => { dragging = true; lastAngle = getAngle(e); angle = lastAngle; draw(); updateDate(); });
canvas.addEventListener('mousemove',  e => { if(!dragging) return; handleDrag(e); });
canvas.addEventListener('mouseup',    () => dragging = false);
canvas.addEventListener('touchstart', e => { dragging = true; lastAngle = getAngle(e); angle = lastAngle; draw(); updateDate(); },{passive:true});
canvas.addEventListener('touchmove',  e => { if(!dragging) return; handleDrag(e); },{passive:true});
canvas.addEventListener('touchend',   () => dragging = false);

document.getElementById('submit-btn').addEventListener('click', () => {
  const txt = document.getElementById('date-display').textContent;
  alert('Birthday submitted: ' + txt + '\n\nGood luck on Level 3 😈');
  window.location.href = '../NormalWebsite/login.php';
});
</script>
</body>
</html>
