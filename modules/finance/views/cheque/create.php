<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\widgets\datepicker\DatepickerThai;
use app\modules\finance\models\FinanceCheque;

/** @var yii\web\View $this */
/** @var app\modules\finance\models\FinanceCheque $cheque */
/** @var array $accounts */
/** @var array $templates */

$isEdit = $isEdit ?? false;
$this->title = $isEdit ? 'แก้ไขเช็ค' : 'ออกเช็คใหม่';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนคุมเช็ค', 'url' => ['/finance/cheque']];
$this->params['breadcrumbs'][] = $this->title;
$this->beginBlock('page-title');
echo Html::encode($this->title);
$this->endBlock();
$this->beginBlock('sub-title');
echo 'คีย์รายละเอียดเช็ค → ดูตัวอย่างบนเช็คจริง → บันทึกเข้าทะเบียน → พิมพ์';
$this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/views/_ap_menu', ['active' => 'cheque']);
$this->endBlock();

$dateVal = $cheque->cheque_date
    ? (date_create($cheque->cheque_date) ? date_create($cheque->cheque_date)->format('d/m/') . ((int) date_create($cheque->cheque_date)->format('Y') + 543) : '')
    : date('d/m/') . ((int) date('Y') + 543);
$err = fn($attr) => $cheque->hasErrors($attr) ? '<div class="text-danger small mt-1">' . Html::encode($cheque->getFirstError($attr)) . '</div>' : '';
$previewBase = Url::to(['preview']);
$nextBase = Url::to(['next-cheque-no']);
$booksBase = Url::to(['books-by-account']);
$bookCreate = Url::to(['book-create']);
$isEditJs = $isEdit ? 'true' : 'false';
?>

