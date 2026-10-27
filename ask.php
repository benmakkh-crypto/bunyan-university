<?php
require_once __DIR__ . '/inc/shell.php';

/* ✏️ الأسئلة الشائعة: [عنوان القسم, أيقونة, [[السؤال, الجواب], ...]] */
$FAQ = [
 ['القبول والتسجيل — أسئلة عامة', 'fa-user-graduate', [
   ['ما التخصصات المتاحة في الجامعة؟', 'تضم الجامعة كليات: القرآن الكريم، الدراسات الإسلامية، الشريعة والقانون، الفقه وأصوله، اللغة العربية، التربية، الإعلام، الاقتصاد والعلوم الإدارية، التقنية والبرمجة، اللغات الأجنبية، إضافة إلى معهد البحوث والاستشارات. تصفّحها من <a href="/depm.php">صفحة الكليات</a>.'],
   ['هل الدراسة في الجامعة عن بُعد بالكامل؟', 'نعم، الدراسة إلكترونية بالكامل: المحاضرات والاختبارات والشهادات تتم عبر المنصة التعليمية، ويمكنك الدراسة من أي مكان في العالم.'],
   ['ما المراحل الدراسية المتاحة؟', 'أربع مراحل: <a href="/depl.php">الدبلوم</a>، <a href="/depm.php">البكالوريوس</a>، <a href="/depmst.php">الماجستير</a>، <a href="/depdec.php">الدكتوراه</a>.'],
   ['كم تبلغ تكلفة الدراسة؟', 'الدبلوم 300 دولار، البكالوريوس 500 دولار، الماجستير 700 دولار، الدكتوراه 1000 دولار، وتفاصيل كل كلية في صفحتها تحت بند «تكلفة الدراسة».'],
 ]],
 ['القبول والتسجيل — مواعيد وشروط القبول', 'fa-calendar-check', [
   ['متى يبدأ التقديم؟ وهل يوجد قبول بين الفصلين؟', 'يبدأ القبول قبل بداية كل فصل دراسي، ويوجد قبول للفصل الثاني. المواعيد محدّثة في <a href="/calendar.php">التقويم الدراسي</a>.'],
   ['ما شروط القبول؟', '<ul><li>الحصول على مؤهل دراسي معتمد لا يقل عن الثانوية العامة أو ما يعادلها للبكالوريوس، والمؤهل السابق للمراحل العليا.</li><li>ألا يكون المتقدم مفصولاً من مؤسسة تعليمية فصلاً أكاديمياً أو تأديبياً.</li><li>إجادة اللغة العربية، أو التحويل إلى برامج اللغات الأجنبية.</li><li>اجتياز المقابلة الشخصية.</li><li>الالتزام بسداد الرسوم وأنظمة الجامعة.</li></ul>'],
   ['هل يشترط عمر معيّن للقبول؟', 'لا يوجد حد أعلى للعمر، فالجامعة ترحّب بطلاب العلم من جميع الأعمار.'],
 ]],
 ['القبول والتسجيل — التسجيل الإلكتروني', 'fa-laptop', [
   ['كيف أتقدّم بطلب الالتحاق؟', 'املأ نموذج <a href="/student/index.php">تسجيل الطلبة</a> بالبيانات المطلوبة وأرفق المستندات، وسيصلك الرد عبر البريد الإلكتروني.'],
   ['هل يتم إرسال الشهادة وصورة الهوية بالبريد أم عبر الموقع؟', 'تُرفع المستندات إلكترونياً عبر نموذج التسجيل في الموقع، ولا حاجة لإرسالها بالبريد إلا إذا طُلب ذلك.'],
   ['نسيت كلمة المرور، ماذا أفعل؟', 'استخدم خيار «نسيت كلمة المرور» في <a href="/syslog/index.php">صفحة تسجيل الدخول</a>، أو تواصل مع <a href="/form2.php">الدعم الفني</a>.'],
 ]],
 ['القبول والتسجيل — التحويل الخارجي', 'fa-exchange-alt', [
   ['هل يمكن التحويل من جامعة أخرى؟', 'نعم، يمكن التحويل بعد دراسة السجل الأكاديمي من عمادة القبول والتسجيل، وتُعادل المقررات المتوافقة مع الخطة الدراسية.'],
   ['ما المستندات المطلوبة للتحويل؟', 'السجل الأكاديمي المعتمد، وتوصيف المقررات المدروسة، وخطاب حسن السيرة من الجامعة السابقة.'],
 ]],
 ['الشؤون الأكاديمية — التأجيل والاعتذار وتغيير التخصص والانسحاب', 'fa-clipboard-list', [
   ['إذا سحبت ملفي من الجامعة، هل أستطيع التقديم مرة أخرى؟', 'سحب الملف خلال فترة القبول وقبل صدور الجدول الدراسي يُعدّ انسحاباً من القبول، ويستطيع المنسحب التقديم مرة أخرى في فترة قبول لاحقة وفق الشروط المعلنة.'],
   ['كيف أؤجّل الدراسة فصلاً دراسياً؟', 'يُقدَّم طلب التأجيل قبل بداية الفصل عبر <a href="/elctro.php">البوابة الإلكترونية</a>، بما لا يتجاوز فصلين دراسيين متتاليين.'],
   ['هل يمكن تغيير التخصص؟', 'نعم، بعد إتمام فصل دراسي واحد على الأقل وبموافقة الكلية المحوَّل إليها.'],
   ['ما آخر موعد للانسحاب من مقرر؟', 'يُحدَّد في <a href="/calendar.php">التقويم الدراسي</a> لكل فصل.'],
 ]],
 ['أسئلة تخص الشؤون الأكاديمية للطالب', 'fa-chalkboard-teacher', [
   ['أين أجد محاضراتي؟', 'في صفحات المحاضرات لكل مرحلة: <a href="/depmlact.php">البكالوريوس</a>، <a href="/depmstlact.php">الماجستير</a>، <a href="/declact.php">الدكتوراه</a>.'],
   ['كيف تتم الاختبارات؟', 'إلكترونياً عبر <a href="/onlineexam/index.php">نظام الامتحان الإلكتروني</a> في المواعيد المحددة بالتقويم.'],
   ['هل تُحتسب الدورات التدريبية ضمن المقررات؟', 'لا تُعتمد الدورة كمادة إلا بقرار من لجنة مختصة، ويُوضَّح ذلك عند الإعلان عن الدورة. تصفّح <a href="/courses/courses.php">الدورات المتاحة</a>.'],
 ]],
 ['النتائج والمعدلات — المعدل التراكمي والتقديرات والمعادلة', 'fa-chart-line', [
   ['متى تُعلن النتائج؟', 'بعد نهاية الاختبارات النهائية بنحو أسبوع، وفق <a href="/calendar.php">التقويم الدراسي</a>.'],
   ['ما معنى التقديرات؟', 'ممتاز (90–100)، جيد جداً (80–89)، جيد (70–79)، مقبول (60–69)، راسب (أقل من 60).'],
   ['كيف يُحسب المعدل التراكمي؟', 'مجموع (درجة كل مقرر × ساعاته المعتمدة) مقسوماً على مجموع الساعات المعتمدة لكل المقررات المدروسة.'],
   ['هل يمكنني الاعتراض على درجة؟', 'نعم، خلال أسبوع من إعلان النتيجة عبر <a href="/elctro.php">البوابة الإلكترونية</a>.'],
 ]],
 ['شؤون الطلبة — الخدمات الطلابية', 'fa-hands-helping', [
   ['ما الخدمات المتاحة للطلاب؟', 'الإرشاد الأكاديمي، <a href="/librarysystem/index.php">المكتبة الرقمية</a>، الدعم الفني، الدورات التدريبية، والمنح الدراسية للمتميزين وذوي الحاجة.'],
   ['كيف أتواصل مع إدارة الجامعة؟', 'عبر <a href="/elctro.php">إرسال طلب أو شكوى إلى رئيس الجامعة</a>، أو <a href="/form2.php">الدعم الفني</a>، أو <a href="/contact.php">صفحة التواصل</a>.'],
 ]],
 ['قسم الخريجين — الوثيقة والسجل الأكاديمي وتعديل الاسم ومرتبة الشرف', 'fa-award', [
   ['كيف أستلم وثيقة التخرج؟', 'تُصدر الوثيقة إلكترونياً ويمكن التحقق منها عبر <a href="/de/des.php">الشهادة الإلكترونية</a>، وتُرسل النسخة الورقية عند الطلب.'],
   ['كيف أعدّل اسمي في الوثيقة؟', 'بتقديم طلب مرفق بصورة جواز السفر أو الهوية عبر <a href="/form2.php">الدعم الفني</a>.'],
   ['ما شروط مرتبة الشرف؟', 'ألا يقل المعدل التراكمي عن «ممتاز» لمرتبة الشرف الأولى و«جيد جداً مرتفع» للثانية، وألا يكون الطالب قد رسب في أي مقرر، وأن يتخرج في المدة النظامية.'],
   ['هل توجد منصة للخريجين؟', 'نعم، <a href="/grad.php">منصة الخريجين</a> للتواصل والفرص والأخبار.'],
 ]],
];

