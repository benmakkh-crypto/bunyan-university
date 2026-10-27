<?php
/* محرر القائمة الرئيسية للموقع — العناوين الرئيسية والفرعية وفرعية الفرعية */
require_once dirname(__DIR__) . '/inc/admin_ui.php';
uwciv_admin_gate(['title' => 'القائمة الرئيسية للموقع', 'perm' => 'menu']);
require_once dirname(__DIR__) . '/inc/i18n.php';
require_once dirname(__DIR__) . '/inc/mega.php';
$msg = ''; $err = '';
$str = function ($v, $n) { return trim(mb_substr(preg_replace('/\s+/u', ' ', (string) $v), 0, $n)); };

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!uwciv_csrf_ok()) $err = 'انتهت صلاحية الصفحة، أعد المحاولة.';
    elseif (($_POST['act'] ?? '') === 'reset') {
        @rename(uwciv_menu_file(), uwciv_menu_file() . '.bak-' . date('YmdHis'));
        uwciv_staff_log('أعاد القائمة الرئيسية إلى الافتراضي'); header('Location: menu.php?ok=' . rawurlencode('أُعيدت القائمة إلى وضعها الافتراضي (حُفظت نسخة احتياطية).')); exit;
    } else {
        $in = json_decode((string) ($_POST['tree'] ?? ''), true); $out = [];
        if (!is_array($in)) $err = 'تعذّر قراءة البيانات.';
        else {
            foreach (array_slice($in, 0, 20) as $i => $m) {
                $label = $str($m['label'] ?? '', 40); if ($label === '') continue;
                $it = ['key' => preg_replace('/[^a-z0-9_]/', '', (string) ($m['key'] ?? '')) ?: 'm' . $i . substr(md5($label), 0, 5), 'label' => $label, 'url' => uwciv_url_ok($m['url'] ?? '#'), 'desc' => $str($m['desc'] ?? '', 160), 'hidden' => !empty($m['hidden']), 'sections' => [], 'cta' => null];
                foreach (array_slice((array) ($m['sections'] ?? []), 0, 12) as $sec) {
                    $S = ['t' => $str($sec['t'] ?? '', 60), 'i' => uwciv_icon_fix($sec['i'] ?? ''), 'd' => $str($sec['d'] ?? '', 90), 'items' => []];
                    if ($S['t'] === '') continue;
                    foreach (array_slice((array) ($sec['items'] ?? []), 0, 16) as $x) { $t = $str($x['t'] ?? '', 70); if ($t === '') continue; $S['items'][] = ['t' => $t, 'u' => uwciv_url_ok($x['u'] ?? '#'), 'i' => uwciv_icon_fix($x['i'] ?? ''), 'd' => $str($x['d'] ?? '', 90)]; }
                    $it['sections'][] = $S;
                }
                $c = (array) ($m['cta'] ?? []);
                if ($str($c['t'] ?? '', 50) !== '') $it['cta'] = ['t' => $str($c['t'], 50), 'd' => $str($c['d'] ?? '', 120), 'u' => uwciv_url_ok($c['u'] ?? '#'), 'b' => $str($c['b'] ?? 'المزيد', 30), 'i' => uwciv_icon_fix($c['i'] ?? '')];
                $out[] = $it;
            }
            if (!$out) $err = 'القائمة فارغة.';
            else {
                $f = uwciv_menu_file(); if (is_file($f)) @copy($f, $f . '.prev');
                if (@file_put_contents($f, json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT), LOCK_EX) === false) $err = 'تعذّر الحفظ على الخادم.';
                else { uwciv_staff_log('عدّل القائمة الرئيسية للموقع'); header('Location: menu.php?ok=' . rawurlencode('تم حفظ القائمة ونشرها في الموقع.') . '&sel=' . (int) ($_POST['sel'] ?? 0)); exit; }
            }
        }
    }
}
if (isset($_GET['ok'])) $msg = (string) $_GET['ok'];
$tree = uwciv_menu_tree(); $custom = is_file(uwciv_menu_file());
$icons = ['fas fa-university', 'fas fa-graduation-cap', 'fas fa-book', 'fas fa-book-open', 'fas fa-quran', 'fas fa-mosque', 'fas fa-scroll', 'fas fa-feather-alt', 'fas fa-pen-nib', 'fas fa-landmark', 'fas fa-gavel', 'fas fa-balance-scale', 'fas fa-users', 'fas fa-user-tie', 'fas fa-user-graduate', 'fas fa-chalkboard-teacher', 'fas fa-laptop-code', 'fas fa-desktop', 'fas fa-certificate', 'fas fa-award', 'fas fa-microscope', 'fas fa-flask', 'fas fa-language', 'fas fa-globe-asia', 'fas fa-map-marker-alt', 'fas fa-phone-alt', 'far fa-envelope', 'fab fa-whatsapp', 'far fa-newspaper', 'far fa-images', 'far fa-calendar-alt', 'fas fa-clipboard-check', 'fas fa-sitemap', 'fas fa-chess', 'fas fa-info-circle', 'far fa-question-circle', 'fas fa-briefcase', 'fas fa-hands-helping', 'fas fa-coins', 'fas fa-bullhorn', 'fas fa-layer-group', 'fas fa-seedling', 'fas fa-crown', 'fas fa-play-circle', 'fas fa-link', 'fas fa-user-plus', 'fas fa-shield-alt', 'fas fa-star', 'fas fa-check-double', 'fas fa-file-alt', 'fas fa-folder-open', 'fas fa-cogs', 'fas fa-home', 'fas fa-heart', 'fas fa-lightbulb', 'fas fa-mobile-alt'];
uwciv_admin_open('menu', 'القائمة الرئيسية للموقع', 'عدّل العناوين الرئيسية وعناوينها الفرعية وروابط كل عنوان فرعي — مع الأيقونة والوصف — ثم احفظ لتظهر فورًا في أعلى كل صفحات الموقع.',
    '<a class="ad-b g" href="/index.php" target="_blank"><i class="fas fa-eye"></i> معاينة الموقع</a>' . ($custom ? '<form method="post" style="margin:0" data-confirm="إعادة القائمة إلى الوضع الافتراضي؟ ستُحفظ نسخة احتياطية من تعديلاتك.">' . uwciv_csrf_field() . '<input type="hidden" name="act" value="reset"><button class="ad-b g"><i class="fas fa-undo"></i> الافتراضي</button></form>' : '') . '<button class="ad-b p" type="button" id="mSave"><i class="fas fa-save"></i> حفظ ونشر</button>');
