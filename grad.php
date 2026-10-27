<?php
/* وحدة الخريجين */
require_once __DIR__ . '/db_config.php';
require_once __DIR__ . '/inc/shell.php';
require_once __DIR__ . '/inc/settings.php';
$news = [];
try { $db = db_pdo('logweb'); $news = $db->query('SELECT id, date, title, summary, class, image FROM postgrad ORDER BY id DESC LIMIT 12')->fetchAll(PDO::FETCH_ASSOC); } catch (Throwable $e) {}
$C = uwciv_contact();
$h = function ($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); };
uwciv_open('وحدة الخريجين', t('about'), 'نرافق خريجينا بعد التخرج: وثائق، فرص عمل، تدريب، وشبكة علاقات تمتد مدى الحياة.');
?>
<style>
.gr-hero{margin:58px 0 24px;display:grid;grid-template-columns:1.1fr .9fr;gap:24px;align-items:stretch}
.gr-intro{background:radial-gradient(500px 260px at 100% 0%,rgba(70,192,122,.28),transparent 60%),linear-gradient(150deg,#0b3119,#06210f);color:#fff;border-radius:28px;padding:32px;position:relative;overflow:hidden;box-shadow:var(--sh-lift)}
.gr-intro:after{content:"\f19d";font-family:"Font Awesome 5 Free";font-weight:900;position:absolute;left:24px;bottom:-30px;font-size:180px;color:rgba(255,255,255,.05)}
.gr-intro small{color:#9fe0b8;font-weight:800}
.gr-intro h2{color:#fff;font-size:clamp(28px,3.6vw,42px);font-weight:900;margin:8px 0 12px}
.gr-intro p{color:#d6ebdd;line-height:2;margin:0;position:relative;z-index:1}
.gr-kpi{display:flex;gap:24px;margin-top:20px;position:relative;z-index:1}.gr-kpi b{display:block;font-size:30px;font-weight:900;color:#7fe0a6}.gr-kpi span{font-size:13px;color:#cfe6d7}
.gr-quick{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.gr-quick a{display:flex;flex-direction:column;gap:8px;background:#fff;border-radius:22px;padding:20px;box-shadow:var(--sh-1);text-decoration:none!important;color:var(--text)!important;transition:.25s;border-bottom:4px solid var(--c)}
.gr-quick a:hover{transform:translateY(-4px);box-shadow:var(--sh-lift)}
.gr-quick i{width:48px;height:48px;border-radius:14px;display:grid;place-items:center;font-size:20px;color:var(--c);background:color-mix(in srgb,var(--c) 11%,#fff)}
.gr-quick b{color:var(--ink);font-size:16px}.gr-quick small{color:var(--muted);line-height:1.7}
.gr-sec{margin-bottom:34px}
.gr-h{display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:16px;gap:12px;flex-wrap:wrap}
.gr-h h3{font-size:26px;font-weight:900;color:var(--ink);margin:0}
.gr-h a{font-weight:800}
.gr-news{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:18px}
.gr-n{background:#fff;border-radius:22px;overflow:hidden;box-shadow:var(--sh-1);display:flex;flex-direction:column;transition:.3s;text-decoration:none!important;color:var(--text)!important}
.gr-n:hover{transform:translateY(-5px);box-shadow:var(--sh-lift)}
.gr-n .img{aspect-ratio:16/10;background:#dfeae3 center/cover;position:relative}
.gr-n .img span{position:absolute;top:12px;right:12px;background:rgba(6,33,15,.85);color:#fff;border-radius:20px;padding:4px 12px;font-size:12.5px;font-weight:800}
.gr-n .b{padding:16px 18px 18px;display:flex;flex-direction:column;gap:8px;flex:1}
.gr-n small{color:var(--teal);font-weight:800;font-size:12.5px}
.gr-n h4{margin:0;font-size:17px;font-weight:900;color:var(--ink);line-height:1.5}
.gr-n p{margin:0;color:var(--muted);font-size:14px;line-height:1.8}
.gr-n em{margin-top:auto;font-style:normal;color:var(--teal);font-weight:800;font-size:13.5px}
.gr-serv{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}
.gr-serv div{background:#fff;border-radius:20px;padding:20px;box-shadow:var(--sh-1)}
.gr-serv i{font-size:22px;color:var(--teal);margin-bottom:10px;display:block}
.gr-serv b{display:block;color:var(--ink);margin-bottom:6px}.gr-serv span{color:var(--muted);font-size:14px;line-height:1.8}
.gr-cta{display:grid;grid-template-columns:1fr auto;gap:20px;align-items:center;background:#fff;border-radius:24px;padding:24px 28px;box-shadow:var(--sh-1);border-inline-start:5px solid var(--gold)}
.gr-cta h3{margin:0 0 6px;font-size:21px;font-weight:900;color:var(--ink)}.gr-cta p{margin:0;color:var(--muted)}
.gr-cta div:last-child{display:flex;gap:10px;flex-wrap:wrap}
.gr-cta a{display:inline-flex;gap:8px;align-items:center;border-radius:14px;padding:12px 18px;font-weight:900;text-decoration:none!important;background:var(--mist);color:var(--ink-3)!important}
.gr-cta a.p{background:var(--teal);color:#fff!important}
.gr-empty{background:#fff;border-radius:20px;padding:30px;text-align:center;color:var(--muted);border:1px dashed #cfdcd4}
@media(max-width:992px){.gr-hero{grid-template-columns:1fr}.gr-serv{grid-template-columns:1fr 1fr}.gr-cta{grid-template-columns:1fr}}
@media(max-width:560px){.gr-quick,.gr-serv{grid-template-columns:1fr}.gr-intro{padding:24px 18px}}
</style>
<section class="gr-hero">
  <div class="gr-intro"><small><i class="fas fa-user-graduate"></i> مجتمع الخريجين</small><h2>وحدة الخريجين</h2>
    <p>تتابع الوحدة شؤون الخريجين وجهات عملهم، وتقيس مواءمة تخصصاتهم لاحتياجات سوق العمل، وتتلقى مقترحاتهم حول البرامج بالتنسيق مع الأقسام ووحدة الجودة. كما توفر لهم فرص التدريب والتوظيف، وتعرّف القطاعين العام والخاص بكفاءات الجامعة كل عام.</p>
    <div class="gr-kpi"><div><b>100+</b><span>خريج</span></div><div><b><?= count($news) ?></b><span>خبرًا وحفلًا</span></div><div><b>20</b><span>كلية</span></div></div></div>
  <div class="gr-quick">
    <a href="/pdf_grad/index.php" style="--c:#1f7a45"><i class="fas fa-certificate"></i><b>وثيقة التخرج</b><small>اعرض وثيقتك وحمّلها برقم المرجع.</small></a>
    <a href="/jobs" style="--c:#2c5fd0"><i class="fas fa-briefcase"></i><b>فرص العمل</b><small>انضم إلى فريق الجامعة أو هيئة التدريس.</small></a>
    <a href="/student/index.php" style="--c:#9b1f45"><i class="fas fa-user-graduate"></i><b>أكمل دراستك</b><small>الماجستير والدكتوراه لخريجي الجامعة.</small></a>
    <a href="/form2.php" style="--c:#c9962a"><i class="fas fa-id-card"></i><b>حدّث بياناتك</b><small>أخبرنا بجهة عملك وإنجازاتك.</small></a>
  </div>
</section>

<section class="gr-sec"><div class="gr-h"><h3>أخبار الخريجين وحفلات التخرج</h3><a href="/blgrad/index.php">كل الأخبار ←</a></div>
  <?php if (!$news): ?><div class="gr-empty">تُنشر أخبار الخريجين هنا قريبًا.</div><?php else: ?>
  <div class="gr-news"><?php foreach ($news as $n): ?>
    <a class="gr-n" href="/blgrad/view.php?id=<?= (int) $n['id'] ?>"><div class="img" style="background-image:url('/blgrad/img/<?= rawurlencode($n['image']) ?>')"><?php if ($n['class']): ?><span><?= $h($n['class']) ?></span><?php endif; ?></div>
      <div class="b"><small><i class="far fa-calendar"></i> <?= $h($n['date']) ?></small><h4><?= $h($n['title']) ?></h4><p><?= $h(mb_strimwidth($n['summary'], 0, 150, '…')) ?></p><em>اقرأ الخبر <i class="fas fa-arrow-left"></i></em></div></a>
  <?php endforeach; ?></div><?php endif; ?>
</section>

<section class="gr-sec"><div class="gr-h"><h3>خدمات الوحدة</h3></div>
  <div class="gr-serv">
    <div><i class="fas fa-route"></i><b>متابعة المسار المهني</b><span>رصد جهات عمل الخريجين وطبيعة وظائفهم.</span></div>
    <div><i class="fas fa-chalkboard-teacher"></i><b>التدريب والتأهيل</b><span>دورات ومهارات تزيد فرص التوظيف.</span></div>
    <div><i class="fas fa-handshake"></i><b>الربط بسوق العمل</b><span>تعريف المؤسسات بكفاءات الخريجين.</span></div>
    <div><i class="fas fa-comments"></i><b>آراء الخريجين</b><span>مقترحاتكم تطوّر برامج الجامعة.</span></div>
  </div></section>

<section class="gr-cta"><div><h3>هل أنت من خريجي الجامعة؟</h3><p>شاركنا قصة نجاحك وجهة عملك، لنفخر بك ونعرّف بك.</p></div>
  <div><a class="p" href="/form2.php"><i class="fas fa-paper-plane"></i> شارك قصتك</a><a href="https://wa.me/<?= $C['digits'] ?>" target="_blank" rel="noopener"><i class="fab fa-whatsapp"></i> تواصل معنا</a></div></section>
<?php uwciv_close(); ?>
