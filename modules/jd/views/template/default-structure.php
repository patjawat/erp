<?php

use yii\helpers\Html;
use yii\helpers\Json;

/** @var yii\web\View $this */
/** @var app\modules\jd\models\JdStructureDefault[] $models */
/** @var array<string, string> $typeOptions */

$this->title = 'จัดการโครงสร้าง Template';
$initial = array_map(static fn($model): array => [
    'section_code' => (string) $model->section_code,
    'title' => (string) $model->title,
    'block_type' => (string) $model->block_type,
    'help_text' => (string) $model->help_text,
    'is_enabled' => (int) $model->is_enabled === 1,
    'is_locked' => (int) $model->is_locked === 1,
], $models);
$initialJson = Json::htmlEncode($initial);
$typeJson = Json::htmlEncode($typeOptions);
?>
<?php $this->beginBlock('page-title'); ?>
<div>
    <h4 class="fw-semibold mb-1"><?= Html::encode($this->title) ?></h4>
    <div class="text-body-secondary small">กำหนดหัวข้อมาตรฐานสำหรับ Template JD ทุกตำแหน่งของโรงพยาบาล</div>
</div>
<?php $this->endBlock(); ?>

<div class="container-fluid py-3 jd-structure-manager">
    <?php foreach (['success' => 'success', 'error' => 'danger'] as $flash => $class): ?>
        <?php if (Yii::$app->session->hasFlash($flash)): ?>
            <div class="alert alert-<?= $class ?> alert-dismissible fade show" role="alert">
                <?= Html::encode(Yii::$app->session->getFlash($flash)) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="ปิด"></button>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>

    <div class="alert alert-primary d-flex gap-2 align-items-start" role="note">
        <i class="bi bi-info-circle mt-1" aria-hidden="true"></i>
        <div>
            <strong>โครงสร้างเริ่มต้นระดับโรงพยาบาล</strong>
            <div class="small">การเปลี่ยนแปลงจะซิงก์ชื่อ รูปแบบ ลำดับ และสถานะกับทุก Template โดยเก็บเนื้อหาเดิมไว้ หัวข้อที่ลบจะถูกปิดใช้งานเพื่อให้กู้ข้อมูลได้</div>
        </div>
    </div>

    <?= Html::beginForm(['default-structure'], 'post', ['id' => 'jd-default-structure-form']) ?>
    <?= Html::hiddenInput('structure', '', ['id' => 'jd-structure-output']) ?>
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div class="text-body-secondary small" id="jd-structure-summary" aria-live="polite"></div>
        <button type="button" class="btn btn-primary" id="jd-add-section"><i class="bi bi-plus-lg me-1" aria-hidden="true"></i>เพิ่มหัวข้อ</button>
    </div>

    <div class="jd-structure-layout">
        <section class="card bg-body border shadow-sm" aria-labelledby="jd-outline-title">
            <div class="card-header bg-body-tertiary border-bottom">
                <h2 class="h6 fw-semibold mb-1" id="jd-outline-title">โครงสร้างหัวข้อ JD</h2>
                <p class="small text-body-secondary mb-0">ลากหรือใช้ปุ่มลูกศรเพื่อเปลี่ยนลำดับ</p>
            </div>
            <div class="list-group list-group-flush" id="jd-structure-list" role="listbox" aria-label="หัวข้อในโครงสร้าง JD"></div>
        </section>

        <section class="card bg-body border shadow-sm jd-structure-inspector" aria-labelledby="jd-inspector-title">
            <div class="card-header bg-body-tertiary border-bottom d-flex align-items-center justify-content-between gap-2">
                <h2 class="h6 fw-semibold mb-0" id="jd-inspector-title">รายละเอียดหัวข้อ</h2>
                <span class="badge bg-secondary-subtle text-secondary-emphasis d-none" id="jd-locked-badge"><i class="bi bi-lock-fill me-1" aria-hidden="true"></i>ล็อกลำดับท้ายสุด</span>
            </div>
            <div class="card-body" id="jd-inspector-empty">
                <p class="text-body-secondary mb-0">เลือกหัวข้อทางซ้ายเพื่อแก้ไขรายละเอียด</p>
            </div>
            <div class="card-body d-none" id="jd-inspector-fields">
                <div class="mb-3">
                    <label class="form-label fw-semibold" for="jd-section-title">ชื่อหัวข้อ <span class="text-danger" aria-hidden="true">*</span></label>
                    <input type="text" class="form-control" id="jd-section-title" maxlength="255" required>
                    <div class="invalid-feedback">กรุณาระบุชื่อหัวข้อ</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold" for="jd-section-type">รูปแบบข้อมูล</label>
                    <select class="form-select" id="jd-section-type"></select>
                    <div class="form-text">รูปแบบนี้กำหนดช่องข้อมูลที่ผู้จัดทำ JD จะกรอก</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold" for="jd-section-help">คำอธิบายช่วยกรอก</label>
                    <textarea class="form-control" id="jd-section-help" rows="4" maxlength="500" placeholder="อธิบายว่าควรกรอกข้อมูลใดในหัวข้อนี้"></textarea>
                    <div class="form-text"><span id="jd-help-count">0</span>/500 ตัวอักษร</div>
                </div>
                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" role="switch" id="jd-section-enabled">
                    <label class="form-check-label" for="jd-section-enabled">เปิดใช้งานหัวข้อนี้</label>
                </div>
                <div class="d-flex flex-wrap gap-2 border-top pt-3">
                    <button type="button" class="btn btn-outline-danger ms-auto" id="jd-delete-section"><i class="bi bi-trash me-1" aria-hidden="true"></i>ลบหัวข้อ</button>
                </div>
                <div class="alert alert-warning mt-3 mb-0 d-none" id="jd-locked-note" role="note">
                    หัวข้อการอนุมัติเอกสารถูกกำหนดให้อยู่ลำดับสุดท้ายเสมอ แต่ยังแก้ชื่อ คำอธิบาย และผู้ลงนามใน Template ได้
                </div>
            </div>
        </section>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-3 sticky-bottom bg-body border rounded-3 p-3 shadow-sm">
        <?= Html::a('กลับคลัง Template', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        <?= Html::submitButton('<i class="bi bi-check-lg me-1" aria-hidden="true"></i>บันทึกการเปลี่ยนแปลง', ['class' => 'btn btn-primary', 'id' => 'jd-save-structure']) ?>
    </div>
    <?= Html::endForm() ?>
</div>

<style>
.jd-structure-manager{max-width:1200px}.jd-structure-layout{display:grid;grid-template-columns:minmax(300px,2fr) minmax(340px,3fr);gap:1rem;align-items:start}.jd-structure-inspector{position:sticky;top:1rem}.jd-structure-row{display:grid;grid-template-columns:auto auto minmax(0,1fr) auto;align-items:center;gap:.5rem;padding:.65rem .75rem;border:0;border-bottom:1px solid var(--bs-border-color);background:var(--bs-body-bg);color:var(--bs-body-color);text-align:left}.jd-structure-row:last-child{border-bottom:0}.jd-structure-row:hover{background:var(--bs-tertiary-bg)}.jd-structure-row.is-selected{background:var(--bs-primary-bg-subtle);color:var(--bs-primary-text-emphasis)}.jd-structure-row.is-dragging{opacity:.55}.jd-drag-handle{cursor:grab}.jd-drag-handle:active{cursor:grabbing}.jd-row-title{min-width:0}.jd-row-title strong{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.jd-row-title small{display:block}.jd-row-order{min-width:1.75rem;text-align:center;font-variant-numeric:tabular-nums}.jd-row-actions{display:flex;gap:.25rem}.jd-row-actions .btn{width:2rem;height:2rem;padding:0}.jd-structure-row:focus-visible{outline:3px solid rgba(var(--bs-primary-rgb),.25);outline-offset:-3px}@media(max-width:767.98px){.jd-structure-layout{grid-template-columns:1fr}.jd-structure-inspector{position:static}.jd-structure-row{grid-template-columns:auto auto minmax(0,1fr)}.jd-row-actions{grid-column:2/4;justify-content:flex-end}.jd-structure-manager .sticky-bottom{position:static!important}}@media(prefers-reduced-motion:reduce){.jd-structure-row{transition:none}}
</style>

<?php
$this->registerJs(<<<JS
(function(){
  var rows={$initialJson};
  var typeOptions={$typeJson};
  var selectedCode=rows.length?rows[0].section_code:null;
  var list=document.getElementById('jd-structure-list');
  var fields=document.getElementById('jd-inspector-fields');
  var empty=document.getElementById('jd-inspector-empty');
  var title=document.getElementById('jd-section-title');
  var type=document.getElementById('jd-section-type');
  var help=document.getElementById('jd-section-help');
  var enabled=document.getElementById('jd-section-enabled');
  var deleteButton=document.getElementById('jd-delete-section');
  var lockedBadge=document.getElementById('jd-locked-badge');
  var lockedNote=document.getElementById('jd-locked-note');
  var draggedCode=null;

  Object.keys(typeOptions).forEach(function(value){var option=document.createElement('option');option.value=value;option.textContent=typeOptions[value];type.appendChild(option);});
  function escapeHtml(value){var div=document.createElement('div');div.textContent=value||'';return div.innerHTML;}
  function current(){return rows.find(function(row){return row.section_code===selectedCode;})||null;}
  function approvalLast(){var approval=rows.find(function(row){return row.section_code==='approval';});rows=rows.filter(function(row){return row.section_code!=='approval';});if(approval)rows.push(approval);}
  function render(){
    approvalLast();list.innerHTML='';
    rows.forEach(function(row,index){
      var item=document.createElement('div');item.className='jd-structure-row'+(row.section_code===selectedCode?' is-selected':'');item.dataset.code=row.section_code;item.draggable=!row.is_locked;item.tabIndex=0;item.setAttribute('role','option');item.setAttribute('aria-selected',row.section_code===selectedCode?'true':'false');
      item.innerHTML='<span class="jd-drag-handle text-body-secondary" aria-hidden="true"><i class="bi '+(row.is_locked?'bi-lock-fill':'bi-grip-vertical')+'"></i></span><span class="jd-row-order">'+(index+1)+'</span><span class="jd-row-title"><strong>'+escapeHtml(row.title)+'</strong><small class="text-body-secondary">'+escapeHtml(typeOptions[row.block_type]||row.block_type)+' · '+(row.is_enabled?'เปิดใช้งาน':'ปิดใช้งาน')+'</small></span><span class="jd-row-actions"><button type="button" class="btn btn-sm btn-outline-secondary" data-move="up" aria-label="เลื่อนขึ้น" '+(index===0||row.is_locked?'disabled':'')+'><i class="bi bi-chevron-up"></i></button><button type="button" class="btn btn-sm btn-outline-secondary" data-move="down" aria-label="เลื่อนลง" '+(index>=rows.length-2||row.is_locked?'disabled':'')+'><i class="bi bi-chevron-down"></i></button></span>';
      item.addEventListener('click',function(event){var move=event.target.closest('[data-move]');if(move){event.stopPropagation();moveRow(row.section_code,move.dataset.move);return;}select(row.section_code);});
      item.addEventListener('keydown',function(event){if(event.key==='Enter'||event.key===' '){event.preventDefault();select(row.section_code);}});
      item.addEventListener('dragstart',function(){draggedCode=row.section_code;item.classList.add('is-dragging');});
      item.addEventListener('dragend',function(){draggedCode=null;item.classList.remove('is-dragging');});
      item.addEventListener('dragover',function(event){if(draggedCode&&!row.is_locked)event.preventDefault();});
      item.addEventListener('drop',function(event){event.preventDefault();reorder(draggedCode,row.section_code);});
      list.appendChild(item);
    });
    document.getElementById('jd-structure-summary').textContent='ทั้งหมด '+rows.length+' หัวข้อ · เปิดใช้งาน '+rows.filter(function(row){return row.is_enabled;}).length+' หัวข้อ';
    fillInspector();
  }
  function select(code){selectedCode=code;render();}
  function fillInspector(){
    var row=current();empty.classList.toggle('d-none',!!row);fields.classList.toggle('d-none',!row);if(!row)return;
    title.value=row.title;type.value=row.block_type;help.value=row.help_text||'';enabled.checked=!!row.is_enabled;
    type.disabled=!!row.is_locked;enabled.disabled=!!row.is_locked;deleteButton.classList.toggle('d-none',!!row.is_locked);lockedBadge.classList.toggle('d-none',!row.is_locked);lockedNote.classList.toggle('d-none',!row.is_locked);document.getElementById('jd-help-count').textContent=help.value.length;
  }
  function update(field,value){var row=current();if(!row)return;row[field]=value;if(field==='title'){title.classList.toggle('is-invalid',!value.trim());var label=list.querySelector('[data-code="'+row.section_code+'"] .jd-row-title strong');if(label)label.textContent=value||'ยังไม่ระบุชื่อ';return;}render();}
  function moveRow(code,direction){var index=rows.findIndex(function(row){return row.section_code===code;});var target=direction==='up'?index-1:index+1;if(index<0||target<0||target>=rows.length||rows[target].is_locked)return;var item=rows.splice(index,1)[0];rows.splice(target,0,item);render();}
  function reorder(fromCode,toCode){if(!fromCode||fromCode===toCode)return;var from=rows.findIndex(function(row){return row.section_code===fromCode;});var to=rows.findIndex(function(row){return row.section_code===toCode;});if(from<0||to<0||rows[from].is_locked||rows[to].is_locked)return;var item=rows.splice(from,1)[0];rows.splice(to,0,item);render();}
  title.addEventListener('input',function(){update('title',title.value);});type.addEventListener('change',function(){update('block_type',type.value);});help.addEventListener('input',function(){var row=current();if(row)row.help_text=help.value;document.getElementById('jd-help-count').textContent=help.value.length;});enabled.addEventListener('change',function(){update('is_enabled',enabled.checked);});
  document.getElementById('jd-add-section').addEventListener('click',function(){var code='custom_'+Date.now().toString(36);var approvalIndex=rows.findIndex(function(row){return row.section_code==='approval';});var row={section_code:code,title:'หัวข้อใหม่',block_type:'named_items',help_text:'',is_enabled:true,is_locked:false};rows.splice(approvalIndex<0?rows.length:approvalIndex,0,row);selectedCode=code;render();title.focus();title.select();});
  deleteButton.addEventListener('click',function(){var row=current();if(!row||row.is_locked)return;if(!window.confirm('ลบหัวข้อ “'+row.title+'” ออกจากโครงสร้างเริ่มต้นหรือไม่? เนื้อหาเดิมใน Template จะถูกเก็บไว้แต่ปิดใช้งาน'))return;rows=rows.filter(function(item){return item.section_code!==row.section_code;});selectedCode=rows.length?rows[0].section_code:null;render();});
  document.getElementById('jd-default-structure-form').addEventListener('submit',function(event){if(rows.some(function(row){return !row.title.trim();})){event.preventDefault();title.classList.add('is-invalid');return;}approvalLast();document.getElementById('jd-structure-output').value=JSON.stringify(rows);document.getElementById('jd-save-structure').disabled=true;});
  render();
})();
JS);
?>