/* شكل بطاقة كل قسم: [عنوان قصير, وصف, لون] — بنفس ترتيب الأقسام أعلاه */
$META = [
 ['أسئلة عامة', 'التخصصات والمراحل والرسوم', '#0f7f8c'],
 ['مواعيد وشروط القبول', 'متى وكيف تُقبل', '#12935a'],
 ['التسجيل الإلكتروني', 'التقديم وكلمة المرور', '#2c5fd0'],
 ['التحويل الخارجي', 'الانتقال من جامعة أخرى', '#5b4bb7'],
 ['التأجيل والانسحاب', 'الاعتذار وتغيير التخصص', '#c9962a'],
 ['الشؤون الأكاديمية', 'المحاضرات والاختبارات', '#0e9aa7'],
 ['النتائج والمعدلات', 'التقديرات والمعدل التراكمي', '#9b1f45'],
 ['الخدمات الطلابية', 'الإرشاد والمكتبة والمنح', '#1c7a5a'],
 ['الخريجون', 'الوثيقة ومرتبة الشرف', '#6c1430'],
];
$count = 0; foreach ($FAQ as $c) $count += count($c[2]);

uwciv_open(t('faq'), t('about'), $count . ' إجابة جاهزة عن القبول والدراسة والنتائج والخريجين — اختر موضوعك أو ابحث مباشرة.');
?>
<div class="f-search">
  <div class="f-search-box">
    <i class="fas fa-search"></i>
    <input type="search" id="f-q" placeholder="اكتب سؤالك... مثلاً: الرسوم، التحويل، الشهادة" autocomplete="off">
    <span class="f-hits" id="f-hits"></span>
  </div>
  <div class="f-pop">الأكثر بحثاً:
    <button data-q="الرسوم">الرسوم</button><button data-q="شروط القبول">شروط القبول</button>
    <button data-q="الشهادة">الشهادة</button><button data-q="المعدل">المعدل</button><button data-q="التأجيل">التأجيل</button>
  </div>
