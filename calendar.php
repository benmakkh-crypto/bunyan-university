<?php
require_once __DIR__ . '/inc/shell.php';
/*
 * ✏️ التقويم الدراسي — يُعدَّل من لوحة التحكم (التقويم الرسمي)، والافتراضي في inc/calendar_data.php
 * النوع: reg تسجيل · study دراسة · exam اختبارات · result نتائج · holiday إجازة · event مناسبة
 */
require __DIR__ . '/inc/calendar_data.php';
require_once __DIR__ . '/inc/settings.php';
$__cs = uwciv_settings();
if (!empty($__cs['calendar']) && is_array($__cs['calendar'])) { $TERMS = $__cs['calendar']; if (!empty($__cs['year'])) $YEAR = $__cs['year']; $__official = true; }
$TYPES = [
  'reg'     => ['#0f7f8c', 'fa-user-edit',      'تسجيل'],
  'study'   => ['#12935a', 'fa-book-reader',    'دراسة'],
  'exam'    => ['#9b1f45', 'fa-file-signature', 'اختبارات'],
  'result'  => ['#2c5fd0', 'fa-poll',           'نتائج'],
  'holiday' => ['#c9962a', 'fa-umbrella-beach', 'إجازة'],
  'event'   => ['#5b4bb7', 'fa-star',           'مناسبة'],
];
$today = date('Y-m-d');
$EV = []; $first = null; $last = null; $now = []; $next = null; $cnt = array_fill_keys(array_keys($TYPES), 0);
foreach ($TERMS as $ti => $tm) foreach ($tm[1] as $e) {
    $end = $e[3] ?: $e[2]; $ty = isset($TYPES[$e[0]]) ? $e[0] : 'event';
    $EV[] = ['ty' => $ty, 't' => $e[1], 's' => $e[2], 'e' => $end, 'n' => $e[4] ?? '', 'term' => $ti];
    $cnt[$ty]++;
    if (!$first || $e[2] < $first) $first = $e[2]; if (!$last || $end > $last) $last = $end;
    if ($e[2] <= $today && $today <= $end) $now[] = $e;
    if ($e[2] > $today && (!$next || $e[2] < $next[2])) $next = $e;
}
$span = max(1, strtotime($last) - strtotime($first));
$yearPct = $today <= $first ? 0 : ($today >= $last ? 100 : round((strtotime($today) - strtotime($first)) / $span * 100));
$pos = function ($d) use ($first, $span) { return max(0, min(100, (strtotime($d) - strtotime($first)) / $span * 100)); };
$upcoming = array_values(array_filter($EV, function ($e) use ($today) { return $e['e'] >= $today; }));
usort($upcoming, function ($a, $b) { return strcmp($a['s'], $b['s']); });
$ar = function ($d, $y = true) {
    $m = ['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
    $t = strtotime($d); return (int) date('j', $t) . ' ' . $m[(int) date('n', $t) - 1] . ($y ? ' ' . date('Y', $t) : '');
};
$days = function ($a, $b) { return (int) round((strtotime($b) - strtotime($a)) / 86400); };
$h = function ($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); };
$MONTHS = []; for ($d = strtotime(date('Y-m-01', strtotime($first))); $d <= strtotime($last); $d = strtotime('+1 month', $d)) $MONTHS[] = date('Y-m', $d);
uwciv_open(t('calendar_m'), t('colleges_m'), 'العام الجامعي ' . $YEAR . ' — كل مواعيد التسجيل والدراسة والاختبارات والإجازات في مكان واحد.');
?>
<style>
.kc{margin-top:58px}
.kc-hero{display:grid;grid-template-columns:minmax(0,1.35fr) minmax(0,1fr);gap:18px;margin-bottom:18px}
.kc-now{position:relative;overflow:hidden;isolation:isolate;border-radius:28px;padding:26px 28px;color:#fff;background:radial-gradient(500px 280px at 100% 0,rgba(70,192,122,.35),transparent 60%),radial-gradient(400px 300px at 0 100%,rgba(201,150,42,.25),transparent 60%),linear-gradient(140deg,#0b3119,#041a0b);box-shadow:var(--sh-lift);display:grid;grid-template-columns:auto minmax(0,1fr);gap:24px;align-items:center}
.kc-now:before{content:"";position:absolute;inset:0;z-index:-1;background:url(/assets/images/pattern-islamic.svg) 0 0/80px;opacity:.13}
.kc-ring{width:168px;height:168px;border-radius:50%;display:grid;place-items:center;background:conic-gradient(#e8c46a calc(var(--p)*1%),rgba(255,255,255,.12) 0);position:relative;flex:none}
.kc-ring:after{content:"";position:absolute;inset:-8px;border-radius:50%;border:1px dashed rgba(232,196,106,.35);animation:kcSpin 40s linear infinite}
@keyframes kcSpin{to{transform:rotate(360deg)}}
.kc-ring div{width:134px;height:134px;border-radius:50%;background:#0b3119;display:grid;place-items:center;text-align:center;box-shadow:inset 0 0 0 1px rgba(255,255,255,.08)}
.kc-ring b{display:block;font-size:38px;font-weight:900;line-height:1;color:#fff}.kc-ring span{font-size:12px;color:#bfe3cc;display:block;margin-top:4px}
.kc-now small{color:#9fe0b8;font-weight:800;display:inline-flex;gap:8px;align-items:center}
.kc-now small i.live{width:9px;height:9px;border-radius:50%;background:#46e08a;box-shadow:0 0 0 0 rgba(70,224,138,.7);animation:kcLive 1.6s infinite}
@keyframes kcLive{70%{box-shadow:0 0 0 10px rgba(70,224,138,0)}100%{box-shadow:0 0 0 0 rgba(70,224,138,0)}}
.kc-now h2{color:#fff;font-size:clamp(22px,2.6vw,30px);font-weight:900;margin:8px 0 6px;line-height:1.4}
.kc-now p{margin:0;color:#cfe6d7}
.kc-nowl{display:flex;gap:8px;flex-wrap:wrap;margin-top:12px}
.kc-nowl span{display:inline-flex;gap:6px;align-items:center;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);border-radius:12px;padding:6px 12px;font-weight:700;font-size:13.5px}
.kc-nowl span i{color:var(--c)}
.kc-side{display:grid;gap:18px}
.kc-next{background:#fff;border-radius:24px;padding:20px 22px;box-shadow:var(--sh-3d);position:relative;overflow:hidden}
.kc-next:after{content:"";position:absolute;width:150px;height:150px;left:-50px;bottom:-60px;background:url(/assets/images/rosette-gold.svg) center/contain no-repeat;opacity:.18}
.kc-next small{color:var(--muted);font-weight:800}.kc-next b.t{display:block;font-size:19px;font-weight:900;color:var(--ink);margin:4px 0 12px}
.kc-cd{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;direction:ltr}
.kc-cd span{background:linear-gradient(180deg,#f4faf6,#e7f2eb);border-radius:14px;padding:8px 4px;text-align:center;font-size:11.5px;color:var(--muted);font-weight:700;border:1px solid #e1ede4}
.kc-cd b{display:block;font-size:26px;font-weight:900;color:var(--ink-2);font-variant-numeric:tabular-nums;line-height:1.2}
.kc-date{background:#fff;border-radius:24px;padding:16px 20px;box-shadow:var(--sh-1);display:flex;gap:14px;align-items:center}
.kc-date .d{width:62px;height:66px;border-radius:16px;overflow:hidden;text-align:center;box-shadow:0 8px 16px -10px rgba(0,0,0,.4);flex:none;background:#fff;border:1px solid #e1ede4}
.kc-date .d small{display:block;background:#9b1f45;color:#fff;font-size:11px;font-weight:800;padding:2px 0}
.kc-date .d b{font-size:28px;font-weight:900;color:var(--ink);line-height:44px}
.kc-date div:last-child b{display:block;color:var(--ink);font-size:16px}.kc-date div:last-child span{color:var(--muted);font-size:13.5px}
.kc-bar{display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin:4px 0 16px}
.kc-views{display:inline-flex;background:#fff;border-radius:16px;padding:5px;box-shadow:var(--sh-1)}
.kc-views button{border:0;background:none;border-radius:12px;padding:9px 16px;font-weight:900;font-family:inherit;color:var(--muted);cursor:pointer;display:inline-flex;gap:7px;align-items:center;font-size:14px}
.kc-views button.on{background:var(--ink-3);color:#fff;box-shadow:0 8px 16px -10px rgba(6,33,15,.8)}
.kc-chips{display:flex;gap:6px;flex-wrap:wrap}
.kc-chips button{border:1px solid #dfe9e3;background:#fff;border-radius:30px;padding:7px 12px;font-weight:800;font-size:13px;color:var(--ink-3);cursor:pointer;font-family:inherit;display:inline-flex;gap:6px;align-items:center;transition:.2s}
.kc-chips button i{width:10px;height:10px;border-radius:50%;background:var(--cc)}
.kc-chips button em{font-style:normal;background:var(--mist);border-radius:20px;padding:0 7px;font-size:11.5px}
.kc-chips button.on{background:var(--cc,#0b3119);color:#fff;border-color:transparent}.kc-chips button.on i{background:#fff}.kc-chips button.on em{background:rgba(255,255,255,.2)}
.kc-act{margin-inline-start:auto;display:flex;gap:6px}
.kc-act a,.kc-act button{border:0;background:#fff;border-radius:12px;padding:9px 12px;font-weight:800;font-size:13px;color:var(--ink-3)!important;box-shadow:var(--sh-1);cursor:pointer;font-family:inherit;text-decoration:none!important;display:inline-flex;gap:6px;align-items:center}
.kc-pane{display:none}.kc-pane.on{display:block;animation:kcIn .35s ease}
@keyframes kcIn{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:none}}
/* الخط الزمني */
.kc-tl{background:#fff;border-radius:26px;box-shadow:var(--sh-3d);padding:22px 24px 26px;overflow:hidden}
.kc-tl-m{position:relative;height:28px;margin:0 0 6px;border-bottom:1px dashed #d5e3da}
.kc-tl-m span{position:absolute;top:4px;transform:translateX(50%);font-size:12px;font-weight:800;color:var(--muted);white-space:nowrap}
.kc-tl-m span:before{content:"";position:absolute;right:50%;top:20px;width:1px;height:8px;background:#cfdcd4}
.kc-lane{display:grid;grid-template-columns:150px minmax(0,1fr);gap:12px;align-items:center;margin-top:14px}
.kc-lane>b{font-size:14px;font-weight:900;color:var(--ink);line-height:1.4}
.kc-track{position:relative;height:44px;border-radius:14px;background:repeating-linear-gradient(90deg,#f4f8f5 0 calc(100%/12),#fbfdfb calc(100%/12) calc(100%/6))}
.kc-seg{position:absolute;top:6px;height:32px;border-radius:10px;background:linear-gradient(135deg,var(--cc),color-mix(in srgb,var(--cc) 70%,#000));min-width:12px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:12px;cursor:pointer;box-shadow:0 6px 12px -8px var(--cc);transition:transform .2s,box-shadow .2s;overflow:hidden}
.kc-seg span{white-space:nowrap;overflow:hidden;text-overflow:ellipsis;padding:0 8px;font-weight:800}
.kc-seg.past{opacity:.45;filter:saturate(.6)}
.kc-seg:hover,.kc-seg.hl{transform:translateY(-3px) scale(1.04);z-index:3;box-shadow:0 14px 22px -10px var(--cc)}
.kc-seg.pt{width:14px;height:14px;top:15px;border-radius:50%;min-width:0;border:3px solid #fff;box-shadow:0 0 0 2px var(--cc)}
.kc-todayl{position:absolute;top:-6px;bottom:-6px;width:2px;background:#c0392b;z-index:4;pointer-events:none}
.kc-todayl.nl:before{display:none}.kc-todayl:before{content:"اليوم";position:absolute;top:-22px;right:50%;transform:translateX(50%);background:#c0392b;color:#fff;font-size:11px;font-weight:900;border-radius:8px;padding:1px 8px;white-space:nowrap}
.kc-tip{position:fixed;z-index:200;pointer-events:none;background:#0b3119;color:#fff;border-radius:14px;padding:10px 14px;font-size:13.5px;box-shadow:0 20px 40px -16px rgba(0,0,0,.5);max-width:280px;opacity:0;transition:opacity .15s}
.kc-tip.on{opacity:1}.kc-tip b{display:block;font-size:14.5px}.kc-tip small{color:#bfe3cc}
/* الشهر */
.kc-month{background:#fff;border-radius:26px;box-shadow:var(--sh-3d);overflow:hidden}
.kc-mh{display:flex;align-items:center;gap:10px;padding:16px 20px;background:linear-gradient(90deg,var(--ink-2),var(--ink-3));color:#fff;position:relative;isolation:isolate}
.kc-mh:before{content:"";position:absolute;inset:0;z-index:-1;background:url(/assets/images/pattern-islamic.svg) 0 0/60px;opacity:.12}
.kc-mh b{font-size:20px;font-weight:900;flex:1}.kc-mh small{color:#e8c46a;font-weight:800}
.kc-mh button{border:0;width:38px;height:38px;border-radius:12px;background:rgba(255,255,255,.12);color:#fff;cursor:pointer}
.kc-mh button.td{width:auto;padding:0 12px;font-family:inherit;font-weight:800}
.kc-grid{display:grid;grid-template-columns:repeat(7,minmax(0,1fr))}
.kc-grid .wd{padding:10px 6px;text-align:center;font-size:12.5px;font-weight:900;color:var(--muted);background:#f6faf7;border-bottom:1px solid #e6eee8}
.kc-day{min-height:104px;border-inline-start:1px solid #eef3ef;border-bottom:1px solid #eef3ef;padding:6px;display:flex;flex-direction:column;gap:3px;position:relative}
.kc-day.out{background:#fafcfb}.kc-day.out .n{opacity:.35}
.kc-day .n{display:flex;justify-content:space-between;align-items:center;font-weight:900;color:var(--ink);font-size:14px}
.kc-day .n small{font-size:10.5px;color:#b98f25;font-weight:700}
.kc-day.today{background:linear-gradient(180deg,#fff8e6,#fff)}
.kc-day.today .n b{background:#c0392b;color:#fff;border-radius:50%;width:26px;height:26px;display:grid;place-items:center}
.kc-day .e{display:block;font-size:11.5px;font-weight:800;color:#fff;background:var(--cc);border-radius:7px;padding:2px 6px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;cursor:pointer}
.kc-day .e.mid{border-radius:0;margin-inline:-6px;padding-inline:12px;opacity:.85}
.kc-day .more{font-size:11px;color:var(--muted);font-weight:800}
/* القائمة */
.kc-term{background:#fff;border-radius:26px;box-shadow:var(--sh-3d);margin-bottom:20px;overflow:hidden}
.kc-term-h{display:flex;align-items:center;gap:14px;padding:18px 22px;background:linear-gradient(90deg,var(--ink-2),var(--ink-3));color:#fff;position:relative;isolation:isolate}
.kc-term-h:before{content:"";position:absolute;inset:0;z-index:-1;background:url(/assets/images/pattern-islamic.svg) 0 0/60px;opacity:.12}
.kc-term-h b{font-size:19px;font-weight:900;flex:1}
.kc-term-h .pg{width:180px;height:10px;border-radius:10px;background:rgba(255,255,255,.15);overflow:hidden}.kc-term-h .pg i{display:block;height:100%;background:linear-gradient(90deg,#e8c46a,#7fe0a6)}
.kc-term-h em{font-style:normal;font-size:12.5px;font-weight:800;color:#e8c46a;white-space:nowrap}
.kc-list{position:relative;padding:10px 22px 14px}
.kc-list:before{content:"";position:absolute;top:24px;bottom:24px;right:52px;width:2px;background:linear-gradient(#e1ede4,#e1ede4) ;border-radius:2px}
.kc-it{display:grid;grid-template-columns:62px minmax(0,1fr) auto;gap:14px;align-items:center;padding:10px 0;position:relative}
.kc-it .dt{width:62px;height:64px;border-radius:16px;background:#fff;border:2px solid var(--cc);text-align:center;position:relative;z-index:1;box-shadow:0 8px 16px -12px var(--cc)}
.kc-it .dt b{display:block;font-size:24px;font-weight:900;color:var(--cc);line-height:1.3;margin-top:4px}.kc-it .dt small{font-size:11px;font-weight:800;color:var(--muted)}
.kc-it .bx{background:#f8fbf9;border-radius:16px;padding:12px 16px;border-inline-start:4px solid var(--cc);transition:.2s}
.kc-it:hover .bx{background:#fff;box-shadow:var(--sh-1);transform:translateX(-4px)}
.kc-it .bx b{display:flex;gap:8px;align-items:center;color:var(--ink);font-size:16px}.kc-it .bx b i{color:var(--cc)}
.kc-it .bx small{display:block;color:var(--muted);font-size:13px;margin-top:2px}
.kc-st{display:flex;flex-direction:column;align-items:flex-end;gap:6px}
.kc-st span{font-size:12px;font-weight:900;border-radius:20px;padding:3px 10px;white-space:nowrap}
.kc-st .past{background:#eef1ef;color:#7a8c80}.kc-st .now{background:#fff1d6;color:#9a6a00}.kc-st .soon{background:#e4f4ea;color:#1f7a45}
.kc-st button{border:0;background:var(--mist);color:var(--teal);width:34px;height:34px;border-radius:10px;cursor:pointer}
.kc-st button:hover{background:var(--teal);color:#fff}
.kc-it.past{opacity:.55}.kc-it.now .bx{background:linear-gradient(90deg,#fff8e6,#fff);box-shadow:0 0 0 2px #e8c46a}
.kc-up{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin-bottom:18px}
.kc-upc{background:#fff;border-radius:20px;padding:16px;box-shadow:var(--sh-1);display:flex;gap:12px;align-items:center;border-bottom:4px solid var(--cc)}
.kc-upc i{width:44px;height:44px;border-radius:14px;display:grid;place-items:center;color:#fff;background:var(--cc);flex:none}
.kc-upc b{display:block;color:var(--ink);font-size:14.5px;line-height:1.5}.kc-upc small{color:var(--muted);font-size:12.5px}
.kc-upc em{margin-inline-start:auto;font-style:normal;text-align:center;font-weight:900;color:var(--cc);font-size:20px;line-height:1}.kc-upc em small{display:block;font-size:11px;color:var(--muted)}
@media(max-width:1000px){.kc-hero{grid-template-columns:minmax(0,1fr)}.kc-up{grid-template-columns:minmax(0,1fr)}.kc-lane{grid-template-columns:minmax(0,1fr)}.kc-lane>b{font-size:13px}}
@media(max-width:640px){.kc-now{grid-template-columns:1fr;justify-items:center;text-align:center;padding:22px 18px}.kc-nowl{justify-content:center}.kc-ring{width:140px;height:140px}.kc-ring div{width:110px;height:110px}.kc-day{min-height:62px;padding:3px}.kc-day .e{font-size:0;height:6px;padding:0}.kc-day .n small{display:none}.kc-it{grid-template-columns:52px minmax(0,1fr)}.kc-it .dt{width:52px;height:56px}.kc-st{grid-column:2;flex-direction:row;align-items:center}.kc-list:before{right:47px}.kc-act{margin:0}.kc-term-h .pg{display:none}}
@media print{.u-header,.u-footer,.u-page-hero,.kc-bar,.kc-hero,.kc-up,.kc-st button,#uwApp{display:none!important}.kc-pane{display:block!important}.kc-pane[data-v=timeline],.kc-pane[data-v=month]{display:none!important}.kc-term{box-shadow:none;border:1px solid #ddd;break-inside:avoid}}
</style>

<div class="kc">
<?php if (empty($__official)): ?><div class="u-note-box" style="margin-bottom:16px"><i class="fas fa-info-circle"></i> المواعيد معتمدة مبدئيًا، وأي تعديل يُعلن في <a href="/news.php">أخبار الجامعة</a> وشريط الأخبار.</div><?php endif; ?>

<section class="kc-hero">
  <div class="kc-now">
    <div class="kc-ring" style="--p:<?= $yearPct ?>"><div><span><b><?= $yearPct ?>%</b>مضى من العام</span></div></div>
    <div>
      <small><?= $now ? '<i class="live"></i> يجري الآن' : '<i class="far fa-clock"></i> العام الجامعي ' . $h($YEAR) ?></small>
      <h2><?= $now ? $h($now[0][1]) : ($today < $first ? 'يبدأ العام الجامعي بعد ' . $days($today, $first) . ' يومًا' : 'لا توجد فعالية جارية اليوم') ?></h2>
      <p><?= $now ? ($now[0][3] ? 'حتى ' . $ar($now[0][3]) . ' — بقي ' . max(0, $days($today, $now[0][3])) . ' يومًا' : 'اليوم') : 'تابع الموعد القادم والعدّاد بجانبه.' ?></p>
      <div class="kc-nowl"><span><i class="fas fa-flag" style="--c:#7fe0a6"></i>البداية <?= $ar($first, false) ?></span><span><i class="fas fa-flag-checkered"></i>النهاية <?= $ar($last, false) ?></span><span><i class="fas fa-calendar-check"></i><?= count($EV) ?> موعدًا</span></div>
    </div>
  </div>
  <div class="kc-side">
    <div class="kc-next"><small><i class="fas fa-hourglass-half"></i> الموعد القادم</small><b class="t"><?= $next ? $h($next[1]) . ' — ' . $ar($next[2], false) : 'لا مواعيد قادمة' ?></b>
      <?php if ($next): ?><div class="kc-cd" data-target="<?= $next[2] ?>T00:00:00"><span><b data-u="d">0</b>يوم</span><span><b data-u="h">0</b>ساعة</span><span><b data-u="m">0</b>دقيقة</span><span><b data-u="s">0</b>ثانية</span></div><?php endif; ?></div>
    <div class="kc-date"><div class="d"><small id="kcM"></small><b><?= (int) date('j') ?></b></div><div><b id="kcG"><?= $ar($today) ?></b><span id="kcH"></span></div></div>
  </div>
</section>

<?php if ($upcoming): ?><div class="kc-up"><?php foreach (array_slice($upcoming, 0, 3) as $e): $ty = $TYPES[$e['ty']]; $dl = $days($today, $e['s']); ?>
  <div class="kc-upc" style="--cc:<?= $ty[0] ?>"><i class="fas <?= $ty[1] ?>"></i><span><b><?= $h($e['t']) ?></b><small><?= $ar($e['s']) ?></small></span><em><?= $dl > 0 ? $dl : '●' ?><small><?= $dl > 0 ? 'يومًا' : 'جارٍ' ?></small></em></div>
<?php endforeach; ?></div><?php endif; ?>

<div class="kc-bar">
  <div class="kc-views" role="tablist"><button type="button" class="on" data-v="list"><i class="fas fa-stream"></i> القائمة</button><button type="button" data-v="timeline"><i class="fas fa-chart-bar"></i> الخط الزمني</button><button type="button" data-v="month"><i class="far fa-calendar-alt"></i> الشهر</button></div>
  <div class="kc-chips"><button type="button" class="on" data-ty="" style="--cc:#0b3119"><i></i>الكل <em><?= count($EV) ?></em></button><?php foreach ($TYPES as $tk => $ty): if (!$cnt[$tk]) continue; ?><button type="button" data-ty="<?= $tk ?>" style="--cc:<?= $ty[0] ?>"><i></i><?= $ty[2] ?> <em><?= $cnt[$tk] ?></em></button><?php endforeach; ?></div>
  <div class="kc-act"><button type="button" id="kcAll" title="أضف كل المواعيد إلى تقويم جوالك"><i class="far fa-calendar-plus"></i> اشترك</button><a href="https://wa.me/?text=<?= rawurlencode('التقويم الجامعي لجامعة بنيان العالمية ' . $YEAR . ': https://your-domain.com/calendar') ?>" target="_blank" rel="noopener"><i class="fab fa-whatsapp"></i></a><button type="button" onclick="window.print()" title="طباعة"><i class="fas fa-print"></i></button></div>
</div>

<!-- القائمة -->
<div class="kc-pane on" data-v="list">
<?php foreach ($TERMS as $ti => $tm): $te = array_values(array_filter($EV, function ($e) use ($ti) { return $e['term'] === $ti; })); if (!$te) continue;
  $f = min(array_column($te, 's')); $l = max(array_column($te, 'e')); $pct = $today <= $f ? 0 : ($today >= $l ? 100 : round((strtotime($today) - strtotime($f)) / max(1, strtotime($l) - strtotime($f)) * 100)); ?>
  <section class="kc-term">
    <div class="kc-term-h"><b><?= $h($tm[0]) ?></b><div class="pg" title="ما مضى من الفصل"><i style="width:<?= $pct ?>%"></i></div><em><?= $pct >= 100 ? 'انتهى' : ($pct > 0 ? $pct . '% مضى' : 'يبدأ ' . $ar($f, false)) ?></em></div>
    <div class="kc-list">
    <?php foreach ($te as $e): $ty = $TYPES[$e['ty']]; $st = $e['e'] < $today ? 'past' : ($e['s'] <= $today ? 'now' : 'soon'); $dl = $days($today, $e['s']); ?>
      <div class="kc-it <?= $st ?> kf" style="--cc:<?= $ty[0] ?>" data-ty="<?= $e['ty'] ?>" data-t="<?= $h($e['t']) ?>" data-s="<?= $e['s'] ?>" data-e="<?= $e['e'] ?>">
        <div class="dt"><b><?= (int) date('j', strtotime($e['s'])) ?></b><small><?= explode(' ', $ar($e['s'], false))[1] ?></small></div>
        <div class="bx"><b><i class="fas <?= $ty[1] ?>"></i><?= $h($e['t']) ?></b><small><?= $ar($e['s']) ?><?= $e['e'] !== $e['s'] ? ' ← ' . $ar($e['e']) . ' · ' . ($days($e['s'], $e['e']) + 1) . ' يومًا' : '' ?><?= $e['n'] ? ' · ' . $h($e['n']) : '' ?></small></div>
        <div class="kc-st"><span class="<?= $st ?>"><?= $st === 'past' ? 'انتهى' : ($st === 'now' ? 'يجري الآن' : 'بعد ' . $dl . ' يومًا') ?></span><?php if ($st !== 'past'): ?><button type="button" class="kc-ics" title="أضف إلى تقويمي"><i class="far fa-calendar-plus"></i></button><?php endif; ?></div>
      </div>
    <?php endforeach; ?>
    </div>
  </section>
<?php endforeach; ?>
</div>

<!-- الخط الزمني -->
<div class="kc-pane" data-v="timeline">
  <div class="kc-tl">
    <div class="kc-lane" style="margin-top:0"><b></b><div class="kc-tl-m"><?php foreach ($MONTHS as $m): $p = $pos($m . '-01'); if ($p <= 0 && $m . '-01' < $first) continue; ?><span style="right:<?= round($p, 2) ?>%"><?= explode(' ', $ar($m . '-01', false))[1] ?></span><?php endforeach; ?></div></div>
    <?php foreach ($TERMS as $ti => $tm): ?>
      <div class="kc-lane"><b><?= $h($tm[0]) ?></b><div class="kc-track">
        <?php if ($today >= $first && $today <= $last): ?><i class="kc-todayl<?= $ti ? ' nl' : '' ?>" style="right:<?= round($pos($today), 2) ?>%"></i><?php endif; ?>
        <?php foreach ($EV as $e): if ($e['term'] !== $ti) continue; $ty = $TYPES[$e['ty']]; $r = $pos($e['s']); $w = $pos($e['e']) - $r; $pt = $e['s'] === $e['e']; ?>
          <span class="kc-seg kf<?= $pt ? ' pt' : '' ?><?= $e['e'] < $today ? ' past' : '' ?>" style="--cc:<?= $ty[0] ?>;right:<?= round($r, 2) ?>%;<?= $pt ? '' : 'width:' . max(1.2, round($w, 2)) . '%' ?>" data-ty="<?= $e['ty'] ?>" data-tip="<?= $h($e['t']) ?>|<?= $ar($e['s']) . ($pt ? '' : ' ← ' . $ar($e['e'])) ?>|<?= $ty[2] ?>"><?= (!$pt && $w > 7) ? '<span>' . $h($e['t']) . '</span>' : '' ?></span>
        <?php endforeach; ?>
      </div></div>
    <?php endforeach; ?>
  </div>
</div>

<!-- الشهر -->
<div class="kc-pane" data-v="month">
  <div class="kc-month"><div class="kc-mh"><button type="button" id="kcPrev" aria-label="الشهر السابق"><i class="fas fa-chevron-right"></i></button><b id="kcMT"></b><small id="kcMH"></small><button type="button" class="td" id="kcToday">اليوم</button><button type="button" id="kcNext" aria-label="الشهر التالي"><i class="fas fa-chevron-left"></i></button></div>
    <div class="kc-grid" id="kcGrid"></div></div>
</div>
<div class="kc-tip" id="kcTip"></div>
</div>
<script>
(function(){
var EV=<?= json_encode(array_map(function ($e) use ($TYPES) { return ['t' => $e['t'], 's' => $e['s'], 'e' => $e['e'], 'ty' => $e['ty'], 'c' => $TYPES[$e['ty']][0], 'l' => $TYPES[$e['ty']][2]]; }, $EV), JSON_UNESCAPED_UNICODE) ?>,TODAY='<?= $today ?>',MN=['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'],WD=['الأحد','الاثنين','الثلاثاء','الأربعاء','الخميس','الجمعة','السبت'];
var $=function(s){return document.querySelector(s)},$$=function(s){return [].slice.call(document.querySelectorAll(s))},cur='';
try{$('#kcM').textContent=MN[new Date().getMonth()];$('#kcH').textContent=new Intl.DateTimeFormat('ar-SA-u-ca-islamic-umalqura',{weekday:'long',day:'numeric',month:'long',year:'numeric'}).format(new Date());}catch(e){}
/* العدّاد */
var cd=$('.kc-cd');if(cd){var T=new Date(cd.dataset.target).getTime();var tick=function(){var s=Math.max(0,(T-Date.now())/1000);cd.querySelector('[data-u=d]').textContent=Math.floor(s/86400);cd.querySelector('[data-u=h]').textContent=Math.floor(s%86400/3600);cd.querySelector('[data-u=m]').textContent=Math.floor(s%3600/60);cd.querySelector('[data-u=s]').textContent=Math.floor(s%60);};tick();setInterval(tick,1000);}
/* العروض */
var views=$$('.kc-views button');function view(v){views.forEach(function(b){b.classList.toggle('on',b.dataset.v===v)});$$('.kc-pane').forEach(function(p){p.classList.toggle('on',p.dataset.v===v)});if(v==='month')month();try{localStorage.setItem('kcView',v)}catch(e){}}
views.forEach(function(b){b.onclick=function(){view(b.dataset.v)}});
/* التصفية */
var chips=$$('.kc-chips button');function filt(ty){cur=ty;chips.forEach(function(c){c.classList.toggle('on',c.dataset.ty===ty)});$$('.kf').forEach(function(x){x.style.display=!ty||x.dataset.ty===ty?'':'none'});if($('.kc-pane[data-v=month]').classList.contains('on'))month();}
chips.forEach(function(c){c.onclick=function(){filt(c.dataset.ty)}});
/* تلميح الخط الزمني */
var tip=$('#kcTip');$$('.kc-seg').forEach(function(s){s.addEventListener('mousemove',function(e){var d=s.dataset.tip.split('|');tip.innerHTML='<b></b><small></small>';tip.querySelector('b').textContent=d[0];tip.querySelector('small').textContent=d[2]+' · '+d[1];tip.style.left=Math.min(innerWidth-300,e.clientX+14)+'px';tip.style.top=(e.clientY+16)+'px';tip.classList.add('on');});s.addEventListener('mouseleave',function(){tip.classList.remove('on')});});
/* الشهر */
var md=new Date(TODAY+'T00:00:00');if(TODAY<EV.reduce(function(a,e){return a<e.s?a:e.s},'9999'))md=new Date(EV[0].s+'T00:00:00');md.setDate(1);
function iso(d){return d.getFullYear()+'-'+('0'+(d.getMonth()+1)).slice(-2)+'-'+('0'+d.getDate()).slice(-2)}
function month(){var g=$('#kcGrid'),y=md.getFullYear(),m=md.getMonth();$('#kcMT').textContent=MN[m]+' '+y;
  try{$('#kcMH').textContent=new Intl.DateTimeFormat('ar-SA-u-ca-islamic-umalqura',{month:'long',year:'numeric'}).format(new Date(y,m,15));}catch(e){}
  var h=WD.map(function(w){return '<div class="wd">'+w+'</div>'}).join(''),start=new Date(y,m,1);start.setDate(1-start.getDay());
  for(var i=0;i<42;i++){var d=new Date(start);d.setDate(start.getDate()+i);var s=iso(d),out=d.getMonth()!==m;if(i>=35&&out&&d.getDate()>7)break;
    var hj='';try{hj=new Intl.DateTimeFormat('ar-SA-u-ca-islamic-umalqura',{day:'numeric'}).format(d)}catch(e){}
    var es=EV.filter(function(e){return e.s<=s&&s<=e.e&&(!cur||e.ty===cur)}),ch=es.slice(0,3).map(function(e){return '<span class="e'+(e.s!==s&&d.getDay()!==0?' mid':'')+'" style="--cc:'+e.c+'" title="'+e.t.replace(/"/g,'&quot;')+'">'+(e.s===s||d.getDay()===0?e.t.replace(/</g,''):'&nbsp;')+'</span>'}).join('')+(es.length>3?'<span class="more">+'+(es.length-3)+'</span>':'');
    h+='<div class="kc-day'+(out?' out':'')+(s===TODAY?' today':'')+'"><div class="n"><b>'+d.getDate()+'</b><small>'+hj+'</small></div>'+ch+'</div>';}
  g.innerHTML=h;}
$('#kcPrev').onclick=function(){md.setMonth(md.getMonth()-1);month()};$('#kcNext').onclick=function(){md.setMonth(md.getMonth()+1);month()};$('#kcToday').onclick=function(){md=new Date(TODAY+'T00:00:00');md.setDate(1);month()};
/* ملف ics */
function ics(list){var L=['BEGIN:VCALENDAR','VERSION:2.0','PRODID:-//uwciv//calendar//AR','X-WR-CALNAME:التقويم الجامعي — جامعة بنيان العالمية'];list.forEach(function(e){var s=e.s.replace(/-/g,''),en=new Date(e.e+'T00:00:00Z');en.setUTCDate(en.getUTCDate()+1);L.push('BEGIN:VEVENT','UID:'+s+'-'+Math.random().toString(36).slice(2)+'@your-domain.com','DTSTAMP:'+new Date().toISOString().replace(/[-:]/g,'').slice(0,15)+'Z','DTSTART;VALUE=DATE:'+s,'DTEND;VALUE=DATE:'+en.toISOString().slice(0,10).replace(/-/g,''),'SUMMARY:'+e.t+' — جامعة بنيان العالمية','URL:https://your-domain.com/calendar','END:VEVENT');});L.push('END:VCALENDAR');
  var a=document.createElement('a');a.href=URL.createObjectURL(new Blob([L.join('\r\n')],{type:'text/calendar'}));a.download='uwciv-calendar.ics';document.body.appendChild(a);a.click();a.remove();}
$$('.kc-ics').forEach(function(b){b.onclick=function(){var r=b.closest('.kc-it');ics([{t:r.dataset.t,s:r.dataset.s,e:r.dataset.e}])}});
$('#kcAll').onclick=function(){ics(EV.filter(function(e){return e.e>=TODAY}))};
/* البداية */
var hv=location.hash.slice(1);if(hv&&$('.kc-chips button[data-ty="'+hv+'"]')){filt(hv);}
var sv='list';try{sv=localStorage.getItem('kcView')||'list'}catch(e){}if(['list','timeline','month'].indexOf(sv)<0)sv='list';view(sv);
var nowEl=$('.kc-it.now')||$('.kc-it.soon');if(nowEl&&sv==='list'&&!hv)setTimeout(function(){nowEl.classList.add('hl')},300);
})();
</script>
<?php uwciv_close(); ?>
