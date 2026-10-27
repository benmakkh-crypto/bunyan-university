<?php
require_once __DIR__ . '/inc/shell.php';
uwciv_open('تطبيق الجامعة', null, 'ثبّت تطبيق جامعة بنيان العالمية على جوالك مجانًا — بدون متجر وبضغطة واحدة.');
?>
<style>
.ap{display:grid;grid-template-columns:1.1fr .9fr;gap:30px;align-items:center}
.ap-txt h2{font-size:clamp(26px,3.4vw,40px);font-weight:900;color:var(--ink);margin:0 0 10px;line-height:1.35}
.ap-txt>p{color:var(--muted);font-size:17px;line-height:1.9;margin:0 0 22px}
.ap-btn{display:inline-flex;align-items:center;gap:12px;border:0;border-radius:18px;padding:16px 30px;font-size:19px;font-weight:900;color:#fff;background:linear-gradient(135deg,var(--teal),var(--ink-3));box-shadow:0 18px 30px -16px rgba(6,33,15,.8);cursor:pointer;font-family:inherit;transition:.25s;text-decoration:none!important}
.ap-btn:hover{transform:translateY(-3px)}
.ap-btn i{font-size:24px}
.ap-btn small{display:block;font-size:12.5px;font-weight:600;opacity:.85}
.ap-ok{display:none;align-items:center;gap:10px;background:var(--cyan-soft);color:var(--teal);font-weight:800;border-radius:14px;padding:12px 16px;margin-top:14px}
.ap-feat{display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-top:26px}
.ap-feat div{display:flex;gap:12px;align-items:center;background:#fff;border-radius:16px;padding:14px;box-shadow:var(--sh-1);font-weight:700;color:var(--ink);font-size:14.5px}
.ap-feat i{width:40px;height:40px;border-radius:12px;display:grid;place-items:center;background:var(--cyan-soft);color:var(--teal);flex:none}
/* الجوال */
.ap-phone{position:relative;width:290px;height:590px;margin:0 auto;border-radius:46px;background:#0c1410;padding:12px;box-shadow:0 50px 80px -30px rgba(6,33,15,.7),inset 0 0 0 2px #2b3a31;transform:perspective(1200px) rotateY(-12deg) rotateX(4deg);transition:transform .6s cubic-bezier(.2,.8,.2,1)}
.ap-phone:hover{transform:perspective(1200px) rotateY(0) rotateX(0)}
.ap-scr{width:100%;height:100%;border-radius:36px;overflow:hidden;background:linear-gradient(170deg,#11432a,#06210f 60%);position:relative;display:flex;flex-direction:column}
.ap-notch{position:absolute;top:10px;left:50%;transform:translateX(-50%);width:90px;height:24px;border-radius:14px;background:#0c1410;z-index:2}
.ap-home{padding:54px 18px 16px;text-align:center;color:#fff}
.ap-home img.lg{width:150px;filter:drop-shadow(0 6px 12px rgba(0,0,0,.5))}
.ap-home small{display:block;color:#a9d6b9;font-size:10px;letter-spacing:.2em;margin-top:6px}
.ap-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;padding:8px 16px}
.ap-grid span{display:flex;flex-direction:column;align-items:center;gap:6px;color:#e6f4ec;font-size:10.5px;font-weight:700}
.ap-grid i{width:48px;height:48px;border-radius:15px;display:grid;place-items:center;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.14);font-size:18px;color:#7fe0a6}
.ap-bar{margin:auto 16px 16px;background:rgba(255,255,255,.08);border-radius:18px;padding:10px;display:flex;justify-content:space-around;color:#a9d6b9;font-size:16px}
.ap-bar i:first-child{color:#7fe0a6}
/* الخطوات */
.ap-how{margin-top:40px;display:grid;grid-template-columns:1fr 1fr auto;gap:18px}
.ap-os{background:#fff;border-radius:22px;padding:24px;box-shadow:var(--sh-1)}
.ap-os h3{display:flex;gap:10px;align-items:center;font-size:19px;font-weight:900;color:var(--ink);margin:0 0 14px}
.ap-os h3 i{font-size:26px}
.ap-os ol{margin:0;padding:0;list-style:none;counter-reset:s}
.ap-os li{counter-increment:s;display:flex;gap:12px;align-items:flex-start;padding:8px 0;font-size:15px;line-height:1.7;color:var(--text)}
.ap-os li:before{content:counter(s);flex:none;width:28px;height:28px;border-radius:9px;background:var(--ink-3);color:#fff;display:grid;place-items:center;font-weight:900;font-size:13px}
.ap-os b{color:var(--teal)}
.ap-qr{background:#fff;border-radius:22px;padding:22px;box-shadow:var(--sh-1);text-align:center;width:250px}
.ap-qr #qr{width:190px;height:190px;margin:0 auto 10px;display:grid;place-items:center}
.ap-qr #qr img,.ap-qr #qr canvas{width:190px!important;height:190px!important}
.ap-qr b{display:block;color:var(--ink)}.ap-qr small{color:var(--muted)}
.ap-note{margin-top:22px;color:var(--muted);font-size:14px;text-align:center}
@media(max-width:992px){.ap{grid-template-columns:1fr}.ap-phone{transform:none;width:260px;height:530px}.ap-how{grid-template-columns:1fr}.ap-qr{width:auto}}
@media(max-width:560px){.ap-feat{grid-template-columns:1fr}.ap-btn{width:100%;justify-content:center}}
</style>

<div class="ap">
  <div class="ap-txt">
    <span class="u-kicker">تطبيق الجوال</span>
    <h2>جامعتك في جيبك<br>أينما كنت</h2>
    <p>يفتح التطبيق مباشرة من شاشة جوالك بأيقونة الجامعة: التسجيل، البوابة الإلكترونية، المحاضرات، الامتحانات، الكليات والتقويم الدراسي — بسرعة وبدون متصفح.</p>
    <button type="button" class="ap-btn" id="apInstall"><i class="fas fa-download"></i><span>ثبّت التطبيق الآن<small>مجاني · حجم صغير جدًا · بدون متجر</small></span></button>
    <div class="ap-ok" id="apOk"><i class="fas fa-check-circle"></i> التطبيق مثبّت على جهازك — افتحه من الشاشة الرئيسية.</div>
    <div class="ap-feat">
      <div><i class="fas fa-bolt"></i>فتح سريع بضغطة واحدة</div>
      <div><i class="fas fa-user-graduate"></i>البوابة والمحاضرات والامتحانات</div>
      <div><i class="fas fa-wifi"></i>يعمل مع ضعف الإنترنت</div>
      <div><i class="fas fa-sync-alt"></i>يتحدّث تلقائيًا دائمًا</div>
    </div>
  </div>
  <div>
    <div class="ap-phone" aria-hidden="true"><div class="ap-scr"><span class="ap-notch"></span>
      <div class="ap-home"><img class="lg" src="/assets/images/na-light.png" alt=""><small>BUNYAN INTERNATIONAL UNIVERSITY</small></div>
      <div class="ap-grid">
        <span><i class="fas fa-user-plus"></i>سجّل معنا</span><span><i class="fas fa-layer-group"></i>البوابة</span><span><i class="fas fa-university"></i>الكليات</span>
        <span><i class="fas fa-play-circle"></i>المحاضرات</span><span><i class="fas fa-file-signature"></i>الامتحانات</span><span><i class="fas fa-certificate"></i>الشهادات</span>
        <span><i class="fas fa-book"></i>المكتبة</span><span><i class="far fa-calendar-alt"></i>التقويم</span><span><i class="fas fa-headset"></i>الدعم</span>
      </div>
      <div class="ap-bar"><i class="fas fa-home"></i><i class="fas fa-search"></i><i class="far fa-bell"></i><i class="far fa-user"></i></div>
    </div></div>
  </div>
</div>

<div class="ap-how" id="how">
  <div class="ap-os"><h3><i class="fab fa-android" style="color:#3ddc84"></i> أندرويد (Chrome)</h3>
    <ol><li>افتح <b>your-domain.com/app</b> من متصفح Chrome.</li><li>اضغط زر <b>«ثبّت التطبيق الآن»</b>.</li><li>اختر <b>«تثبيت»</b> — تظهر أيقونة الجامعة على شاشتك.</li></ol></div>
  <div class="ap-os"><h3><i class="fab fa-apple" style="color:#111"></i> آيفون (Safari)</h3>
    <ol><li>افتح <b>your-domain.com/app</b> من متصفح Safari.</li><li>اضغط زر المشاركة <b><i class="fas fa-share-square"></i></b> في الأسفل.</li><li>اختر <b>«إضافة إلى الشاشة الرئيسية»</b> ثم <b>«إضافة»</b>.</li></ol></div>
  <div class="ap-qr"><div id="qr"></div><b>امسح الرمز بالجوال</b><small>من الكمبيوتر إلى جوالك مباشرة</small></div>
</div>
<p class="ap-note"><i class="fas fa-shield-alt"></i> التطبيق رسمي من موقع الجامعة your-domain.com، ولا يطلب أي صلاحيات على جهازك.</p>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
(function(){
  try{ new QRCode(document.getElementById('qr'),{text:'https://your-domain.com/app',width:380,height:380,colorDark:'#0b3119',colorLight:'#ffffff',correctLevel:QRCode.CorrectLevel.H}); }catch(e){ document.getElementById('qr').innerHTML='<img src="/assets/app/icon-192.png" alt="">'; }
  var btn=document.getElementById('apInstall'), ok=document.getElementById('apOk');
  var standalone=window.matchMedia('(display-mode: standalone)').matches||navigator.standalone;
  if(standalone){btn.style.display='none';ok.style.display='flex';}
  btn.addEventListener('click',function(){
    if(window.uwcivInstall){window.uwcivInstall();return;}
    var ios=/iphone|ipad|ipod/i.test(navigator.userAgent);
    document.getElementById('how').scrollIntoView({behavior:'smooth',block:'start'});
    var t=document.createElement('div');t.style.cssText='position:fixed;bottom:22px;left:50%;transform:translateX(-50%);background:#0b3119;color:#fff;padding:12px 20px;border-radius:30px;z-index:9999;font-weight:700;max-width:90%;text-align:center';
    t.textContent=ios?'على آيفون: اضغط زر المشاركة ثم «إضافة إلى الشاشة الرئيسية»':'اتبع الخطوات أدناه، أو افتح الصفحة من جوالك عبر رمز QR';document.body.appendChild(t);setTimeout(function(){t.remove()},4500);
  });
  window.addEventListener('appinstalled',function(){btn.style.display='none';ok.style.display='flex';});
})();
</script>
<?php uwciv_close(); ?>
