<?php session_start(); ?>
<!doctype html><html><head><meta charset="utf-8"><title>Student Portal</title>
<style>
html,body{margin:0;background:#eee}
.stage{position:relative;width:100%;aspect-ratio:1919/949}
.stage img{width:100%;height:100%;display:block}
/* invisible hotspot over the "SPR" item in the sidebar */
.spr{position:absolute;left:.6%;top:73.2%;width:10%;height:2.9%;border-radius:6px}
.spr:hover{background:#b0243222}
</style></head><body>
<div class="stage">
  <img src="dashboard-bg.png" alt="Student portal dashboard">
  <a class="spr" href="grades.php" title="SPR"></a>
</div>
</body></html>
