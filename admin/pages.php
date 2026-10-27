<?php
/* صفحات المعلومات — تعديل محتوى صفحات الإدارة والمراكز والعمادات وغيرها */
require_once dirname(__DIR__) . '/inc/admin_ui.php';
uwciv_admin_gate(['title' => 'صفحات المعلومات', 'perm' => 'pages']);
require_once dirname(__DIR__) . '/inc/i18n.php';
require_once dirname(__DIR__) . '/inc/pages.php';
$msg = ''; $err = '';
$GROUPS = uwciv_info_page_groups();
$p = preg_replace('/[^a-z_]/', '', (string) ($_GET['p'] ?? ''));
if ($p !== '' && !isset($UWCIV_PAGES[$p])) $p = '';

/* تنقية HTML: نسمح بوسوم التنسيق البسيطة والروابط فقط */
function uwciv_clean_html($html)
{
    $html = trim((string) $html); if ($html === '') return '';
    $allow = ['p' => [], 'br' => [], 'b' => [], 'strong' => [], 'i' => [], 'em' => [], 'u' => [], 'ul' => [], 'ol' => [], 'li' => [], 'h4' => [], 'blockquote' => [], 'a' => ['href']];
    $doc = new DOMDocument(); libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="utf-8"?><div id="r">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD); libxml_clear_errors();
    $walk = function (DOMNode $n) use (&$walk, $allow, $doc) {
        for ($c = $n->firstChild; $c; $c = $next) {
            $next = $c->nextSibling;
            if ($c instanceof DOMElement) {
                $tag = strtolower($c->tagName); $walk($c);
                if (in_array($tag, ['div', 'span', 'font', 'h1', 'h2', 'h3', 'h5', 'h6'], true)) { // نفكّ الوسم ونبقي محتواه
                    if (in_array($tag, ['div', 'h1', 'h2', 'h3', 'h5', 'h6'], true)) { $pEl = $doc->createElement($tag === 'div' ? 'p' : 'h4'); while ($c->firstChild) $pEl->appendChild($c->firstChild); $n->replaceChild($pEl, $c); }
                    else { while ($c->firstChild) $n->insertBefore($c->firstChild, $c); $n->removeChild($c); }
                    continue;
                }
                if (!isset($allow[$tag])) { $n->removeChild($c); continue; }
                foreach (iterator_to_array($c->attributes) as $a) if (!in_array($a->name, $allow[$tag], true)) $c->removeAttribute($a->name);
                if ($tag === 'a') { $h = trim($c->getAttribute('href')); if (!preg_match('~^(/|https?://|mailto:|tel:)~i', $h)) $c->removeAttribute('href'); elseif (preg_match('~^https?://~i', $h)) { $c->setAttribute('target', '_blank'); $c->setAttribute('rel', 'noopener'); } }
            } elseif ($c->nodeType === XML_COMMENT_NODE) $n->removeChild($c);
        }
    };
    $root = $doc->getElementById('r'); $walk($root);
    $out = ''; foreach ($root->childNodes as $c) $out .= $doc->saveHTML($c);
    $out = trim($out);
    if ($out !== '' && !preg_match('~^<(p|ul|ol|h4|blockquote)~', $out)) $out = '<p>' . $out . '</p>';
    return mb_substr($out, 0, 20000);
}

if ($p && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!uwciv_csrf_ok()) $err = 'انتهت صلاحية الصفحة، أعد المحاولة.';
    else {
        $ALL = uwciv_pages_overrides();
        if (($_POST['act'] ?? '') === 'reset') { unset($ALL[$p]); $msg = 'أُعيدت الصفحة إلى محتواها الافتراضي.'; }
        else {
            $lead = trim(mb_substr(strip_tags((string) ($_POST['lead'] ?? '')), 0, 800));
            $secs = [];
            foreach ((array) ($_POST['sec'] ?? []) as $s) { $t = trim(mb_substr(strip_tags((string) ($s['t'] ?? '')), 0, 120)); $b = uwciv_clean_html($s['b'] ?? ''); if ($t !== '' || $b !== '') $secs[] = [$t ?: '—', $b]; }
            if ($lead === '' && !$secs) $err = 'الصفحة فارغة.';
            else { $ALL[$p] = ['lead' => htmlspecialchars($lead, ENT_NOQUOTES, 'UTF-8'), 'sections' => array_map(function ($x) { return [htmlspecialchars($x[0], ENT_NOQUOTES, 'UTF-8'), $x[1]]; }, array_slice($secs, 0, 20))]; $msg = 'تم حفظ الصفحة ونشرها.'; }
        }
        if (!$err) {
            if (@file_put_contents(uwciv_pages_override_file(), json_encode($ALL, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT), LOCK_EX) === false) $err = 'تعذّر الحفظ على الخادم.';
            else { uwciv_staff_log('عدّل صفحة «' . t($p) . '»'); header('Location: pages.php?p=' . $p . '&ok=' . rawurlencode($msg)); exit; }
        }
    }
}
if (isset($_GET['ok'])) $msg = (string) $_GET['ok'];
$OV = uwciv_pages_overrides(); $h = 'uwciv_h';
$plain = function ($html) { return trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) $html), ENT_QUOTES, 'UTF-8'))); };

