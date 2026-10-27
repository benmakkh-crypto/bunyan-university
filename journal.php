<?php
/* مجلة جامعة بنيان العالمية العلمية المحكّمة */
require_once __DIR__ . '/inc/shell.php';
require_once __DIR__ . '/inc/settings.php';
$C = uwciv_contact();
uwciv_open('مجلة الجامعة', t('about'), 'دورية علمية محكّمة تنشر البحوث الأصيلة في العلوم الشرعية والعربية والإنسانية.');
$FIELDS = [['fa-quran', 'القرآن وعلومه'], ['fa-scroll', 'السنة وعلومها'], ['fa-balance-scale', 'الفقه والشريعة والقانون'], ['fa-star-and-crescent', 'العقيدة والفكر'], ['fa-pen-nib', 'اللغة العربية وآدابها'], ['fa-chalkboard-teacher', 'التربية والتعليم'], ['fa-coins', 'الاقتصاد الإسلامي'], ['fa-landmark', 'التاريخ والحضارة']];
$STEPS = [['fa-file-upload', 'الإرسال', 'يرسل الباحث بحثه بصيغة Word مع ملخص عربي وإنجليزي.'], ['fa-filter', 'الفحص الأولي', 'تتحقق هيئة التحرير من المطابقة والأصالة خلال أسبوعين.'], ['fa-user-secret', 'التحكيم السري', 'يُرسل البحث إلى محكّمَين متخصصَين دون ذكر الأسماء.'], ['fa-edit', 'التعديلات', 'يجري الباحث التعديلات المطلوبة ويعيد الإرسال.'], ['fa-check-double', 'القبول والنشر', 'يصدر خطاب القبول، ويُنشر البحث في العدد القادم.']];
$COND = ['أن يكون البحث أصيلًا لم يُنشر ولم يُقدّم للنشر في جهة أخرى.', 'الالتزام بمنهجية البحث العلمي وتوثيق المصادر والمراجع.', 'ألا يزيد البحث على 30 صفحة، بخط 14 للمتن و12 للحواشي.', 'إرفاق ملخص بالعربية والإنجليزية لا يتجاوز 250 كلمة، مع كلمات مفتاحية.', 'أن يُكتب بلغة سليمة خالية من الأخطاء.', 'يخضع كل بحث للتحكيم العلمي السري، وقرار هيئة التحرير نهائي.'];
?>
<style>
.jr-hero{margin:58px 0 26px;display:grid;grid-template-columns:1.2fr .8fr;gap:30px;align-items:center;background:#fff;border-radius:30px;padding:34px;box-shadow:var(--sh-3d);position:relative;overflow:hidden}
.jr-hero:before{content:"";position:absolute;inset:0 0 auto;height:6px;background:linear-gradient(90deg,var(--teal),var(--gold))}
.jr-hero small{color:var(--teal);font-weight:800;display:inline-flex;gap:8px;align-items:center}
.jr-hero h2{font-size:clamp(28px,3.6vw,42px);font-weight:900;color:var(--ink);margin:8px 0 12px;line-height:1.3}
.jr-hero p{color:var(--muted);line-height:2;margin:0 0 18px;font-size:16px}
.jr-meta{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px}
.jr-meta span{background:var(--mist);border-radius:12px;padding:8px 12px;font-size:13.5px;font-weight:800;color:var(--ink-3)}
.jr-meta i{color:var(--teal);margin-inline-end:6px}
.jr-cta{display:flex;gap:10px;flex-wrap:wrap}
.jr-cta a{display:inline-flex;gap:8px;align-items:center;border-radius:14px;padding:13px 20px;font-weight:900;text-decoration:none!important;background:var(--mist);color:var(--ink-3)!important}
.jr-cta a.p{background:linear-gradient(135deg,#2fa060,var(--ink-3));color:#fff!important;box-shadow:0 14px 26px -14px rgba(6,33,15,.7)}
.jr-cover{justify-self:center;width:260px;aspect-ratio:3/4.1;border-radius:8px 18px 18px 8px;background:linear-gradient(160deg,#11432a,#06210f);color:#fff;padding:26px 22px 22px 30px;display:flex;flex-direction:column;box-shadow:inset 10px 0 0 rgba(0,0,0,.25),0 40px 60px -30px rgba(6,33,15,.8);transform:perspective(1000px) rotateY(-14deg) rotate(2deg);transition:transform .6s;position:relative;overflow:hidden}
.jr-hero:hover .jr-cover{transform:perspective(1000px) rotateY(0) rotate(0)}
.jr-cover:after{content:"";position:absolute;inset:0;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='60' height='60'%3E%3Cpath d='M30 4l7 19 19 7-19 7-7 19-7-19-19-7 19-7z' fill='none' stroke='%23fff' stroke-opacity='.07'/%3E%3C/svg%3E")}
.jr-cover img{width:70px;position:relative;z-index:1}
.jr-cover small{font-size:10px;letter-spacing:.2em;color:#9fe0b8;margin-top:auto;position:relative;z-index:1}
.jr-cover b{font-size:24px;line-height:1.4;position:relative;z-index:1;font-family:'Amiri','Tajawal',serif!important}
.jr-cover span{display:flex;justify-content:space-between;border-top:1px solid rgba(255,255,255,.25);padding-top:10px;margin-top:14px;font-size:11.5px;color:#cfe6d7;position:relative;z-index:1}
.jr-sec{margin-bottom:34px;scroll-margin-top:120px}#cond,#issues{scroll-margin-top:120px}
.jr-sec>h3{font-size:24px;font-weight:900;color:var(--ink);margin:0 0 16px;display:flex;gap:10px;align-items:center}
.jr-sec>h3 i{color:var(--teal)}
.jr-fields{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}
.jr-fields div{background:#fff;border-radius:18px;padding:16px;box-shadow:var(--sh-1);display:flex;gap:12px;align-items:center;font-weight:800;color:var(--ink-3)}
.jr-fields i{width:42px;height:42px;border-radius:12px;display:grid;place-items:center;background:var(--cyan-soft);color:var(--teal);flex:none}
.jr-steps{display:grid;grid-template-columns:repeat(5,1fr);gap:12px;position:relative}
.jr-steps:before{content:"";position:absolute;top:30px;right:8%;left:8%;height:3px;background:linear-gradient(90deg,var(--gold),var(--teal));border-radius:3px}
.jr-steps div{position:relative;text-align:center;padding:0 6px}
.jr-steps i{width:60px;height:60px;border-radius:50%;display:grid;place-items:center;margin:0 auto 12px;background:#fff;border:3px solid var(--teal);color:var(--teal);font-size:21px;box-shadow:0 0 0 6px var(--mist);position:relative}
.jr-steps b{display:block;color:var(--ink);margin-bottom:4px}.jr-steps span{color:var(--muted);font-size:13.5px;line-height:1.7}
.jr-two{display:grid;grid-template-columns:1.2fr .8fr;gap:18px}
.jr-box{background:#fff;border-radius:22px;padding:22px;box-shadow:var(--sh-1)}
.jr-box h4{margin:0 0 12px;font-size:18px;font-weight:900;color:var(--ink);display:flex;gap:8px;align-items:center}.jr-box h4 i{color:var(--teal)}
.jr-cond{list-style:none;margin:0;padding:0;display:grid;gap:8px}
.jr-cond li{display:flex;gap:10px;font-size:14.5px;line-height:1.8}.jr-cond li i{color:var(--teal);margin-top:6px}
.jr-issue{display:flex;flex-direction:column;gap:10px;align-items:center;text-align:center;justify-content:center;height:100%}
.jr-issue i{font-size:44px;color:#bfd8c8}.jr-issue b{color:var(--ink);font-size:17px}.jr-issue span{color:var(--muted);line-height:1.8}
@media(max-width:992px){.jr-hero{grid-template-columns:1fr}.jr-fields{grid-template-columns:1fr 1fr}.jr-steps{grid-template-columns:1fr;gap:16px}.jr-steps:before{display:none}.jr-steps div{display:grid;grid-template-columns:60px 1fr;text-align:right;gap:12px;align-items:center}.jr-steps i{margin:0}.jr-two{grid-template-columns:1fr}}
@media(max-width:560px){.jr-hero{padding:24px 18px}.jr-fields{grid-template-columns:1fr}}
</style>
<section class="jr-hero">
  <div><small><i class="fas fa-newspaper"></i> دورية علمية محكّمة</small><h2>مجلة جامعة بنيان العالمية</h2>
    <p>منبر علمي محكّم لنشر البحوث الأصيلة في العلوم الشرعية والعربية والإنسانية، يلتزم بالتحكيم السري المزدوج وأخلاقيات النشر، وتصدر عن معهد البحوث والاستشارات.</p>
    <div class="jr-meta"><span><i class="fas fa-redo"></i>نصف سنوية</span><span><i class="fas fa-user-secret"></i>تحكيم سري مزدوج</span><span><i class="fas fa-language"></i>العربية والإنجليزية</span><span><i class="fas fa-unlock"></i>وصول مفتوح</span></div>
    <div class="jr-cta"><a class="p" href="mailto:info@biuedu.com?subject=<?= rawurlencode('بحث للنشر في مجلة الجامعة') ?>"><i class="fas fa-paper-plane"></i> أرسل بحثك</a><a href="/form2.php"><i class="far fa-question-circle"></i> استفسار</a><a href="/research.php"><i class="fas fa-microscope"></i> معهد البحوث</a></div></div>
  <div class="jr-cover" aria-hidden="true"><img src="/assets/images/logo-light.png" alt=""><small>JOURNAL OF BUNYAN INTERNATIONAL UNIVERSITY</small><b>مجلة جامعة<br>بنيان العالمية</b><span><em style="font-style:normal">مجلة علمية محكّمة</em><em style="font-style:normal">العدد الأول</em></span></div>
</section>

<section class="jr-sec" id="fields"><h3><i class="fas fa-th-large"></i> مجالات النشر</h3><div class="jr-fields"><?php foreach ($FIELDS as $f): ?><div><i class="fas <?= $f[0] ?>"></i><?= $f[1] ?></div><?php endforeach; ?></div></section>
<section class="jr-sec" id="steps"><h3><i class="fas fa-stream"></i> رحلة البحث من الإرسال إلى النشر</h3><div class="jr-steps"><?php foreach ($STEPS as $s): ?><div><i class="fas <?= $s[0] ?>"></i><span><b><?= $s[1] ?></b><?= $s[2] ?></span></div><?php endforeach; ?></div></section>
<section class="jr-sec jr-two">
  <div class="jr-box" id="cond"><h4><i class="fas fa-clipboard-list"></i> شروط النشر</h4><ul class="jr-cond"><?php foreach ($COND as $c): ?><li><i class="fas fa-check-circle"></i><?= $c ?></li><?php endforeach; ?></ul></div>
  <div class="jr-box" id="issues"><div class="jr-issue"><i class="fas fa-book-open"></i><b>الأعداد المنشورة</b><span>العدد الأول قيد الإعداد، ويستقبل البحوث الآن. تُنشر الأعداد هنا وفي <a href="/librarysystem/index.php">المكتبة الرقمية</a>.</span></div></div>
</section>
<?php uwciv_close(); ?>
