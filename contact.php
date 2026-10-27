<?php
require_once __DIR__ . '/inc/shell.php';
require_once __DIR__ . '/inc/settings.php';
$C = uwciv_contact();
$soc = uwciv_settings()['social'] ?? [];
uwciv_open(t('contact'), null, 'نسعد بتواصلك — اختر الطريقة الأنسب لك، ويرد عليك فريق الجامعة في أقرب وقت.');
?>
<style>
.ct-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:18px;margin-bottom:26px}
.ct-card{position:relative;display:flex;flex-direction:column;gap:6px;background:#fff;border-radius:22px;padding:26px 22px;box-shadow:var(--sh-3d);text-decoration:none!important;color:var(--text)!important;transition:transform .35s cubic-bezier(.2,.8,.2,1),box-shadow .35s;overflow:hidden;border-top:4px solid var(--cc)}
.ct-card:hover{transform:translateY(-8px);box-shadow:var(--sh-lift)}
.ct-card:after{content:"";position:absolute;width:150px;height:150px;border-radius:50%;background:color-mix(in srgb,var(--cc) 10%,transparent);left:-50px;bottom:-60px}
.ct-ic{width:58px;height:58px;border-radius:17px;display:grid;place-items:center;background:linear-gradient(145deg,var(--cc),color-mix(in srgb,var(--cc) 60%,#000));color:#fff;font-size:24px;margin-bottom:8px;transform:rotate(-6deg);box-shadow:0 12px 22px -10px var(--cc)}
.ct-ic i{color:#fff!important}
.ct-card b{font-size:18px;color:var(--ink)}
.ct-card span{color:var(--muted);font-size:14.5px;line-height:1.7}
.ct-card em{font-style:normal;font-weight:900;color:var(--cc);font-size:17px;margin-top:4px;font-family:'Plus Jakarta Sans',sans-serif!important}
.ct-two{display:grid;grid-template-columns:1.2fr 1fr;gap:18px}
.ct-box{background:#fff;border-radius:22px;padding:24px;box-shadow:var(--sh-1)}
.ct-box h3{font-size:19px;font-weight:900;color:var(--ink);margin:0 0 12px;display:flex;gap:10px;align-items:center}
.ct-box h3 i{color:var(--teal)}
.ct-map{border-radius:16px;min-height:240px;display:grid;place-items:center;text-align:center;background:linear-gradient(135deg,var(--ink-2),var(--teal));color:#fff;padding:24px;position:relative;overflow:hidden}
.ct-map i{font-size:44px;color:var(--gold);margin-bottom:10px;animation:ctPin 2.4s ease-in-out infinite}
@keyframes ctPin{0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}
.ct-map a{display:inline-block;margin-top:12px;background:var(--gold);color:#2a1f05!important;border-radius:30px;padding:10px 20px;font-weight:900;text-decoration:none!important}
.ct-mapwrap{position:relative;border-radius:18px;overflow:hidden;box-shadow:var(--sh-1);min-height:340px}
.ct-mapwrap iframe{display:block;width:100%;height:340px;border:0;filter:saturate(1.1)}
.ct-maplink{position:absolute;inset:0 0 80px 0;z-index:1}
.ct-pin{position:absolute;z-index:2;right:12px;left:12px;bottom:12px;background:rgba(6,33,15,.92);backdrop-filter:blur(6px);color:#fff;border-radius:16px;padding:12px 14px;display:flex;gap:12px;align-items:center;box-shadow:var(--sh-lift)}
.ct-pin>i{font-size:26px;color:var(--gold);animation:ctPin 2.4s ease-in-out infinite}
.ct-pin b{display:block;font-size:15px}.ct-pin small{color:#bfe0c9;font-size:12.5px}
.ct-pin a{margin-inline-start:auto;background:var(--gold);color:#2a1f05!important;border-radius:30px;padding:9px 16px;font-weight:900;text-decoration:none!important;white-space:nowrap}
.ct-pin a i{color:inherit;font-size:14px}
@media(max-width:560px){.ct-pin{flex-wrap:wrap}.ct-pin a{margin:0;width:100%;text-align:center}}
.ct-hours li{display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px dashed #e2ebe5;font-size:15px}
.ct-hours{list-style:none;margin:0 0 16px;padding:0}
.ct-soc{display:flex;flex-wrap:wrap;gap:10px}
.ct-soc a{width:46px;height:46px;border-radius:14px;display:grid;place-items:center;background:var(--mist);color:var(--teal)!important;font-size:19px;transition:.25s}
.ct-soc a:hover{background:var(--teal);color:#fff!important;transform:translateY(-3px)}
@media(max-width:992px){.ct-grid{grid-template-columns:1fr 1fr}.ct-two{grid-template-columns:1fr}}
@media(max-width:560px){.ct-grid{grid-template-columns:1fr}}
</style>

<div class="ct-grid">
  <a class="ct-card" style="--cc:#25a35a" href="https://wa.me/<?= $C['digits'] ?>" target="_blank" rel="noopener"><span class="ct-ic"><i class="fab fa-whatsapp"></i></span><b>واتساب</b><span>للتواصل والاستفسار السريع</span><em dir="ltr"><?= $C['pretty'] ?></em></a>
  <a class="ct-card" style="--cc:#2c5fd0" href="tel:+<?= $C['digits'] ?>"><span class="ct-ic"><i class="fas fa-phone-alt"></i></span><b>اتصال مباشر</b><span>يسعدنا خدمتكم طوال أيام الأسبوع</span><em dir="ltr"><?= $C['pretty'] ?><?php if (!empty($C['phone2']) && preg_replace('/\D/', '', $C['phone2']) !== $C['digits']): ?><br><?= htmlspecialchars($C['phone2']) ?><?php endif; ?></em></a>
  <a class="ct-card" style="--cc:#c9962a" href="mailto:info@biuedu.com"><span class="ct-ic"><i class="fas fa-envelope"></i></span><b>البريد الإلكتروني</b><span>للمراسلات الرسمية والطلبات</span><em dir="ltr">info@biuedu.com<br>info@bonyan.co.id</em></a>
  <a class="ct-card" style="--cc:#0e9aa7" href="/form2.php"><span class="ct-ic"><i class="fas fa-headset"></i></span><b>الدعم الفني</b><span>مشكلة في الدخول أو المنصة أو الامتحانات</span><em>أرسل طلبك ←</em></a>
  <a class="ct-card" style="--cc:#9b1f45" href="/form1.php"><span class="ct-ic"><i class="fas fa-envelope-open-text"></i></span><b>طلب أو شكوى لرئيس الجامعة</b><span>تُرفع مباشرة إلى إدارة الجامعة</span><em>اكتب رسالتك ←</em></a>
  <a class="ct-card" style="--cc:#12935a" href="/student/index.php"><span class="ct-ic"><i class="fas fa-user-plus"></i></span><b>القبول والتسجيل</b><span>قدّم طلب التحاقك إلكترونيًا</span><em>سجّل معنا ←</em></a>
</div>

<div class="ct-two">
  <div class="ct-box">
    <h3><i class="fas fa-map-marker-alt"></i> موقع الجامعة</h3>
    <div class="ct-mapwrap">
      <a class="ct-maplink" href="<?= htmlspecialchars($C['location']) ?>" target="_blank" rel="noopener" aria-label="افتح الموقع في خرائط Google"></a>
      <iframe src="<?= htmlspecialchars($C['embed']) ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="موقع جامعة بنيان العالمية على الخريطة" allowfullscreen></iframe>
      <div class="ct-pin"><i class="fas fa-map-marker-alt"></i><div><b>جامعة بنيان العالمية</b><small><?= htmlspecialchars($C['address']) ?><?php if ($C['lat'] !== null): ?> · <span dir="ltr"><?= round($C['lat'], 5) ?>, <?= round($C['lng'], 5) ?></span><?php endif; ?></small></div>
        <a href="<?= htmlspecialchars($C['location']) ?>" target="_blank" rel="noopener"><i class="fas fa-directions"></i> الاتجاهات</a></div>
    </div>
  </div>
  <div class="ct-box">
    <h3><i class="far fa-clock"></i> أوقات الرد</h3>
    <ul class="ct-hours">
      <li><span>السبت – الخميس</span><b>9:00 ص – 5:00 م</b></li>
      <li><span>الجمعة</span><b>إجازة</b></li>
      <li><span>البريد ونماذج الموقع</span><b>على مدار الساعة</b></li>
    </ul>
    <h3><i class="fas fa-share-alt"></i> تابعنا</h3>
    <div class="ct-soc">
      <?php $any = false; foreach (uwciv_social_defs() as $k => $d): if (empty($soc[$k])) continue; $any = true; ?><a href="<?= htmlspecialchars($soc[$k]) ?>" target="_blank" rel="noopener" aria-label="<?= $d[0] ?>"><i class="<?= $d[1] ?>"></i></a><?php endforeach; ?>
      <a href="https://wa.me/<?= $C['digits'] ?>" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
      <a href="mailto:info@biuedu.com" aria-label="Email"><i class="fas fa-envelope"></i></a>
    </div>
  </div>
</div>
<?php uwciv_close(); ?>