if (!$p):
    uwciv_admin_open('pages', 'صفحات المعلومات', 'كل صفحات التعريف في الموقع — المجالس والقيادات والإدارات والعمادات والمراكز والاعتماد وغيرها. اختر صفحة لتعديل مقدمتها وأقسامها بمحرر سهل.');
    uwciv_admin_toast($err ?: $msg, (bool) $err);
?>
<style>
.pg-h{display:flex;gap:10px;align-items:center;margin:22px 0 12px;font-size:18px;font-weight:900;color:var(--ink)}
.pg-h i{width:36px;height:36px;border-radius:11px;display:grid;place-items:center;background:linear-gradient(145deg,#e8c46a,#b98f25);color:#0b3119}
.pg-g{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px}
.pgc{background:#fff;border-radius:20px;box-shadow:var(--sh-1);padding:16px 18px;text-decoration:none!important;color:var(--text)!important;display:flex;flex-direction:column;gap:8px;transition:.25s;border-inline-start:4px solid var(--teal)}
.pgc:hover{transform:translateY(-4px);box-shadow:var(--sh-lift)}
.pgc b{color:var(--ink);font-size:16px;display:flex;gap:8px;align-items:center}.pgc b i{color:var(--teal)}
.pgc p{margin:0;color:var(--muted);font-size:13px;line-height:1.8;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.pgc small{color:var(--muted);font-size:12px;display:flex;gap:10px}
</style>
<div class="ad-bar"><div class="ad-in"><i class="fas fa-search"></i><input placeholder="ابحث في عناوين الصفحات ومحتواها…" data-filter="#pgall"></div></div>
<div id="pgall">
<?php foreach ($GROUPS as $gname => $gd): ?>
  <div class="pg-h"><i class="<?= $gd[0] ?>"></i><?= $gname ?></div>
  <div class="pg-g"><?php foreach ($gd[1] as $k): if (!isset($UWCIV_PAGES[$k])) continue; $pg = $UWCIV_PAGES[$k]; ?>
    <a class="pgc fi" href="?p=<?= $k ?>" data-s="<?= $h(t($k) . ' ' . $plain($pg['lead'])) ?>"><b><i class="<?= uwciv_info_page_icon($k) ?>"></i><?= $h(t($k)) ?></b><p><?= $h($plain($pg['lead'])) ?></p><small><span><?= count($pg['sections']) ?> أقسام</span><?= isset($OV[$k]) ? '<span style="color:#1f7a45;font-weight:800">● معدّلة</span>' : '<span>افتراضية</span>' ?></small></a>
  <?php endforeach; ?></div>
<?php endforeach; ?>
</div>
<?php uwciv_admin_close(); exit; endif;

$pg = $UWCIV_PAGES[$p];
uwciv_admin_open('pages', t($p), 'عدّل المقدمة والأقسام — استخدم أزرار التنسيق للعريض والقوائم والروابط. تظهر التعديلات فورًا في الصفحة.', '<a class="ad-b g" href="/info.php?p=' . $p . '" target="_blank"><i class="fas fa-eye"></i> عرض الصفحة</a><a class="ad-b g" href="pages.php"><i class="fas fa-th"></i> كل الصفحات</a>');
uwciv_admin_toast($err ?: $msg, (bool) $err);
?>
<style>
.ps{border:1px solid var(--ad-line);border-radius:18px;margin-bottom:14px;overflow:hidden;background:#fbfdfb}
.ps-h{display:flex;gap:8px;align-items:center;padding:10px 12px;background:#f2f8f4;border-bottom:1px solid var(--ad-line)}
.ps-h b{width:30px;height:30px;border-radius:9px;display:grid;place-items:center;background:var(--ink-3);color:#fff;flex:none;font-size:13px}
.ps-h input{flex:1;border:1.5px solid #dbe7df;border-radius:10px;height:40px;padding:0 12px;font-family:inherit;font-weight:900;font-size:15px;min-width:0}
.ps-h button,.rt-bar button{border:0;width:32px;height:32px;border-radius:9px;background:#fff;cursor:pointer;color:var(--ink-3);box-shadow:inset 0 0 0 1px var(--ad-line)}
.ps-h .rm{background:#fdecef;color:#c0392b;box-shadow:none}
.rt-bar{display:flex;gap:4px;flex-wrap:wrap;padding:8px 12px 0}
.rt{min-height:120px;margin:8px 12px 12px;background:#fff;border:1.5px solid #dbe7df;border-radius:12px;padding:10px 14px;line-height:1.95;font-size:15px;outline:none}
.rt:focus{border-color:var(--teal);box-shadow:0 0 0 4px rgba(31,122,69,.1)}
.rt ul,.rt ol{padding-inline-start:22px;margin:6px 0}.rt h4{margin:8px 0 4px;font-size:16px;color:var(--ink)}.rt p{margin:0 0 8px}
.rt a{color:var(--teal);text-decoration:underline}
</style>
<form method="post" id="pgf"><?= uwciv_csrf_field() ?>
  <div class="cp-card"><h2><i class="<?= uwciv_info_page_icon($p) ?>"></i> المقدمة</h2><p>الفقرة البارزة في أعلى الصفحة.</p>
    <div class="cp-f"><label class="w"><div class="cp-in ta"><i class="fas fa-align-right"></i><textarea name="lead" maxlength="800" style="min-height:90px"><?= $h($plain($pg['lead'])) ?></textarea></div></label></div></div>
  <div class="cp-card"><h2><i class="fas fa-list-ul"></i> أقسام الصفحة <span style="flex:1"></span><button type="button" class="cp-b p sm" id="psAdd"><i class="fas fa-plus"></i> قسم جديد</button></h2><p>لكل قسم عنوان ونص منسّق. حدّد النص ثم اضغط زر التنسيق.</p>
    <div id="psl"><?php foreach ($pg['sections'] as $i => $s): ?>
      <div class="ps"><div class="ps-h"><b><?= $i + 1 ?></b><input class="pst" value="<?= $h($plain($s[0])) ?>" placeholder="عنوان القسم"><button type="button" data-a="up" title="أعلى"><i class="fas fa-arrow-up"></i></button><button type="button" data-a="dn" title="أسفل"><i class="fas fa-arrow-down"></i></button><button type="button" class="rm" data-a="rm" title="حذف"><i class="fas fa-trash"></i></button></div>
        <div class="rt-bar"><button type="button" data-c="bold" title="عريض"><i class="fas fa-bold"></i></button><button type="button" data-c="insertUnorderedList" title="قائمة نقطية"><i class="fas fa-list-ul"></i></button><button type="button" data-c="insertOrderedList" title="قائمة مرقّمة"><i class="fas fa-list-ol"></i></button><button type="button" data-c="h4" title="عنوان فرعي"><i class="fas fa-heading"></i></button><button type="button" data-c="link" title="رابط"><i class="fas fa-link"></i></button><button type="button" data-c="removeFormat" title="إزالة التنسيق"><i class="fas fa-eraser"></i></button></div>
        <div class="rt" contenteditable="true"><?= $s[1] ?></div></div>
    <?php endforeach; ?></div>
  </div>
  <div id="pgh"></div>
  <div class="cp-btns"><button class="cp-b p" id="pgs"><i class="fas fa-save"></i> حفظ ونشر</button><a class="cp-b g" href="/info.php?p=<?= $p ?>" target="_blank"><i class="fas fa-eye"></i> معاينة</a><?php if (isset($OV[$p])): ?><button class="cp-b r" name="act" value="reset" formnovalidate data-confirm="إعادة الصفحة إلى محتواها الافتراضي؟"><i class="fas fa-undo"></i> الافتراضي</button><?php endif; ?></div>
</form>
<script>
(function(){var L=document.getElementById('psl'),f=document.getElementById('pgf');
function num(){[].forEach.call(L.children,function(x,i){x.querySelector('.ps-h b').textContent=i+1;});}
L.addEventListener('click',function(e){var b=e.target.closest('button');if(!b)return;var s=b.closest('.ps');
  if(b.dataset.a==='rm'){if(L.children.length>1&&confirm('حذف هذا القسم؟')){s.remove();num();}return;}
  if(b.dataset.a==='up'&&s.previousElementSibling){s.previousElementSibling.before(s);num();return;}
  if(b.dataset.a==='dn'&&s.nextElementSibling){s.nextElementSibling.after(s);num();return;}
  var c=b.dataset.c;if(!c)return;var rt=s.querySelector('.rt');rt.focus();
  if(c==='link'){var u=prompt('الرابط (مثل /contact.php أو https://…)');if(u)document.execCommand('createLink',false,u);}
  else if(c==='h4')document.execCommand('formatBlock',false,'h4');else document.execCommand(c,false,null);});
L.addEventListener('mousedown',function(e){if(e.target.closest('.rt-bar button'))e.preventDefault();});
document.getElementById('psAdd').onclick=function(){var n=L.children[0].cloneNode(true);n.querySelector('.pst').value='';n.querySelector('.rt').innerHTML='<p><br></p>';L.appendChild(n);num();n.querySelector('.pst').focus();};
f.addEventListener('submit',function(e){if(e.submitter&&e.submitter.value==='reset')return;var h=document.getElementById('pgh');h.innerHTML='';[].forEach.call(L.children,function(s,i){var a=document.createElement('input');a.type='hidden';a.name='sec['+i+'][t]';a.value=s.querySelector('.pst').value;var b=document.createElement('input');b.type='hidden';b.name='sec['+i+'][b]';b.value=s.querySelector('.rt').innerHTML;h.appendChild(a);h.appendChild(b);});});})();
</script>
<?php uwciv_admin_close(); ?>
