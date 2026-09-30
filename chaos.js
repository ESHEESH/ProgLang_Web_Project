// chaos.js - animation only. PHP decides every result; JS just makes it look dramatic.

/* ---------- The runaway button ----------
   It flees when the cursor gets close, BUT you can catch it:
   1. STAMINA: every escape costs 1 of 8 stamina. At 0 it is too tired to run for 5 seconds.
   2. CORNERED: it can only run inside the form area. Trap it against an edge and it can't escape.
   3. GRAB: holding the mouse button down on it freezes it in place.
   4. FAST HANDS: it needs a moment between dashes, so a quick flick can land on it. */
(function () {
  var btn = document.querySelector('form button.submit');
  if (!btn || window.matchMedia('(pointer: coarse)').matches) return;   // no dodging on touch screens

  var MAX_STAMINA = 8, FLEE_RADIUS = 110, STEP = 150, TIRED_MS = 5000, COOLDOWN = 250;
  var stamina = MAX_STAMINA, tired = false, held = false;
  var ox = 0, oy = 0, lastMove = 0, base = null;
  var label = btn.textContent;
  var wrap = btn.closest('.form-wrap');

  var meter = document.createElement('p');
  meter.className = 'stamina';
  btn.insertAdjacentElement('afterend', meter);

  function rand(min, max) { return Math.random() * (max - min) + min; }
  function clamp(v, lo, hi) { return Math.max(lo, Math.min(hi, v)); }

  function drawMeter() {
    meter.textContent = tired
      ? 'Button stamina: ▯▯▯▯▯▯▯▯ It is exhausted. Catch it!'
      : 'Button stamina: ' + '▮'.repeat(stamina) + '▯'.repeat(MAX_STAMINA - stamina);
  }
  function place() { btn.style.transform = 'translate(' + ox + 'px,' + oy + 'px)'; }

  function measure() {   // remember where the button naturally sits (page coordinates)
    btn.style.transition = 'none';
    btn.style.transform = 'none';
    var r = btn.getBoundingClientRect();
    base = { l: r.left + window.scrollX, t: r.top + window.scrollY, w: r.width, h: r.height };
    ox = 0; oy = 0;
    btn.offsetWidth;                 // force reflow
    btn.style.transition = '';
  }

  function bounds() {    // the area the button is allowed to run around in
    var w = wrap.getBoundingClientRect(), sx = window.scrollX, sy = window.scrollY;
    return {
      l: Math.max(w.left + sx - 40,  sx + 8),
      r: Math.min(w.right + sx + 40, sx + window.innerWidth - 8),
      t: Math.max(w.top + sy,        sy + 8),
      b: Math.min(w.bottom + sy + 40, sy + window.innerHeight - 8)
    };
  }

  function recover() {
    tired = false; stamina = MAX_STAMINA;
    btn.textContent = label;
    ox = 0; oy = 0; place(); drawMeter();
  }

  function becomeTired() {
    tired = true;
    btn.textContent = '😮‍💨 Too tired to run. Click me.';
    drawMeter();
    setTimeout(recover, TIRED_MS);
  }

  document.addEventListener('mousemove', function (e) {
    if (tired || held || !base) return;
    var now = Date.now();
    if (now - lastMove < COOLDOWN) return;

    var L = base.l + ox, T = base.t + oy, R = L + base.w, B = T + base.h;
    var nearX = clamp(e.pageX, L, R), nearY = clamp(e.pageY, T, B);
    if (Math.hypot(e.pageX - nearX, e.pageY - nearY) > FLEE_RADIUS) return;   // cursor still far away

    // run directly away from the cursor (with a little randomness)
    var dx = (L + base.w / 2) - e.pageX, dy = (T + base.h / 2) - e.pageY;
    var dist = Math.hypot(dx, dy) || 1;
    var nx = ox + (dx / dist) * STEP + rand(-30, 30);
    var ny = oy + (dy / dist) * STEP + rand(-30, 30);

    var b = bounds();
    nx = clamp(nx, b.l - base.l, b.r - base.l - base.w);
    ny = clamp(ny, b.t - base.t, b.b - base.t - base.h);

    if (Math.hypot(nx - ox, ny - oy) < 25) return;   // cornered: nowhere to run, you can catch it

    ox = nx; oy = ny; place();
    lastMove = now;
    stamina--;
    if (stamina <= 0) becomeTired(); else drawMeter();
  });

  btn.addEventListener('mousedown', function () { held = true; });   // grabbed!
  document.addEventListener('mouseup', function () { held = false; });

  window.addEventListener('load', function () {
    btn.style.width = '60%';        // narrower button = more room to run
    measure(); drawMeter();
  });
  window.addEventListener('resize', function () { if (!tired) measure(); });
})();

/* ---------- Slot machine animation ----------
   Spins the 3 reels, then stops them one by one on the values PHP chose */
function runSlots(finalSymbols, onDone) {
  var reels = document.querySelectorAll('.reel');
  var symbols = ['🍒', '🍋', '🔔', '💀', '🥔', '7️⃣'];
  reels.forEach(function (reel, i) {
    reel.classList.add('spinning');
    var timer = setInterval(function () {
      reel.textContent = symbols[Math.floor(Math.random() * symbols.length)];
    }, 70);
    setTimeout(function () {
      clearInterval(timer);
      reel.textContent = finalSymbols[i];
      reel.classList.remove('spinning');
      if (i === reels.length - 1) onDone();
    }, 1200 + i * 800);
  });
}

/* ---------- Secret staff entrance ----------
   Click the yellow logo 5 times to reveal a hidden button.
   It asks for a code; PHP checks the code (BYPASS_CODE in chaos.php) and skips the slot machine. */
(function () {
  var logo = document.querySelector('.brand svg');
  var staff = document.getElementById('staff');
  var field = document.getElementById('bypass');
  var form = document.querySelector('form');
  if (!logo || !staff || !field || !form) return;

  var clicks = 0;
  logo.addEventListener('click', function () {
    clicks++;
    if (clicks >= 5) staff.hidden = false;
  });
  staff.addEventListener('click', function () {
    var code = prompt('Staff code?');
    if (code === null) return;
    field.value = code;
    form.requestSubmit();     // still needs the username, password and puzzle filled in
  });
})();
