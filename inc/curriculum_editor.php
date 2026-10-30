<?php
/* نوافذ التحرير المباشر للخطط الدراسية — تظهر للمشرف ومن يملك صلاحية «الخطط الدراسية» فقط */
if (!function_exists('cur_can_edit') || !cur_can_edit()) return;
$__D = cur_doc();
$__prog = preg_replace('/[^a-z0-9-]/', '', (string) ($_GET['p'] ?? ''));
$__levels = isset($__D['programs'][$__prog]) ? array_column($__D['programs'][$__prog]['levels'], 'n') : [];
?>
<datalist id="cu-codes"><?php foreach ($__D['courses'] as $k => $c): ?><option value="<?= cur_h($k) ?>"><?= cur_h($c['n']) ?></option><?php endforeach; ?></datalist>
<div class="cu-modal" id="cu-modal"><div class="cu-dlg"><header><i class="fas fa-pen"></i><b id="cu-mt">تعديل</b><button type="button" data-close>&times;</button></header><form id="cu-form" enctype="multipart/form-data"></form></div></div>
<div class="cu-toast" id="cu-toast"></div>
<script>
(function(){
  var API='/admin/curriculum-api.php', CSRF=<?= json_encode(cur_csrf()) ?>, PROG=<?= json_encode($__prog) ?>, LEVELS=<?= json_encode($__levels, JSON_UNESCAPED_UNICODE) ?>;
  var TYPES=<?= json_encode(array_map(function ($t) { return $t[0]; }, cur_types()), JSON_UNESCAPED_UNICODE) ?>, CATS=<?= json_encode(array_map(function ($t) { return $t[0]; }, cur_book_cats()), JSON_UNESCAPED_UNICODE) ?>, QS=<?= json_encode(array_map(function ($t) { return $t[0]; }, cur_book_quality()), JSON_UNESCAPED_UNICODE) ?>, LANGS=<?= json_encode(array_map(function ($t) { return $t[0]; }, cur_langs()), JSON_UNESCAPED_UNICODE) ?>;
  var COLS=<?= json_encode(array_map(function ($c) { return $c['n']; }, $GLOBALS['UWCIV_COLLEGES']), JSON_UNESCAPED_UNICODE) ?>;
  var modal=document.getElementById('cu-modal'), form=document.getElementById('cu-form'), title=document.getElementById('cu-mt'), toast=document.getElementById('cu-toast');
  function esc(s){return String(s==null?'':s).replace(/[&<>"]/g,function(c){return{'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]})}
  function opts(o,v){return Object.keys(o).map(function(k){return '<option value="'+esc(k)+'"'+(k==v?' selected':'')+'>'+esc(o[k])+'</option>'}).join('')}
  function say(m,bad){toast.textContent=m;toast.style.background=bad?'#9b1f45':'#0b3119';toast.style.display='block';clearTimeout(say.t);say.t=setTimeout(function(){toast.style.display='none'},3500)}
  function post(data,file){var fd=new FormData();fd.append('csrf',CSRF);if(PROG&&!data.program)fd.append('program',PROG);Object.keys(data).forEach(function(k){fd.append(k,data[k])});if(file)fd.append('pdf',file);
    return fetch(API,{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){return r.json()}).catch(function(){return{ok:false,msg:'تعذّر الاتصال بالخادم.'}})}
  function done(r){if(!r.ok){var m=form.querySelector('.msg');if(m&&modal.classList.contains('on'))m.textContent=r.msg;else say(r.msg,true);return false}
    say(r.msg||'تم الحفظ.');setTimeout(function(){location.href=r.go||location.href.split('#')[0]+location.hash},500);return true}
  function open(t,html,onsub){title.textContent=t;form.innerHTML=html+'<div class="msg"></div><div class="act"><button type="button" class="cu-btn" data-close>إلغاء</button><button class="cu-btn p" type="submit"><i class="fas fa-check"></i> حفظ</button></div>';
    modal.classList.add('on');form.onsubmit=function(e){e.preventDefault();var b=form.querySelector('[type=submit]');b.disabled=true;b.innerHTML='<i class="fas fa-spinner fa-spin"></i> جارٍ الحفظ…';Promise.resolve(onsub(new FormData(form))).then(function(ok){if(ok===false){b.disabled=false;b.innerHTML='<i class="fas fa-check"></i> حفظ'}})};
    var f=form.querySelector('input,select,textarea');if(f)f.focus()}
  modal.addEventListener('click',function(e){if(e.target===modal||e.target.closest('[data-close]'))modal.classList.remove('on')});
  document.addEventListener('keydown',function(e){if(e.key==='Escape')modal.classList.remove('on')});
  function obj(fd){var o={};fd.forEach(function(v,k){if(!(v instanceof File))o[k]=v});return o}

  function courseForm(c,code,level){
    c=c||{n:'',en:'',bn:'',ur:'',cr:2,pre:[],t:'M',d:''};
    open(code?'تعديل المقرر '+code:'مقرر جديد',
      '<div class="two"><label>رمز المقرر (مثل FIQ201)<input name="code" value="'+esc(code||'')+'" required dir="ltr" pattern="[A-Za-z]{2,4}[0-9]{3}"></label><label>الساعات<input name="cr" type="number" min="0" max="200" value="'+esc(c.cr)+'" required></label></div>'+
      '<label>الاسم بالعربية<input name="n" value="'+esc(c.n)+'" required></label>'+
      '<label>English name<input name="en" value="'+esc(c.en)+'" dir="ltr"></label>'+
      '<div class="two"><label>বাংলা নাম (Bengali)<input name="bn" value="'+esc(c.bn)+'" dir="ltr"></label><label>اردو نام (Urdu)<input name="ur" value="'+esc(c.ur)+'"></label></div>'+
      '<div class="two"><label>نوع المقرر<select name="t">'+opts(TYPES,c.t)+'</select></label><label>المتطلبات السابقة (رموز مفصولة بفاصلة)<input name="pre" value="'+esc((c.pre||[]).join(', '))+'" dir="ltr" list="cu-codes"></label></div>'+
      '<label>وصف المقرر<textarea name="d">'+esc(c.d)+'</textarea></label>',
      function(fd){var o=obj(fd);o.act='course-save';o.orig=code||'';if(level!=null)o.level=level;return post(o).then(done)});
  }
  function bookForm(code,b){
    b=b||{lang:'ar',cat:'main',q:'approved',title:'',author:'',url:'',file:''};
    open(b.id?'تعديل كتاب':'إضافة كتاب إلى '+code,
      '<div class="two"><label>اللغة<select name="lang">'+opts(LANGS,b.lang)+'</select></label><label>التصنيف<select name="cat">'+opts(CATS,b.cat)+'</select></label></div>'+
      '<label>عنوان الكتاب<input name="title" value="'+esc(b.title)+'" required></label>'+
      '<div class="two"><label>المؤلف<input name="author" value="'+esc(b.author)+'"></label><label>الجودة<select name="q">'+opts(QS,b.q)+'</select></label></div>'+
      '<label>ملف PDF من جهازك (حتى 60 ميغابايت — يُفحص قبل الحفظ)<input type="file" name="pdf_file" accept="application/pdf,.pdf"></label>'+
      (b.file?'<label style="flex-direction:row;display:flex;gap:8px;align-items:center;font-weight:700"><input type="checkbox" name="remove_file" value="1" style="width:auto"> حذف الملف المرفوع الحالي</label>':'')+
      '<label>أو رابط خارجي موثوق (https)<input name="url" value="'+esc(b.url)+'" dir="ltr" placeholder="https://shamela.ws/book/…"></label>',
      function(fd){var o=obj(fd);o.act='book-save';o.code=code;o.book=b.id||'';var f=form.querySelector('[name=pdf_file]').files[0];delete o.pdf_file;return post(o,f).then(done)});
  }
  function programForm(p,slug){
    p=p||{n:'',en:'',bn:'',ur:'',college:'',stage:'bac',target:144,unit:'cr',kkni:6,years:4,desc:'',plos:[],status:'proposed'};
    open(slug?'بيانات البرنامج':'برنامج جديد',
      (slug?'':'<label>معرّف البرنامج (لاتيني، مثل quran-ba)<input name="program" required dir="ltr" pattern="[a-z][a-z0-9-]{2,40}"></label>')+
      '<label>اسم البرنامج<input name="n" value="'+esc(p.n)+'" required></label><label>English name<input name="en" value="'+esc(p.en)+'" dir="ltr"></label>'+
      '<div class="two"><label>বাংলা নাম<input name="bn" value="'+esc(p.bn||'')+'" dir="ltr"></label><label>اردو نام<input name="ur" value="'+esc(p.ur||'')+'"></label></div>'+
      '<div class="two"><label>الكلية<select name="college">'+opts(COLS,p.college)+'</select></label><label>المرحلة<select name="stage">'+opts({dip:'الدبلوم',bac:'البكالوريوس',mas:'الماجستير',phd:'الدكتوراه'},p.stage)+'</select></label></div>'+
      '<div class="two"><label>مجموع الساعات المطلوب<input name="target" type="number" value="'+esc(p.target)+'"></label><label>نوع الساعات<select name="unit">'+opts({cr:'ساعات معتمدة',contact:'ساعات اتصال'},p.unit)+'</select></label></div>'+
      '<div class="two"><label>مستوى KKNI<input name="kkni" type="number" min="0" max="9" value="'+esc(p.kkni)+'"></label><label>مدة الدراسة (سنوات)<input name="years" type="number" min="0" max="8" value="'+esc(p.years)+'"></label></div>'+
      (slug?'':'<label>عدد المستويات<input name="levels" type="number" min="1" max="12" value="8"></label>')+
      '<label>حالة الخطة<select name="status">'+opts({proposed:'مقترحة بانتظار الاعتماد',approved:'معتمدة'},p.status)+'</select></label>'+
      '<label>وصف البرنامج<textarea name="desc">'+esc(p.desc)+'</textarea></label>'+
      '<label>مخرجات التعلم (سطر لكل مخرج)<textarea name="plos" style="min-height:110px">'+esc((p.plos||[]).join('\n'))+'</textarea></label>',
      function(fd){var o=obj(fd);o.act='program-save';if(slug)o.program=slug;return post(o).then(done)});
  }
  window.cuProgramForm=programForm;

  document.addEventListener('click',function(e){
    var b=e.target.closest('[data-act]');if(!b)return;var a=b.dataset.act,code=b.dataset.code,lv=b.dataset.level;
    if(a==='course-edit'){post({act:'course-get',code:code}).then(function(r){r.ok?courseForm(r.course,code):say(r.msg,true)})}
    else if(a==='course-delete'){if(!confirm('حذف المقرر '+code+' نهائيًا من الكتالوج وكل الخطط مع كتبه؟'))return;post({act:'course-delete',code:code}).then(function(r){if(!r.ok&&r.confirm&&confirm(r.msg))post({act:'course-delete',code:code,force:1}).then(done);else done(r)})}
    else if(a==='plan-add'){open('إضافة مقرر إلى '+LEVELS[lv],'<label>اختر مقررًا موجودًا بالرمز<input name="code" list="cu-codes" dir="ltr" placeholder="FIQ201" required></label><p style="margin:0;font-size:13px;color:var(--muted)">أو <a href="#" id="cu-newc">أنشئ مقررًا جديدًا في هذا المستوى</a>.</p>',
        function(fd){return post({act:'plan-add',code:(fd.get('code')||'').toUpperCase(),level:lv}).then(done)});
      document.getElementById('cu-newc').onclick=function(ev){ev.preventDefault();courseForm(null,'',lv)}}
    else if(a==='plan-remove'){if(confirm('إزالة '+code+' من هذه الخطة؟ (يبقى المقرر في الكتالوج)'))post({act:'plan-remove',code:code}).then(done)}
    else if(a==='plan-move'){var o={};LEVELS.forEach(function(n,i){o[i]=n});open('نقل '+code+' إلى مستوى آخر','<label>المستوى الجديد<select name="to">'+opts(o,lv)+'</select></label>',function(fd){return post({act:'plan-move',code:code,to:fd.get('to')}).then(done)})}
    else if(a==='level-add'){open('مستوى جديد','<label>اسم المستوى<input name="name" value="المستوى '+(LEVELS.length+1)+'"></label>',function(fd){return post({act:'level-add',name:fd.get('name')}).then(done)})}
    else if(a==='level-rename'){open('تعديل اسم المستوى','<label>الاسم<input name="name" value="'+esc(LEVELS[lv])+'" required></label>',function(fd){return post({act:'level-rename',level:lv,name:fd.get('name')}).then(done)})}
    else if(a==='level-delete'){if(confirm('حذف «'+LEVELS[lv]+'»؟ يجب أن يكون فارغًا.'))post({act:'level-delete',level:lv}).then(done)}
    else if(a==='program-edit'){post({act:'program-get'}).then(function(r){r.ok?programForm(r.program,PROG):say(r.msg,true)})}
    else if(a==='program-new'){programForm(null,'')}
    else if(a==='program-delete'){var s=b.dataset.program;if(confirm('حذف البرنامج '+s+' نهائيًا؟ تبقى مقرراته في الكتالوج.'))post({act:'program-delete',program:s}).then(done)}
    else if(a==='book-add'){bookForm(code,null)}
    else if(a==='book-edit'){post({act:'book-get',code:code,book:b.dataset.book}).then(function(r){r.ok?bookForm(code,r.book):say(r.msg,true)})}
    else if(a==='book-delete'){if(confirm('حذف هذا الكتاب وملفه؟'))post({act:'book-delete',code:code,book:b.dataset.book}).then(done)}
    else return;
    e.preventDefault();
  });
})();
</script>
