<?php session_start();
$scale = ["Strongly Agree","Agree","Undecided / Neutral","Disagree","Strongly Disagree"];
// 'ok' => accepted answers for trap (attention-check) questions
$qs = [
 ["The course learning objectives were clearly outlined in the syllabus and consistently followed throughout the term."],
 ["The required course materials were relevant and directly supported my learning."],
 ["The instructor explained complex topics clearly and thoroughly during the course."],
 ["Feedback on assignments and exams was detailed enough to help me improve my work."],
 ["To show that you are reading each feedback statement carefully, please select Disagree for this question.", "ok"=>["Disagree"]],
 ["The overall workload for this course was reasonable and manageable given the course credit hours."],
 ["The grading criteria and rubrics were clearly communicated before assignments were due."],
 ["The course structure provided sufficient opportunities to practice skills before major evaluations."],
 ["The instructor was accessible, approachable, and responsive during office hours or via email."],
 ["The assignments and quizzes were fair reflections of the material covered in class."],
 ["I learned absolutely nothing from this course, yet I strongly recommend every student take it to gain valuable knowledge.", "ok"=>["Disagree","Strongly Disagree"]],
 ["The online platform, tools, and digital resources used for this course functioned reliably."],
 ["Class discussions and interactive activities actively encouraged me to think critically about the subject matter."],
 ["The pace at which the course material was delivered was appropriate for effective understanding."],
 ["I feel that I achieved the stated learning outcomes and gained practical knowledge from this course."],
 ["The instructor created an inclusive learning environment where questions and diverse perspectives were welcomed."],
 ["If you are completing this course feedback attentively, select Strongly Agree to verify your response.", "ok"=>["Strongly Agree"]],
 ["The course effectively connected theoretical concepts to real-world applications or practical scenarios."],
 ["Overall, the quality of instruction and delivery in this course met or exceeded my expectations."],
 ["I would recommend this course to other students interested in this subject area."],
];
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $pass = true;
  foreach ($qs as $i=>$q) if (isset($q['ok']) && !in_array($_POST["q$i"] ?? '', $q['ok'])) $pass = false;
  if ($pass) { $_SESSION['survey_done']=true; $_SESSION['captcha_ok']=true; $_SESSION['answers']=$_POST; header('Location: grades.php'); exit; }
}
$traps = [];
foreach ($qs as $i=>$q) if (isset($q['ok'])) $traps[$i] = ['ok'=>$q['ok']];
?>
<!doctype html><html><head><meta charset="utf-8"><title>Course Assessment</title>
<style>
body{margin:0;background:#d92121;min-height:100vh;color:#8a2fb0}
.wrap{max-width:760px;margin:2rem auto;padding:1rem}
h1{font-family:Impact,fantasy;font-size:2.6rem;letter-spacing:-2px;cursor:pointer;margin:0 0 1rem}
.note{font-size:.6rem;font-family:"Courier New",monospace}
.q{margin:1.8rem 0 .4rem;font-weight:700}
.q:nth-of-type(5n+1){font:italic .75rem "Comic Sans MS",cursive}
.q:nth-of-type(5n+2){font:2rem Papyrus,fantasy}
.q:nth-of-type(5n+3){font:.6rem "Times New Roman",serif}
.q:nth-of-type(5n+4){font:1.4rem "Brush Script MT",cursive}
.q:nth-of-type(5n+5){font:.7rem Georgia,serif}
label{display:inline-block;margin:0 .6rem;font-size:.65rem;color:#942db8;white-space:nowrap}
input{accent-color:#8a2fb0}
#next{margin-top:2rem;padding:.3rem .5rem;font-size:.55rem;background:#d92121;color:#8a2fb0;border:1px solid #8a2fb0;cursor:pointer}
.next-btn{position:fixed;bottom:24px;right:24px;padding:12px 24px;background:#ff0000;color:#8a2fb0;border:3px solid #8a2fb0;border-radius:0;font-size:1rem;font-weight:700;cursor:pointer;z-index:10;text-decoration:none;letter-spacing:.02em}
.next-btn:hover{background:#cc0000}
body.readable{background:#fff;color:#111}
body.readable .wrap *{color:#111!important;font:1rem system-ui!important;letter-spacing:0!important}
/* maroon trap popup (readable) */
#veil{position:fixed;inset:0;background:rgba(60,0,10,.55);display:none;place-items:center;z-index:9}
#veil.show{display:grid}
.modal{background:#fff;color:#222;width:min(440px,92vw);border-radius:6px;overflow:hidden;box-shadow:0 10px 40px #0009;font-family:system-ui,sans-serif;animation:pop .2s ease-out}
.modal header{background:#9b1b30;color:#fff;padding:.9rem 1.2rem;font-weight:700}
.modal .body{padding:1.3rem 1.2rem;line-height:1.5}
.modal footer{padding:0 1.2rem 1.2rem;text-align:right}
.modal button{background:#9b1b30;color:#fff;border:0;padding:.65rem 1.3rem;border-radius:4px;font-size:1rem;font-weight:600;cursor:pointer}
@keyframes pop{from{transform:scale(.9);opacity:0}to{transform:none;opacity:1}}
</style></head><body>
<form class="wrap" method="post" id="form">
<h1 id="title">Course Assessment Form™</h1>
<p class="note"><?= isset($_GET['why']) ? "You must assess the course before viewing grades. " : "" ?>Rate each statement. Your honesty is valued (not required).</p>
<?php foreach ($qs as $i=>$q): ?>
<div class="q" id="q<?= $i ?>"><?= ($i+1) ?>. <?= htmlspecialchars($q[0]) ?><br>
<?php foreach ($scale as $opt): ?><label><input type="radio" name="q<?= $i ?>" value="<?= htmlspecialchars($opt) ?>" required> <?= $opt ?></label><?php endforeach; ?>
</div>
<?php endforeach; ?>
<button type="submit" id="next">Submit &raquo;</button>
</form>

<a href="evil.php" class="next-btn">Next &rarr;</a>

<div id="veil"><div class="modal"><header>⚠️ We Caught Your Goofy Ahh</header>
<div class="body"><p style="margin:0">Your answers were not appropriate. Please read each question carefully for the reason.</p></div>
<footer><button id="back">Try again</button></footer>
</div></div>

<script>
const traps=<?= json_encode($traps) ?>;
const $=id=>document.getElementById(id);
$('title').addEventListener('click',e=>{if(e.detail===3)document.body.classList.toggle('readable')});
$('form').addEventListener('submit',e=>{
  for(const [i,t] of Object.entries(traps)){
    const v=(document.querySelector(`input[name=q${i}]:checked`)||{}).value;
    if(!t.ok.includes(v)){
      e.preventDefault();
      $('veil').classList.add('show');return;
    }
  }
});
$('back').onclick=()=>$('veil').classList.remove('show');
</script></body></html>
