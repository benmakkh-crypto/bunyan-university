<?php
require_once __DIR__ . '/inc/shell.php';
require_once __DIR__ . '/inc/colleges.php';
list($nCol, $nDep) = uwciv_college_counts();

uwciv_open('دليل الكليات', t('colleges_m'), 'ثلاث كليات ومعهد لتعليم اللغة العربية، تضم ' . $nDep . ' أقسامًا وبرامج ومراحل — اختر كليتك واطّلع على أقسامها وبرامجها ومجالات العمل.');
?>
<link rel="stylesheet" href="/assets/css/uwciv-colleges.css?v=1">

<div class="k-stats">
  <div class="k-stat"><b>3</b><span>كليات ومعهد للغة العربية</span></div>
  <div class="k-stat"><b><?= $nDep ?></b><span>أقسام وبرامج</span></div>
  <div class="k-stat"><b>4</b><span>مراحل: دبلوم · بكالوريوس · ماجستير · دكتوراه</span></div>
  <div class="k-stat"><b>4</b><span>لغات: العربية · الإنجليزية · البنغالية · الأردية</span></div>
</div>

<div class="k-tools">
  <label class="k-search"><i class="fas fa-search"></i><input id="kq" type="search" placeholder="ابحث عن كلية أو قسم أو مهنة… مثل: الحديث، المحاسبة، مترجم" aria-label="بحث في الكليات"></label>
  <div class="k-chips" id="kg">
    <button class="k-chip on" data-g="">الكل</button>
    <?php foreach ($UWCIV_GROUPS as $gk => $g): ?><button class="k-chip" data-g="<?= $gk ?>" style="--cc:<?= $g[2] ?>"><i class="fas <?= $g[1] ?>"></i><?= $g[0] ?></button><?php endforeach; ?>
  </div>
  <div class="k-chips" id="ks">
    <?php foreach ($UWCIV_STAGES as $sk => $s): ?><button class="k-chip" data-s="<?= $sk ?>" style="--cc:<?= $s[1] ?>"><?= $s[0] ?></button><?php endforeach; ?>
  </div>
</div>

<?php $no = 0; foreach ($UWCIV_GROUPS as $gk => $g):
  $list = array_filter($UWCIV_COLLEGES, function ($c) use ($gk) { return $c['g'] === $gk; }); ?>
<section class="k-group" data-group="<?= $gk ?>">
  <div class="k-gh" style="--cc:<?= $g[2] ?>"><i class="fas <?= $g[1] ?>"></i><h2><?= $g[0] ?></h2><span><?= count($list) ?> كليات</span></div>
  <div class="k-grid">
    <?php foreach ($list as $slug => $c): $no++;
      $hay = $c['n'] . ' ' . $c['tag'] . ' ' . implode(' ', array_column($c['deps'], 0)) . ' ' . implode(' ', $c['jobs']); ?>
    <a class="k-card" href="<?= uwciv_college_url($slug) ?>" style="--cc:<?= $c['c'] ?>" data-st="<?= implode(' ', $c['st']) ?>" data-hay="<?= htmlspecialchars($hay) ?>">
      <div class="k-top"><span class="no"><?= sprintf('%02d', $no) ?></span><i class="fas <?= $c['i'] ?>"></i><h3><?= $c['n'] ?></h3></div>
      <div class="k-body">
        <p><?= $c['tag'] ?></p>
        <div class="k-deg"><?php foreach ($c['st'] as $s): ?><span style="--sc:<?= $UWCIV_STAGES[$s][1] ?>"><?= $UWCIV_STAGES[$s][0] ?></span><?php endforeach; ?></div>
        <div class="k-foot"><span><b><?= count($c['deps']) ?></b> أقسام علمية</span><span class="go"><i class="fas fa-arrow-left"></i></span></div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
</section>
<?php endforeach; ?>
<div class="k-empty" id="kempty"><i class="fas fa-search" style="font-size:30px;margin-bottom:10px"></i><br>لا توجد نتائج مطابقة — جرّب كلمة أخرى.</div>

<script>
(function(){
  var q=document.getElementById('kq'),g='',s='';
  function norm(t){return (t||'').replace(/[أإآ]/g,'ا').replace(/ة/g,'ه').replace(/ى/g,'ي').replace(/[ً-ْ]/g,'').toLowerCase();}
  function run(){var v=norm(q.value.trim()),any=false;
    document.querySelectorAll('.k-group').forEach(function(sec){var vis=0;
      sec.querySelectorAll('.k-card').forEach(function(c){
        var ok=(!g||sec.dataset.group===g)&&(!s||(' '+c.dataset.st+' ').indexOf(' '+s+' ')>-1)&&(!v||norm(c.dataset.hay).indexOf(v)>-1);
        c.style.display=ok?'':'none'; if(ok)vis++;});
      sec.style.display=vis?'':'none'; if(vis)any=true;});
    document.getElementById('kempty').style.display=any?'none':'block';}
  q.addEventListener('input',run);
  document.querySelectorAll('#kg .k-chip').forEach(function(b){b.onclick=function(){document.querySelectorAll('#kg .k-chip').forEach(function(x){x.classList.remove('on')});b.classList.add('on');g=b.dataset.g;run();};});
  document.querySelectorAll('#ks .k-chip').forEach(function(b){b.onclick=function(){var on=!b.classList.contains('on');document.querySelectorAll('#ks .k-chip').forEach(function(x){x.classList.remove('on')});if(on)b.classList.add('on');s=on?b.dataset.s:'';run();};});
  var p=new URLSearchParams(location.search); if(p.get('q')){q.value=p.get('q');run();}
  if(window.matchMedia('(hover:hover)').matches&&!window.matchMedia('(prefers-reduced-motion:reduce)').matches){
    document.querySelectorAll('.k-card').forEach(function(c){
      c.addEventListener('mousemove',function(e){var r=c.getBoundingClientRect(),x=(e.clientX-r.left)/r.width-.5,y=(e.clientY-r.top)/r.height-.5;c.style.transform='perspective(900px) rotateY('+(x*10)+'deg) rotateX('+(-y*10)+'deg) translateY(-6px)';});
      c.addEventListener('mouseleave',function(){c.style.transform='';});});
  }
})();
</script>
<?php uwciv_close(); ?>
