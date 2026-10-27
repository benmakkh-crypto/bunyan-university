<?php
/* معهد البحوث والاستشارات */
require_once __DIR__ . '/inc/shell.php';
$h = function ($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); };
$SECS = [
  ['about', 'fa-info-circle', 'نبذة عن المعهد'], ['mission', 'fa-bullseye', 'الرسالة والرؤية'], ['goals', 'fa-flag-checkered', 'أهداف المعهد'],
  ['services', 'fa-concierge-bell', 'خدمات المعهد'], ['dean', 'fa-quote-right', 'كلمة عميد المعهد'], ['admission', 'fa-clipboard-check', 'شروط القبول'],
  ['fees', 'fa-tag', 'تكلفة الدراسة'], ['contact', 'fa-paper-plane', 'اطلب خدمة بحثية'],
];
$COND = ['أن يكون المتقدم حاصلًا على مؤهل دراسي معتمد لا يقل عن الثانوية العامة أو ما يعادلها.', 'ألا يكون قد فُصل من أي مؤسسة تعليمية فصلًا أكاديميًا أو تأديبيًا.', 'أن يجيد اللغة العربية أو يُحوَّل إلى البرامج باللغات الأجنبية.', 'أن يجتاز المقابلة الشخصية.', 'أن يلتزم بأنظمة الجامعة ولوائحها الأكاديمية والسلوكية.', 'أن يسدد الرسوم المحددة في مواعيدها.', 'أن يكون القبول مشروطًا باستكمال الإجراءات النظامية.', 'أن يتعهد بصحة جميع البيانات والوثائق المقدمة، ويُلغى القبول إذا ثبت خلاف ذلك.'];
$FEES = [['الدبلوم', 300, '#0e9aa7', 'fa-seedling'], ['البكالوريوس', 500, '#12935a', 'fa-book-open'], ['الماجستير', 700, '#2c5fd0', 'fa-feather-alt'], ['الدكتوراه', 1000, '#9b1f45', 'fa-crown']];
$SERV = [['fa-microscope', 'البحوث والدراسات', 'دراسات علمية محكّمة في العلوم الشرعية والإنسانية والاجتماعية.'], ['fa-handshake', 'الاستشارات', 'استشارات أكاديمية وشرعية وتربوية وإدارية للمؤسسات.'], ['fa-chalkboard', 'الدورات التدريبية', 'برامج تدريبية لتطوير كفاءة العاملين في القطاعات المختلفة.'], ['fa-scroll', 'تحقيق المخطوطات', 'فهرسة المخطوطات وتحقيقها ونشرها وفق المناهج العلمية.'], ['fa-language', 'الترجمة العلمية', 'ترجمة البحوث والكتب بين العربية والإنجليزية والبنغالية والأردية.'], ['fa-chart-bar', 'المسوح والإحصاء', 'تصميم الاستبانات وتحليل البيانات وإعداد التقارير.']];
uwciv_open('معهد البحوث والاستشارات', t('centers') ?? 'المراكز', 'بيت خبرة علمية في البحث والاستشارات والتدريب، يخدم الجامعة والمجتمع.');
?>
<style>
.rs{display:grid;grid-template-columns:270px 1fr;gap:26px;margin-top:58px;align-items:start}
.rs-nav{position:sticky;top:84px;background:#fff;border-radius:24px;box-shadow:var(--sh-1);overflow:hidden}
.rs-nav-h{background:linear-gradient(135deg,#11432a,#06210f);color:#fff;padding:22px;text-align:center}
.rs-nav-h img{width:74px;height:74px;border-radius:50%;background:#fff;padding:8px;box-shadow:0 10px 20px -10px #000}
.rs-nav-h b{display:block;font-size:17px;margin-top:10px}
.rs-nav ul{list-style:none;margin:0;padding:10px}
.rs-nav a{display:flex;gap:10px;align-items:center;padding:10px 12px;border-radius:12px;font-weight:800;font-size:14px;color:var(--ink-3)!important;text-decoration:none!important;transition:.2s}
.rs-nav a i{width:30px;height:30px;border-radius:9px;display:grid;place-items:center;background:var(--mist);color:var(--teal);font-size:13px;transition:.2s}
.rs-nav a:hover{background:var(--mist)}
.rs-nav a.on{background:var(--cyan-soft);color:var(--teal)!important}.rs-nav a.on i{background:var(--teal);color:#fff}
.rs-main>section{background:#fff;border-radius:24px;box-shadow:var(--sh-1);padding:26px 28px;margin-bottom:18px;scroll-margin-top:90px;position:relative}
.rs-main h2{display:flex;gap:12px;align-items:center;font-size:22px;font-weight:900;color:var(--ink);margin:0 0 14px}
.rs-main h2 i{width:44px;height:44px;border-radius:13px;display:grid;place-items:center;background:linear-gradient(145deg,var(--teal),var(--ink-3));color:#fff;font-size:17px}
.rs-main p{line-height:2;color:#3b4d42;margin:0;font-size:15.5px}
.rs-hero{background:radial-gradient(500px 260px at 0% 0%,rgba(70,192,122,.25),transparent 60%),linear-gradient(150deg,#0b3119,#06210f)!important;color:#fff}
.rs-hero h2{color:#fff}.rs-hero p{color:#d6ebdd}
.rs-hero .num{display:flex;gap:24px;margin-top:20px}.rs-hero .num b{display:block;font-size:30px;font-weight:900;color:#7fe0a6}.rs-hero .num span{font-size:13px;color:#cfe6d7}
.rs-two{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.rs-two div{background:var(--mist);border-radius:18px;padding:18px;border-top:4px solid var(--c)}
.rs-two b{display:flex;gap:8px;align-items:center;color:var(--c);font-size:16px;margin-bottom:8px}
.rs-goals{list-style:none;margin:0;padding:0;counter-reset:g;display:grid;gap:10px}
.rs-goals li{counter-increment:g;display:flex;gap:12px;align-items:center;background:var(--mist);border-radius:14px;padding:12px 14px;font-weight:700;line-height:1.8}
.rs-goals li:before{content:counter(g);flex:none;width:32px;height:32px;border-radius:10px;background:var(--ink-3);color:#9fe0b8;display:grid;place-items:center;font-weight:900}
.rs-serv{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}
.rs-serv div{border:1px solid #e3ebe6;border-radius:18px;padding:16px;transition:.25s}
.rs-serv div:hover{border-color:var(--teal);transform:translateY(-3px);box-shadow:var(--sh-1)}
.rs-serv i{width:44px;height:44px;border-radius:12px;display:grid;place-items:center;background:var(--cyan-soft);color:var(--teal);font-size:18px;margin-bottom:8px}
.rs-serv b{display:block;color:var(--ink);margin-bottom:4px}.rs-serv span{color:var(--muted);font-size:13.5px;line-height:1.7}
.rs-quote{border-inline-start:4px solid var(--gold);background:linear-gradient(90deg,#fffaf0,#fff);border-radius:16px;padding:20px 22px;font-family:'Amiri','Tajawal',serif!important;font-size:17px!important;line-height:2.2!important}
.rs-quote:before{content:"\201D";font-size:60px;line-height:0;color:var(--gold);vertical-align:-24px;margin-inline-end:6px}
.rs-cond{list-style:none;margin:0;padding:0;display:grid;grid-template-columns:1fr 1fr;gap:10px}
.rs-cond li{display:flex;gap:10px;align-items:flex-start;font-size:14.5px;line-height:1.8;background:var(--mist);border-radius:12px;padding:10px 12px}
.rs-cond li i{color:var(--teal);margin-top:5px}
.rs-fees{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}
.rs-fees div{border-radius:18px;padding:18px;text-align:center;color:#fff;background:linear-gradient(160deg,var(--c),color-mix(in srgb,var(--c) 55%,#000));box-shadow:0 14px 26px -16px var(--c)}
.rs-fees i{font-size:22px;opacity:.85}.rs-fees b{display:block;font-size:30px;font-weight:900;margin:6px 0 2px}.rs-fees small{opacity:.9}
.rs-cta{display:flex;gap:10px;flex-wrap:wrap;margin-top:14px}
.rs-cta a{display:inline-flex;gap:8px;align-items:center;border-radius:14px;padding:12px 18px;font-weight:900;text-decoration:none!important;background:var(--mist);color:var(--ink-3)!important}
.rs-cta a.p{background:var(--teal);color:#fff!important}
@media(max-width:992px){.rs{grid-template-columns:1fr}.rs-nav{position:static}.rs-nav ul{display:flex;overflow-x:auto;gap:4px}.rs-nav a{white-space:nowrap}.rs-nav-h{display:none}.rs-serv,.rs-fees{grid-template-columns:1fr 1fr}.rs-cond,.rs-two{grid-template-columns:1fr}}
@media(max-width:560px){.rs-main>section{padding:20px 16px}.rs-serv{grid-template-columns:1fr}}
</style>
<div class="rs">
  <aside class="rs-nav"><div class="rs-nav-h"><img src="/assets/images/Logo2.png" alt=""><b>معهد البحوث والاستشارات</b></div>
    <ul><?php foreach ($SECS as $i => $s): ?><li><a href="#<?= $s[0] ?>" class="<?= $i ? '' : 'on' ?>"><i class="fas <?= $s[1] ?>"></i><?= $s[2] ?></a></li><?php endforeach; ?></ul></aside>
  <div class="rs-main">
    <section id="about" class="rs-hero"><h2><i class="fas fa-info-circle"></i> نبذة عن المعهد</h2>
      <p>هيّأت الجامعة لمعهد البحوث والاستشارات مواردها المادية والبشرية من الخبرات العلمية والبحثية والتقنية، مع إمكانية استقطاب الخبرات العلمية والاستشارية محليًا ودوليًا، خدمةً للأهداف الاستراتيجية للجامعة والمجتمع.</p>
      <div class="num"><div><b>6</b><span>مجالات خدمة</span></div><div><b>20</b><span>كلية شريكة</span></div><div><b>4</b><span>لغات</span></div></div></section>
    <section id="mission"><h2><i class="fas fa-bullseye"></i> الرسالة والرؤية</h2>
      <div class="rs-two"><div style="--c:var(--teal)"><b><i class="fas fa-envelope-open-text"></i> الرسالة</b><p>أن نكون بيت خبرة متميزًا في تقديم الخدمات البحثية والاستشارية والتدريبية.</p></div>
      <div style="--c:#2c5fd0"><b><i class="far fa-eye"></i> الرؤية</b><p>التعريف بإمكانات الجامعة وخبراتها، وتوظيف مواردها البحثية والاستشارية في بناء شراكات فاعلة مع القطاعين الحكومي والخاص.</p></div></div></section>
    <section id="goals"><h2><i class="fas fa-flag-checkered"></i> أهداف المعهد</h2>
      <ol class="rs-goals"><li>التعريف بالإمكانات البحثية والاستشارية للجامعة، وتقديم خدماتها لجميع القطاعات.</li><li>عقد الدورات التدريبية لتطوير كفاءة العاملين في كافة القطاعات.</li><li>التعاون العلمي مع المراكز البحثية والتقنية العالمية والإقليمية والوطنية.</li><li>دعم النشر العلمي المحكّم عبر <a href="/journal">مجلة الجامعة</a>.</li></ol></section>
    <section id="services"><h2><i class="fas fa-concierge-bell"></i> خدمات المعهد</h2>
      <div class="rs-serv"><?php foreach ($SERV as $s): ?><div><i class="fas <?= $s[0] ?>"></i><b><?= $s[1] ?></b><span><?= $s[2] ?></span></div><?php endforeach; ?></div></section>
    <section id="dean"><h2><i class="fas fa-quote-right"></i> كلمة عميد المعهد</h2>
      <p class="rs-quote">بسم الله الرحمن الرحيم، والحمد لله والصلاة والسلام على سيدنا محمد وعلى آله وصحبه وسلم، أما بعد: فإنّ حاجتنا إلى الدراسات والأبحاث تزداد شيئًا فشيئًا في ظل التحديات الجسيمة التي تمر بها المجتمعات والشعوب، والبحث العلمي بمناهجه وإجراءاته ضرورة لكل حقل من حقول المعرفة؛ فهو الباعث الحقيقي لصناعة الثقافة وابتكار المعرفة وتقديم الرؤى المستقبلية الثاقبة.</p></section>
    <section id="admission"><h2><i class="fas fa-clipboard-check"></i> شروط القبول</h2>
      <ul class="rs-cond"><?php foreach ($COND as $c): ?><li><i class="fas fa-check-circle"></i><?= $h($c) ?></li><?php endforeach; ?></ul></section>
    <section id="fees"><h2><i class="fas fa-tag"></i> تكلفة الدراسة</h2>
      <div class="rs-fees"><?php foreach ($FEES as $f): ?><div style="--c:<?= $f[2] ?>"><i class="fas <?= $f[3] ?>"></i><b><?= $f[1] ?> $</b><small>مرحلة <?= $f[0] ?></small></div><?php endforeach; ?></div>
      <div class="rs-cta"><a href="/colleges.php"><i class="fas fa-book"></i> المواد الدراسية والكليات</a><a href="/regulations-view.php?d=finance" target="_blank"><i class="fas fa-gavel"></i> اللائحة المالية</a></div></section>
    <section id="contact"><h2><i class="fas fa-paper-plane"></i> اطلب خدمة بحثية أو استشارية</h2>
      <p>للمؤسسات والباحثين: أرسل طلبك وسيتواصل معك فريق المعهد لتحديد نطاق العمل والتكلفة والمدة.</p>
      <div class="rs-cta"><a class="p" href="/form2.php"><i class="fas fa-paper-plane"></i> أرسل طلبك</a><a href="/apply"><i class="fas fa-user-plus"></i> التسجيل في المعهد</a><a href="/jobs"><i class="fas fa-user-tie"></i> انضم كباحث</a></div></section>
  </div>
</div>
<script>
(function(){var links=document.querySelectorAll('.rs-nav a');var io=new IntersectionObserver(function(es){es.forEach(function(e){if(e.isIntersecting){links.forEach(function(a){a.classList.toggle('on',a.getAttribute('href')==='#'+e.target.id)});}})},{rootMargin:'-40% 0px -55% 0px'});document.querySelectorAll('.rs-main>section').forEach(function(s){io.observe(s)});})();
</script>
<?php uwciv_close(); ?>