<div class="row g-3">
    <div class="col-lg-5 order-lg-2">
        <?php if ($cheque->hasErrors()): ?>
            <div class="alert alert-danger"><?= implode('<br>', $cheque->getErrorSummary(true)) ?></div>
        <?php endif; ?>
        <div class="card shadow-sm">
            <div class="card-header fw-semibold"><i class="bi bi-cash-stack me-1"></i><?= Html::encode($this->title) ?></div>
            <div class="card-body">
                <?= Html::beginForm($isEdit ? ['update', 'id' => $cheque->id] : ['create'], 'post') ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small">บัญชีจ่าย <span class="text-danger">*</span></label>
                        <select name="cash_account_id" id="ck-acc" class="form-select">
                            <option value="">— เลือกบัญชีจ่าย —</option>
                            <?php foreach ($accounts as $aid => $al): ?>
                                <option value="<?= $aid ?>" <?= (int) $cheque->cash_account_id === (int) $aid ? 'selected' : '' ?>><?= Html::encode($al) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">แม่แบบเช็ค (สำหรับพิมพ์)</label>
                        <select name="template_id" id="ck-tpl" class="form-select">
                            <option value="">— ไม่ระบุ (ใช้แม่แบบที่ใช้งาน) —</option>
                            <?php foreach ($templates as $tid => $tl): ?>
                                <option value="<?= $tid ?>" <?= (int) $cheque->template_id === (int) $tid ? 'selected' : '' ?>><?= Html::encode($tl) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">เลขที่เช็ค <span class="text-danger">*</span></label>
                        <input type="text" name="cheque_no" id="ck-no" class="form-control" value="<?= Html::encode($cheque->cheque_no) ?>" required>
                        <div id="ck-no-hint" class="form-text"></div>
                        <?= $err('cheque_no') ?>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">เล่มเช็ค</label>
                        <select name="book_id" id="ck-book" class="form-select" data-current="<?= (int) $cheque->book_id ?>">
                            <option value="">— เลือกบัญชีก่อน —</option>
                        </select>
                        <div id="ck-book-hint" class="form-text"></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">วันที่สั่งจ่าย</label>
                        <?= DatepickerThai::widget([
                            'name' => 'cheque_date',
                            'value' => $dateVal,
                            'options' => ['id' => 'ck-date', 'class' => 'form-control', 'autocomplete' => 'off', 'placeholder' => 'วว/ดด/พ.ศ.'],
                        ]) ?>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label small">จ่ายให้ (ชื่อผู้รับ) <span class="text-danger">*</span></label>
                        <input type="text" name="payee_name" id="ck-payee" class="form-control" value="<?= Html::encode($cheque->payee_name) ?>" required>
                        <?= $err('payee_name') ?>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">จำนวนเงิน (บาท) <span class="text-danger">*</span></label>
                        <input type="text" inputmode="decimal" name="amount" id="ck-amount" class="form-control text-end" value="<?= $cheque->amount ? Html::encode(number_format((float) $cheque->amount, 2)) : '' ?>" required>
                        <?= $err('amount') ?>
                    </div>
                    <div class="col-12">
                        <div class="alert alert-light border mb-0 py-2 px-3">
                            <span class="text-body-secondary small">ตัวอักษร:</span>
                            <span id="ck-bahttext" class="fw-semibold ms-1">—</span>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <label class="form-label small">รูปแบบเช็ค</label>
                        <select name="form_type" id="ck-form" class="form-select">
                            <?php foreach (FinanceCheque::formTypeOptions() as $fk => $fv): ?>
                                <option value="<?= $fk ?>" <?= ($cheque->form_type ?: FinanceCheque::FORM_AC_PAYEE) === $fk ? 'selected' : '' ?>><?= Html::encode($fv) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">คุมการขีดฆ่า "หรือผู้ถือ" + ขีดคร่อมอัตโนมัติ (ตามคู่มือ)</div>
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2">
                    <?= Html::submitButton('<i class="bi bi-save me-1"></i>' . ($isEdit ? 'บันทึกการแก้ไข' : 'บันทึกเข้าทะเบียน'), ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
                </div>
                <?= Html::endForm() ?>
            </div>
        </div>
    </div>

    <div class="col-lg-7 order-lg-1">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-eye me-1"></i>ตัวอย่างบนเช็ค</span>
                <button type="button" id="ck-refresh" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-clockwise me-1"></i>อัปเดต</button>
            </div>
            <div class="card-body p-2">
                <iframe id="ck-preview" style="width:100%;height:460px;border:1px solid #dee2e6;border-radius:.375rem;background:#fff"></iframe>
                <div class="form-text mt-2">ตัวอย่างซ้อนบนรูปเช็คจริง ปรับข้อมูลด้านซ้ายแล้วกด "อัปเดต" (ตำแหน่งจริงปรับได้ที่เมนู "แม่แบบเช็ค")</div>
            </div>
        </div>
    </div>
</div>

<?php
$js = <<<JS
(function(){
  var digits=['ศูนย์','หนึ่ง','สอง','สาม','สี่','ห้า','หก','เจ็ด','แปด','เก้า'];
  var places=['','สิบ','ร้อย','พัน','หมื่น','แสน'];
  function readInt(s){
    s=s.replace(/^0+/,''); if(s==='') return '';
    if(s.length>6){ return readInt(s.slice(0,s.length-6))+'ล้าน'+readInt(s.slice(s.length-6)); }
    var r='', len=s.length;
    for(var i=0;i<len;i++){ var d=+s[i], place=len-i-1; if(d===0) continue;
      if(place===1&&d===1) r+='สิบ';
      else if(place===1&&d===2) r+='ยี่สิบ';
      else if(place===0&&d===1&&len>1) r+='เอ็ด';
      else r+=digits[d]+places[place];
    } return r;
  }
  function bahtText(v){
    var num=parseFloat(String(v).replace(/[,\\s]/g,''))||0;
    if(num<=0) return '';
    num=num.toFixed(2); var p=num.split('.'), baht=p[0].replace(/^0+/,''), satang=p[1];
    var t=(baht===''?'ศูนย์':readInt(baht))+'บาท';
    if(satang==='00') t+='ถ้วน'; else t+=readInt(satang.replace(/^0+/,'')||'0')+'สตางค์';
    return t;
  }
  function toYmd(th){
    var m=String(th).match(/^(\\d{1,2})\\/(\\d{1,2})\\/(\\d{3,4})$/);
    if(!m) return '';
    var y=parseInt(m[3],10); if(y>2400) y-=543;
    return y+'-'+('0'+m[2]).slice(-2)+'-'+('0'+m[1]).slice(-2);
  }
  var amount=document.getElementById('ck-amount'), baht=document.getElementById('ck-bahttext');
  var payee=document.getElementById('ck-payee'), tpl=document.getElementById('ck-tpl');
  var date=document.getElementById('ck-date'), form=document.getElementById('ck-form');
  var frame=document.getElementById('ck-preview'), base='{$previewBase}';
  var acc=document.getElementById('ck-acc'), no=document.getElementById('ck-no'), noHint=document.getElementById('ck-no-hint');
  var book=document.getElementById('ck-book'), bookHint=document.getElementById('ck-book-hint');
  var nextBase='{$nextBase}', booksBase='{$booksBase}', bookCreate='{$bookCreate}';
  var isEdit={$isEditJs}; // โหมดแก้ไข: ห้ามทับเลขเช็คเดิม
  function fetchNextNo(){ // สำรอง: เลขล่าสุดของบัญชี (กรณีไม่มีเล่ม)
    if(!acc.value){ noHint.textContent=''; return; }
    fetch(nextBase+'?account_id='+encodeURIComponent(acc.value),{headers:{'X-Requested-With':'XMLHttpRequest'}})
      .then(function(r){return r.json();})
      .then(function(d){
        if(d && d.next){ if(!no.value && !isEdit) no.value=d.next; noHint.innerHTML='เลขถัดไปในทะเบียน: <b>'+d.next+'</b>'; }
        else { noHint.textContent='ยังไม่มีเลขเช็คในบัญชีนี้ — กรอกเลขเริ่มต้นเอง'; }
      }).catch(function(){ noHint.textContent=''; });
  }
  function applyBook(){
    var opt=book.options[book.selectedIndex];
    if(!opt || !opt.value){ bookHint.textContent=''; return; }
    var rem=opt.getAttribute('data-remaining'), nx=opt.getAttribute('data-next');
    bookHint.innerHTML='คงเหลือในเล่ม: <b>'+rem+'</b> ใบ';
    if(nx && !isEdit){ no.value=nx; noHint.innerHTML='เลขถัดไปในเล่ม: <b>'+nx+'</b>'; }
    else if(nx){ noHint.innerHTML='เลขถัดไปในเล่ม: <b>'+nx+'</b>'; }
    else if(!isEdit){ noHint.innerHTML='<span class="text-danger">เล่มนี้ใช้หมดแล้ว</span>'; }
    updPreview();
  }
  function fetchBooks(){
    if(!acc.value){ book.innerHTML='<option value="">— เลือกบัญชีก่อน —</option>'; bookHint.textContent=''; return; }
    fetch(booksBase+'?account_id='+encodeURIComponent(acc.value),{headers:{'X-Requested-With':'XMLHttpRequest'}})
      .then(function(r){return r.json();})
      .then(function(list){
        if(list && list.length){
          var h=''; list.forEach(function(b){ h+='<option value="'+b.id+'" data-next="'+(b.next||'')+'" data-remaining="'+b.remaining+'">'+b.label+'</option>'; });
          book.innerHTML=h;
          var cur=book.getAttribute('data-current'); if(isEdit && cur && cur!=='0'){ book.value=cur; }
          applyBook();
        } else {
          book.innerHTML='<option value="">— ไม่มีเล่ม (ใช้เลขล่าสุดบัญชี) —</option>';
          bookHint.innerHTML='<a href="'+bookCreate+'">+ รับเล่มเช็คเข้า</a>'; fetchNextNo();
        }
      }).catch(function(){ book.innerHTML='<option value="">— โหลดเล่มไม่ได้ —</option>'; });
  }
  function updBaht(){ baht.textContent = bahtText(amount.value) || '—'; }
  function tplId(){ if(tpl.value) return tpl.value; return tpl.options[1] ? tpl.options[1].value : ''; }
  function updPreview(){
    var tid=tplId(); if(!tid){ return; }
    var q=new URLSearchParams();
    q.set('id',tid);
    var d=toYmd(date.value); if(d) q.set('d',d);
    q.set('p', payee.value||'');
    q.set('a', String(amount.value).replace(/[,\\s]/g,'')||'0');
    q.set('ft', form.value||'ac_payee');
    frame.src = base+'?'+q.toString();
  }
  amount.addEventListener('input', updBaht);
  document.getElementById('ck-refresh').addEventListener('click', updPreview);
  [payee,amount,date,tpl,form].forEach(function(el){ el.addEventListener('change', updPreview); });
  if(window.jQuery){ jQuery(date).on('change', updPreview); } // datepicker ไทยยิง change ผ่าน jQuery
  if(acc){ acc.addEventListener('change', function(){ fetchBooks(); updPreview(); }); }
  if(book){ book.addEventListener('change', applyBook); }
  updBaht(); updPreview(); if(acc && acc.value) fetchBooks();
})();
JS;
$this->registerJs($js, \yii\web\View::POS_END);
?>