uwciv_admin_toast($err ?: $msg, (bool) $err);
?>
<style>
.me{display:grid;grid-template-columns:300px minmax(0,1fr);gap:18px;align-items:start}
.me-list{background:#fff;border-radius:22px;box-shadow:var(--sh-1);padding:12px;position:sticky;top:80px}
.me-list h3{margin:4px 6px 10px;font-size:15px;font-weight:900;color:var(--ink);display:flex;gap:8px;align-items:center}.me-list h3 i{color:#b98f25}
.me-m{display:flex;align-items:center;gap:8px;border-radius:14px;padding:8px 10px;cursor:pointer;border:2px solid transparent;margin-bottom:4px;transition:.15s}
.me-m:hover{background:var(--mist)}
.me-m.on{border-color:var(--teal);background:var(--cyan-soft)}
.me-m.off{opacity:.5}
.me-m b{flex:1;font-size:14.5px;color:var(--ink)}
.me-m small{font-size:11px;color:var(--muted);background:#fff;border-radius:20px;padding:1px 7px}
.me-m button{border:0;background:none;color:var(--muted);cursor:pointer;padding:4px 5px;border-radius:8px}
.me-m button:hover{background:#fff;color:var(--ink)}
.me-add{width:100%;border:1.5px dashed #bcd8c6;background:#fff;color:var(--teal);border-radius:12px;padding:10px;font-weight:900;cursor:pointer;font-family:inherit;margin-top:6px}
.me-ed .cp-card>h2 .sp{flex:1}
.me-sec{border:1px solid var(--ad-line);border-radius:18px;margin-bottom:12px;background:#fbfdfb;overflow:hidden}
.me-sh{display:grid;grid-template-columns:44px minmax(0,1.2fr) minmax(0,1fr) minmax(0,1.4fr) auto;gap:8px;align-items:center;padding:12px;background:#f2f8f4;border-bottom:1px solid var(--ad-line)}
.me-ir{display:grid;grid-template-columns:44px minmax(0,1.1fr) minmax(0,1.2fr) minmax(0,1fr) minmax(0,1.3fr) auto;gap:6px;align-items:center;padding:6px 12px}
.me-ir:nth-child(even){background:#fff}
.me input,.me select{border:1.5px solid #dbe7df;border-radius:10px;padding:0 10px;height:38px;font-family:inherit;font-size:13.5px;background:#fff;min-width:0;width:100%;box-sizing:border-box}
.me-sh input.t{font-weight:900;font-size:14.5px}
.me .ico{width:40px;height:38px;border-radius:10px;display:grid;place-items:center;background:var(--cyan-soft);color:var(--teal);font-size:16px;border:0;cursor:pointer}
.me .bt{display:flex;gap:2px}
.me .bt button{border:0;background:var(--mist);width:30px;height:30px;border-radius:8px;cursor:pointer;color:var(--ink-3);font-size:12px}
.me .bt button.rm{background:#fdecef;color:#c0392b}
.me-addi{border:0;background:none;color:var(--teal);font-weight:800;cursor:pointer;font-family:inherit;padding:8px 12px 12px;font-size:13px}
.me-lbl{display:grid;grid-template-columns:44px minmax(0,1.1fr) minmax(0,1.2fr) minmax(0,1fr) minmax(0,1.3fr) auto;gap:6px;padding:8px 12px 0;font-size:11.5px;color:var(--muted);font-weight:800}
.me-pick{position:fixed;z-index:400;background:#fff;border-radius:16px;box-shadow:0 30px 60px -20px rgba(0,0,0,.45);padding:10px;display:grid;grid-template-columns:repeat(8,38px);gap:4px;max-width:340px}
.me-pick button{width:38px;height:38px;border:0;border-radius:10px;background:var(--mist);color:var(--ink-3);cursor:pointer;font-size:15px}
.me-pick button:hover{background:var(--teal);color:#fff}
.me-pick input{grid-column:1/-1;height:34px;border:1.5px solid #dbe7df;border-radius:10px;padding:0 10px;font-family:inherit;direction:ltr}
.me-dirty{display:none;align-items:center;gap:8px;background:#fff4e0;color:#8a5a00;border-radius:12px;padding:8px 14px;font-weight:800;margin-bottom:12px}
.me-dirty.on{display:flex}
@media(max-width:1100px){.me{grid-template-columns:minmax(0,1fr)}.me-list{position:static}.me-sh,.me-ir{grid-template-columns:44px minmax(0,1fr) auto}.me-sh>*:nth-child(3),.me-sh>*:nth-child(4){grid-column:2/3}.me-ir>*:nth-child(3),.me-ir>*:nth-child(4),.me-ir>*:nth-child(5){grid-column:2/3}.me-lbl{display:none}}
</style>
<div class="me-dirty" id="mDirty"><i class="fas fa-exclamation-circle"></i> لديك تعديلات غير محفوظة — اضغط «حفظ ونشر».</div>
<div class="me">
  <aside class="me-list"><h3><i class="fas fa-stream"></i> العناوين الرئيسية</h3><div id="mList"></div><button type="button" class="me-add" id="mAdd"><i class="fas fa-plus"></i> عنوان رئيسي جديد</button></aside>
  <div class="me-ed" id="mEd"></div>
</div>
<form method="post" id="mForm"><?= uwciv_csrf_field() ?><input type="hidden" name="act" value="save"><input type="hidden" name="tree" id="mTree"><input type="hidden" name="sel" id="mSel"></form>
<script>
(function(){
var T=<?= json_encode($tree, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,ICONS=<?= json_encode($icons) ?>,sel=Math.min(<?= (int) ($_GET['sel'] ?? 0) ?>,T.length-1),dirty=false;
var $=function(i){return document.getElementById(i)},esc=function(s){return String(s==null?'':s).replace(/[&<>"]/g,function(c){return{'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]})};
function mark(){dirty=true;$('mDirty').classList.add('on');}
window.addEventListener('beforeunload',function(e){if(dirty){e.preventDefault();e.returnValue='';}});
function mv(a,i,d){var j=i+d;if(j<0||j>=a.length)return false;var x=a[i];a[i]=a[j];a[j]=x;return true;}
function list(){$('mList').innerHTML=T.map(function(m,i){var n=(m.sections||[]).reduce(function(s,x){return s+x.items.length},0);return '<div class="me-m'+(i===sel?' on':'')+(m.hidden?' off':'')+'" data-i="'+i+'"><b>'+esc(m.label)+'</b><small>'+(m.sections||[]).length+' / '+n+'</small><span class="bt"><button type="button" data-a="up" title="أعلى"><i class="fas fa-arrow-up"></i></button><button type="button" data-a="dn" title="أسفل"><i class="fas fa-arrow-down"></i></button><button type="button" data-a="hide" title="'+(m.hidden?'إظهار':'إخفاء')+'"><i class="far fa-eye'+(m.hidden?'-slash':'')+'"></i></button></span></div>';}).join('');}
function ed(){var m=T[sel];if(!m){$('mEd').innerHTML='';return;}m.sections=m.sections||[];
 var h='<div class="cp-card"><h2><i class="fas fa-heading"></i> العنوان الرئيسي<span class="sp"></span><button type="button" class="cp-b r sm" data-a="delm"><i class="fas fa-trash"></i> حذف العنوان</button></h2><div class="cp-f three">'+
  '<label>النص الظاهر في القائمة<div class="cp-in"><i class="fas fa-font"></i><input data-f="label" value="'+esc(m.label)+'" maxlength="40"></div></label>'+
  '<label>الرابط عند الضغط<div class="cp-in"><i class="fas fa-link"></i><input data-f="url" value="'+esc(m.url)+'" dir="ltr" placeholder="/page.php"></div></label>'+
  '<label>وصف قصير (يظهر في رأس القائمة)<div class="cp-in"><i class="fas fa-align-right"></i><input data-f="desc" value="'+esc(m.desc)+'" maxlength="160"></div></label></div></div>';
 h+='<div class="cp-card"><h2><i class="fas fa-list-ul"></i> العناوين الفرعية وروابطها <small style="color:var(--muted);font-weight:700;font-size:13px">('+m.sections.length+')</small><span class="sp"></span><button type="button" class="cp-b p sm" data-a="adds"><i class="fas fa-plus"></i> عنوان فرعي</button></h2><p>كل عنوان فرعي يظهر في عمود القائمة، وتحته روابطه (العناوين الفرعية الفرعية). اضغط على الأيقونة لتغييرها.</p>';
 if(!m.sections.length)h+='<div class="cp-empty" style="padding:16px"><i class="fas fa-stream"></i>بلا عناوين فرعية — سيظهر العنوان رابطًا مباشرًا. أضف عنوانًا فرعيًا لتظهر القائمة المنسدلة.</div>';
 m.sections.forEach(function(s,si){h+='<div class="me-sec" data-s="'+si+'"><div class="me-sh"><button type="button" class="ico" data-ico="s"><i class="'+esc(s.i)+'"></i></button><input class="t" data-sf="t" value="'+esc(s.t)+'" placeholder="العنوان الفرعي"><input data-sf="d" value="'+esc(s.d)+'" placeholder="وصف قصير"><span style="font-size:12px;color:var(--muted);font-weight:700">'+s.items.length+' رابط</span><span class="bt"><button type="button" data-a="sup"><i class="fas fa-arrow-up"></i></button><button type="button" data-a="sdn"><i class="fas fa-arrow-down"></i></button><button type="button" class="rm" data-a="sdel"><i class="fas fa-trash"></i></button></span></div>'+
  '<div class="me-lbl"><span>أيقونة</span><span>اسم الرابط</span><span>الرابط</span><span>وصف قصير</span><span></span><span></span></div>'+
  s.items.map(function(x,xi){return '<div class="me-ir" data-x="'+xi+'"><button type="button" class="ico" data-ico="x"><i class="'+esc(x.i)+'"></i></button><input data-xf="t" value="'+esc(x.t)+'" placeholder="اسم الرابط"><input data-xf="u" value="'+esc(x.u)+'" dir="ltr" placeholder="/page.php"><input data-xf="d" value="'+esc(x.d)+'" placeholder="وصف قصير" style="grid-column:span 2"><span class="bt"><button type="button" data-a="xup"><i class="fas fa-arrow-up"></i></button><button type="button" data-a="xdn"><i class="fas fa-arrow-down"></i></button><button type="button" class="rm" data-a="xdel"><i class="fas fa-times"></i></button></span></div>';}).join('')+
  '<button type="button" class="me-addi" data-a="addx"><i class="fas fa-plus-circle"></i> رابط جديد تحت «'+esc(s.t)+'»</button></div>';});
 h+='</div>';
 var c=m.cta||{t:'',d:'',u:'',b:'',i:'fas fa-star'};
 h+='<div class="cp-card"><h2><i class="fas fa-bullhorn"></i> بطاقة الدعوة <small style="color:var(--muted);font-weight:700;font-size:13px">(اختيارية — تظهر يسار القائمة)</small></h2><div class="cp-f three">'+
  '<label>العنوان<div class="cp-in"><button type="button" class="ico" data-ico="c" style="width:30px;height:30px"><i class="'+esc(c.i)+'"></i></button><input data-cf="t" value="'+esc(c.t)+'" placeholder="اتركه فارغًا لإخفاء البطاقة"></div></label>'+
  '<label>النص<div class="cp-in"><i class="fas fa-align-right"></i><input data-cf="d" value="'+esc(c.d)+'"></div></label>'+
  '<label>نص الزر<div class="cp-in"><i class="fas fa-hand-pointer"></i><input data-cf="b" value="'+esc(c.b)+'"></div></label>'+
  '<label class="w">رابط الزر<div class="cp-in"><i class="fas fa-link"></i><input data-cf="u" value="'+esc(c.u)+'" dir="ltr"></div></label></div></div>';
 $('mEd').innerHTML=h;}
function render(){list();ed();}
$('mList').addEventListener('click',function(e){var r=e.target.closest('.me-m');if(!r)return;var i=+r.dataset.i,a=e.target.closest('[data-a]');
 if(a){if(a.dataset.a==='up'&&mv(T,i,-1)){if(sel===i)sel--;else if(sel===i-1)sel++;mark();}if(a.dataset.a==='dn'&&mv(T,i,1)){if(sel===i)sel++;else if(sel===i+1)sel--;mark();}if(a.dataset.a==='hide'){T[i].hidden=!T[i].hidden;mark();}render();return;}
 sel=i;render();window.scrollTo({top:0,behavior:'smooth'});});
$('mAdd').onclick=function(){T.push({key:'',label:'عنوان جديد',url:'#',desc:'',hidden:false,sections:[{t:'عنوان فرعي',i:'fas fa-folder-open',d:'',items:[{t:'رابط',u:'/index.php',i:'fas fa-link',d:''}]}],cta:null});sel=T.length-1;mark();render();};
var ed$=$('mEd');
ed$.addEventListener('input',function(e){var m=T[sel],t=e.target,s,x;
 if(t.dataset.f){m[t.dataset.f]=t.value;if(t.dataset.f==='label')list();}
 else if(t.dataset.sf){m.sections[+t.closest('.me-sec').dataset.s][t.dataset.sf]=t.value;}
 else if(t.dataset.xf){s=m.sections[+t.closest('.me-sec').dataset.s];s.items[+t.closest('.me-ir').dataset.x][t.dataset.xf]=t.value;}
 else if(t.dataset.cf){m.cta=m.cta||{t:'',d:'',u:'',b:'',i:'fas fa-star'};m.cta[t.dataset.cf]=t.value;}
 mark();});
ed$.addEventListener('click',function(e){var a=e.target.closest('[data-a]'),ic=e.target.closest('[data-ico]');var m=T[sel];
 if(ic){pick(ic);return;}if(!a)return;var sec=e.target.closest('.me-sec'),si=sec?+sec.dataset.s:-1,ir=e.target.closest('.me-ir'),xi=ir?+ir.dataset.x:-1;
 switch(a.dataset.a){
  case 'delm':if(confirm('حذف العنوان الرئيسي «'+m.label+'» بكل عناوينه الفرعية؟')){T.splice(sel,1);sel=Math.max(0,sel-1);}break;
  case 'adds':m.sections.push({t:'عنوان فرعي جديد',i:'fas fa-folder-open',d:'',items:[]});break;
  case 'sup':mv(m.sections,si,-1);break;case 'sdn':mv(m.sections,si,1);break;
  case 'sdel':if(!confirm('حذف «'+m.sections[si].t+'» وروابطه؟'))return;m.sections.splice(si,1);break;
  case 'addx':m.sections[si].items.push({t:'',u:'',i:'fas fa-link',d:''});break;
  case 'xup':mv(m.sections[si].items,xi,-1);break;case 'xdn':mv(m.sections[si].items,xi,1);break;
  case 'xdel':m.sections[si].items.splice(xi,1);break;}
 mark();render();
 if(a.dataset.a==='addx'){var last=ed$.querySelectorAll('.me-sec')[si].querySelectorAll('.me-ir');if(last.length)last[last.length-1].querySelector('input').focus();}});
var P=null;function pick(btn){if(P)P.remove();P=document.createElement('div');P.className='me-pick';P.innerHTML='<input placeholder="fas fa-… (اكتب اسم أيقونة Font Awesome)">'+ICONS.map(function(c){return '<button type="button" data-c="'+c+'" title="'+c+'"><i class="'+c+'"></i></button>';}).join('');document.body.appendChild(P);
 var r=btn.getBoundingClientRect();P.style.top=Math.min(innerHeight-P.offsetHeight-10,r.bottom+6)+'px';P.style.left=Math.max(10,Math.min(innerWidth-P.offsetWidth-10,r.left))+'px';
 function set(c){var m=T[sel],k=btn.dataset.ico;if(k==='s')m.sections[+btn.closest('.me-sec').dataset.s].i=c;else if(k==='x'){m.sections[+btn.closest('.me-sec').dataset.s].items[+btn.closest('.me-ir').dataset.x].i=c;}else{m.cta=m.cta||{t:'',d:'',u:'',b:'',i:''};m.cta.i=c;}btn.innerHTML='<i class="'+c+'"></i>';mark();P.remove();P=null;}
 P.addEventListener('click',function(e){var b=e.target.closest('[data-c]');if(b)set(b.dataset.c);});
 P.querySelector('input').addEventListener('keydown',function(e){if(e.key==='Enter'){e.preventDefault();var v=this.value.trim();if(/^(fas|far|fab) fa-[a-z0-9-]+$/.test(v))set(v);}});P.querySelector('input').focus();}
document.addEventListener('mousedown',function(e){if(P&&!P.contains(e.target)&&!e.target.closest('[data-ico]')){P.remove();P=null;}});
$('mSave').onclick=function(){dirty=false;$('mTree').value=JSON.stringify(T);$('mSel').value=sel;this.innerHTML='<i class="fas fa-spinner fa-spin"></i> جارٍ الحفظ…';$('mForm').submit();};
document.addEventListener('keydown',function(e){if((e.ctrlKey||e.metaKey)&&e.key==='s'){e.preventDefault();$('mSave').click();}});
render();})();
</script>
<?php uwciv_admin_close(); ?>