</div>

<div class="f-cats" id="f-cats">
  <?php foreach ($FAQ as $i => $c): $m = $META[$i]; ?>
  <button class="f-cat<?= $i === 0 ? ' is-on' : '' ?>" data-c="<?= $i ?>" style="--fc:<?= $m[2] ?>">
    <span class="f-cat-in">
      <span class="f-ic"><i class="fas <?= $c[1] ?>"></i></span>
      <b><?= $m[0] ?></b>
      <small><?= $m[1] ?></small>
      <span class="f-n"><?= count($c[2]) ?> أسئلة</span>
    </span>
  </button>
  <?php endforeach; ?>
</div>

<div class="f-stage">
  <?php foreach ($FAQ as $i => $c): $m = $META[$i]; ?>
  <section class="f-panel<?= $i === 0 ? ' is-on' : '' ?>" data-c="<?= $i ?>" style="--fc:<?= $m[2] ?>">
    <header class="f-panel-head">
      <span class="f-ic"><i class="fas <?= $c[1] ?>"></i></span>
      <div><h2><?= htmlspecialchars($c[0]) ?></h2><small><?= count($c[2]) ?> أسئلة في هذا القسم</small></div>
    </header>
    <?php foreach ($c[2] as $j => $qa): ?>
    <details class="f-qa"<?= $j === 0 ? ' open' : '' ?>>
      <summary><span class="f-qn"><?= $j + 1 ?></span><span class="f-qt"><?= htmlspecialchars($qa[0]) ?></span><span class="f-tg"></span></summary>
      <div class="f-ans">
        <img src="/assets/images/logo2.png" alt="" class="f-av">
        <div class="f-bubble"><?= $qa[1] ?></div>
      </div>
    </details>
    <?php endforeach; ?>
  </section>
  <?php endforeach; ?>
  <div class="f-none" id="f-none"><i class="far fa-comment-dots"></i><b>لم نجد إجابة لهذا السؤال</b><span>أرسله لنا وسيجيبك فريق الجامعة.</span><a class="u-btn" href="/form2.php">اسأل الدعم الفني</a></div>
</div>

<div class="f-cta">
  <a href="/form2.php" style="--fc:#0f7f8c"><i class="fas fa-headset"></i><b>الدعم الفني</b><small>مشكلات الدخول والمنصة</small></a>
  <a href="/form1.php" style="--fc:#9b1f45"><i class="fas fa-envelope-open-text"></i><b>راسل رئاسة الجامعة</b><small>طلب أو شكوى رسمية</small></a>
  <a href="/calendar.php" style="--fc:#c9962a"><i class="fas fa-calendar-alt"></i><b>التقويم الدراسي</b><small>كل المواعيد المهمة</small></a>
</div>

<script>
(function(){
  var cats = document.querySelectorAll('.f-cat'), panels = document.querySelectorAll('.f-panel'), q = document.getElementById('f-q'),
      hits = document.getElementById('f-hits'), none = document.getElementById('f-none');
  function show(i){
    cats.forEach(function(c){ c.classList.toggle('is-on', c.dataset.c === i); });
    panels.forEach(function(p){ p.classList.toggle('is-on', p.dataset.c === i); p.querySelectorAll('.f-qa').forEach(function(d){ d.style.display = ''; }); });
    none.style.display = 'none';
  }
  cats.forEach(function(c){ c.addEventListener('click', function(){
    q.value = ''; hits.textContent = ''; document.body.classList.remove('f-searching'); show(c.dataset.c);
    var st = document.querySelector('.f-stage'); window.scrollTo({top: st.getBoundingClientRect().top + scrollY - 150, behavior: 'smooth'});
  }); });
  function search(){
    var v = q.value.trim();
    if (!v) { document.body.classList.remove('f-searching'); hits.textContent = ''; var on = document.querySelector('.f-cat.is-on'); show(on ? on.dataset.c : '0'); return; }
    document.body.classList.add('f-searching'); var n = 0;
    panels.forEach(function(p){ var k = 0;
      p.querySelectorAll('.f-qa').forEach(function(d){ var hit = d.textContent.indexOf(v) > -1; d.style.display = hit ? '' : 'none'; if (hit) { k++; d.open = true; } });
      p.classList.toggle('is-on', k > 0); n += k; });
    hits.textContent = n ? n + ' نتيجة' : ''; none.style.display = n ? 'none' : 'flex';
  }
  q.addEventListener('input', search);
  document.querySelectorAll('.f-pop button').forEach(function(b){ b.addEventListener('click', function(){ q.value = b.dataset.q; search(); q.focus(); }); });
  // ميلان ثلاثي الأبعاد مع حركة المؤشر
  if (!matchMedia('(prefers-reduced-motion: reduce)').matches && matchMedia('(hover: hover)').matches) {
    cats.forEach(function(c){
      var inn = c.querySelector('.f-cat-in');
      c.addEventListener('mousemove', function(e){ var r = c.getBoundingClientRect(), x = (e.clientX - r.left) / r.width - .5, y = (e.clientY - r.top) / r.height - .5;
        inn.style.transform = 'rotateY(' + (x * 18) + 'deg) rotateX(' + (-y * 18) + 'deg) translateZ(10px)';
        c.style.setProperty('--gx', (x + .5) * 100 + '%'); c.style.setProperty('--gy', (y + .5) * 100 + '%'); });
      c.addEventListener('mouseleave', function(){ inn.style.transform = ''; });
    });
  }
})();
</script>
<?php uwciv_close();
