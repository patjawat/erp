<?php

use app\modules\jd\models\JdTemplateBlock;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

/** @var app\modules\hr\models\Employees $employee */
/** @var app\modules\jd\models\JdEmployee $jd */
/** @var app\modules\jd\models\JdEmployeeSection $section */

$this->title = 'แก้ไขหัวข้อ — ' . $section->title;
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนบุคลากร', 'url' => ['/hr/employees/index']];
$this->params['breadcrumbs'][] = ['label' => $employee->fullname, 'url' => ['/hr/employees/view', 'id' => $employee->id]];
$this->params['breadcrumbs'][] = ['label' => 'JD', 'url' => ['view', 'emp_id' => $employee->id]];
$this->params['breadcrumbs'][] = 'แก้ไขหัวข้อ';

$isStructured = (bool) $section->section_code;
$columns = $isStructured ? JdTemplateBlock::editorColumns($section->block_type ?: 'named_items') : [];
$data = $section->getData() + ['intro' => '', 'items' => []];
$items = $data['items'] ?? [];
if (in_array($section->block_type, ['kpi', 'approval'], true) && $items === []) {
    $items = [[]];
}
$employeeUrl = Json::htmlEncode(Url::to(['/hr/organization/list-employee']));
?>

<style>
.jd-section-editor{--ink-1:#1a202c;--ink-2:#4a5568;--ink-3:#718096;--surface:#fff;--surface-2:#f7f9fc;--line:rgba(15,23,42,.08);--line-strong:rgba(15,23,42,.14);--primary:#0d6efd;--primary-soft:rgba(13,110,253,.08);max-width:1100px}.jd-editor-panel{background:var(--surface);border:1px solid var(--line);border-radius:10px;box-shadow:0 1px 2px rgba(15,23,42,.04)}.jd-editor-panel__head{padding:.9rem 1rem;border-bottom:1px solid var(--line);background:var(--surface-2);border-radius:10px 10px 0 0}.jd-editor-panel__body{padding:1rem}.jd-item{border:1px solid var(--line-strong);border-radius:8px;padding:.85rem;margin-bottom:.75rem}.jd-item-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:.75rem}.jd-label{display:block;color:var(--ink-2);font-size:.8rem;font-weight:600;margin-bottom:.35rem}.jd-input{width:100%;min-height:42px;border:1px solid var(--line-strong);border-radius:8px;padding:.55rem .65rem;background:#fff}.jd-input:focus{border-color:var(--primary);outline:3px solid var(--primary-soft)}.jd-rte{border:1px solid var(--line-strong);border-radius:8px;overflow:hidden;background:#fff}.jd-rte:focus-within{border-color:var(--primary);box-shadow:0 0 0 3px var(--primary-soft)}.jd-rte__toolbar{display:flex;flex-wrap:wrap;gap:.2rem;padding:.35rem;border-bottom:1px solid var(--line);background:var(--surface-2)}.jd-rte__button{width:36px;height:36px;border:0;border-radius:6px;background:transparent;color:var(--ink-2)}.jd-rte__button:hover,.jd-rte__button:focus-visible,.jd-rte__button.is-active{background:#eef2f7;color:var(--ink-1)}.jd-rte__area{min-height:110px;padding:.75rem;outline:0;line-height:1.6}.jd-rte__area p:last-child,.jd-rte__area ul:last-child,.jd-rte__area ol:last-child{margin-bottom:0}.jd-savebar{position:sticky;bottom:0;padding:.75rem 1rem;background:rgba(255,255,255,.97);border:1px solid var(--line);border-radius:10px;box-shadow:0 -4px 16px rgba(15,23,42,.06);z-index:10}@media(max-width:767.98px){.jd-editor-panel__body{padding:.8rem}.jd-savebar .btn{min-height:44px}}@media(prefers-reduced-motion:reduce){.jd-section-editor *{transition:none!important}}
</style>

<div class="jd-section-editor">
    <?php $form = ActiveForm::begin(['id' => 'jd-section-form']); ?>
    <div class="jd-editor-panel mb-3">
        <div class="jd-editor-panel__head">
            <h5 class="fw-semibold mb-1"><?= Html::encode($section->title) ?></h5>
            <p class="small text-muted mb-0">จัดรูปแบบข้อความได้เหมือนหน้าเอกสาร SOP และบันทึกลง JD Revision นี้โดยตรง</p>
        </div>
        <div class="jd-editor-panel__body">
            <?= $form->field($section, 'title')->textInput(['maxlength' => true, 'class' => 'jd-input'])->label('หัวข้อ') ?>

            <?php if ($isStructured): ?>
                <?php if (!in_array($section->block_type, ['kpi', 'approval'], true)): ?>
                    <label class="jd-label">บทนำหรือหมายเหตุของหมวด</label>
                    <textarea class="jd-input mb-3" rows="2" data-intro data-richtext><?= Html::encode($data['intro'] ?? '') ?></textarea>
                <?php else: ?>
                    <input type="hidden" data-intro value="">
                    <p class="small text-muted mb-3"><?= $section->block_type === 'kpi'
                        ? 'ตัวชี้วัด 1 รายการต่อ 1 แถว สามารถเพิ่มได้มากกว่าหนึ่งรายการ'
                        : 'เลือกผู้ลงนามจากทะเบียนบุคลากรเท่านั้น ระบบจะบันทึกรหัสบุคลากรไว้สำหรับการยืนยันอ่านและลงนามเอกสาร' ?></p>
                <?php endif; ?>
                <div data-items>
                    <?php foreach ($items as $item): ?>
                        <div class="jd-item">
                            <div class="jd-item-grid">
                                <?php foreach ($columns as $key => $label): ?>
                                    <label><span class="jd-label"><?= Html::encode($label) ?></span>
                                    <?php if ($section->block_type === 'approval' && $key === 'role'): ?>
                                        <?= Html::dropDownList('', $item[$key] ?? '', ['ผู้จัดทำ' => 'ผู้จัดทำ', 'ผู้ตรวจสอบ' => 'ผู้ตรวจสอบ', 'ผู้อนุมัติ' => 'ผู้อนุมัติ'], ['class' => 'jd-input', 'data-key' => $key]) ?>
                                    <?php elseif ($section->block_type === 'approval' && $key === 'employee_id'): ?>
                                        <select class="jd-input jd-employee-picker" data-key="employee_id">
                                            <?php if (!empty($item['employee_id'])): ?><option value="<?= (int) $item['employee_id'] ?>" selected><?= Html::encode($item['employee_name'] ?? ('พนักงาน #' . $item['employee_id'])) ?></option><?php endif; ?>
                                        </select>
                                    <?php elseif ($section->block_type === 'kpi'): ?>
                                        <input type="text" class="jd-input" data-key="<?= Html::encode($key) ?>" value="<?= Html::encode($item[$key] ?? ($key === 'expectation' ? ($item['measurement'] ?? '') : '')) ?>" placeholder="<?= Html::encode($label) ?>">
                                    <?php else: ?>
                                        <textarea class="jd-input" rows="2" data-key="<?= Html::encode($key) ?>" data-richtext><?= Html::encode($item[$key] ?? ($key === 'expectation' ? ($item['measurement'] ?? '') : '')) ?></textarea>
                                    <?php endif; ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <div class="text-end mt-2"><button type="button" class="btn btn-sm btn-outline-danger" data-remove><i class="bi bi-trash me-1"></i>ลบรายการ</button></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary mb-3" data-add><i class="bi bi-plus-lg me-1"></i><?= $section->block_type === 'approval' ? 'เพิ่มผู้ลงนาม' : 'เพิ่มรายการ' ?></button>
                <?= Html::hiddenInput('section_payload', '', ['id' => 'section-payload']) ?>
            <?php else: ?>
                <?= $form->field($section, 'content')->textarea(['rows' => 12, 'class' => 'jd-input', 'data-richtext' => true, 'placeholder' => 'กรอกรายละเอียดของหัวข้อนี้'])->label('รายละเอียด') ?>
            <?php endif; ?>

            <?= $form->field($section, 'sort_order')->textInput(['type' => 'number', 'class' => 'jd-input'])->label('ลำดับ') ?>
        </div>
    </div>
    <div class="jd-savebar d-flex flex-wrap justify-content-end gap-2">
        <?= Html::a('ยกเลิก', ['view', 'emp_id' => $employee->id, 'id' => $jd->id], ['class' => 'btn btn-outline-secondary']) ?>
        <?= Html::submitButton('<i class="bi bi-check-lg me-1"></i>บันทึกหัวข้อ', ['class' => 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>

<?php
$columnsJson = Json::htmlEncode($columns);
$blockType = Json::htmlEncode($section->block_type ?: 'named_items');
$this->registerJs(<<<JS
(function(){
  var columns={$columnsJson}, blockType={$blockType}, employeeUrl={$employeeUrl};
  var commands=[['bold','type-bold','ตัวหนา'],['italic','type-italic','ตัวเอียง'],['underline','type-underline','ขีดเส้นใต้'],['insertUnorderedList','list-ul','หัวข้อย่อย'],['insertOrderedList','list-ol','ลำดับเลข'],['removeFormat','eraser','ล้างรูปแบบ']];
  function esc(v){var d=document.createElement('div');d.textContent=v||'';return d.innerHTML;}
  function seed(v){return /<(?:p|br|ul|ol|li|strong|em|b|i|u)\\b/i.test(v)?v:esc(v).replace(/\\r?\\n/g,'<br>');}
  function enhanceRich(scope){
    (scope||document).querySelectorAll('[data-richtext]:not([data-rte-ready])').forEach(function(t){
      t.dataset.rteReady='1';var wrap=document.createElement('div');wrap.className='jd-rte';var bar=document.createElement('div');bar.className='jd-rte__toolbar';bar.setAttribute('role','toolbar');bar.setAttribute('aria-label','จัดรูปแบบข้อความ');
      commands.forEach(function(c){var b=document.createElement('button');b.type='button';b.className='jd-rte__button';b.dataset.command=c[0];b.title=c[2];b.setAttribute('aria-label',c[2]);b.innerHTML='<i class="bi bi-'+c[1]+'"></i>';bar.appendChild(b);});
      var area=document.createElement('div');area.className='jd-rte__area';area.contentEditable='true';area.setAttribute('role','textbox');area.setAttribute('aria-multiline','true');area.innerHTML=seed(t.value);area._source=t;t.classList.add('visually-hidden');t.after(wrap);wrap.append(bar,area);
    });
  }
  function enhanceEmployees(scope){if(!window.jQuery||!jQuery.fn.select2)return;jQuery(scope||document).find('.jd-employee-picker:not(.select2-hidden-accessible)').select2({width:'100%',placeholder:'ค้นหาและเลือกผู้ลงนาม',allowClear:true,minimumInputLength:1,ajax:{url:employeeUrl,dataType:'json',delay:250,data:function(p){return{q:p.term};},processResults:function(d){return{results:d.items||[]};}}});}
  function field(key,label){if(blockType==='approval'&&key==='role')return '<label><span class="jd-label">'+esc(label)+'</span><select class="jd-input" data-key="role"><option>ผู้จัดทำ</option><option>ผู้ตรวจสอบ</option><option>ผู้อนุมัติ</option></select></label>';if(blockType==='approval'&&key==='employee_id')return '<label><span class="jd-label">'+esc(label)+'</span><select class="jd-input jd-employee-picker" data-key="employee_id"></select></label>';if(blockType==='kpi')return '<label><span class="jd-label">'+esc(label)+'</span><input type="text" class="jd-input" data-key="'+esc(key)+'" placeholder="'+esc(label)+'"></label>';return '<label><span class="jd-label">'+esc(label)+'</span><textarea class="jd-input" rows="2" data-key="'+esc(key)+'" data-richtext></textarea></label>';}
  document.addEventListener('mousedown',function(e){if(e.target.closest('.jd-rte__button'))e.preventDefault();});
  document.addEventListener('click',function(e){
    var cmd=e.target.closest('.jd-rte__button');if(cmd){var area=cmd.closest('.jd-rte').querySelector('.jd-rte__area');area.focus();document.execCommand(cmd.dataset.command,false,null);return;}
    if(e.target.closest('[data-add]')){var fields='';Object.keys(columns).forEach(function(k){fields+=field(k,columns[k]);});document.querySelector('[data-items]').insertAdjacentHTML('beforeend','<div class="jd-item"><div class="jd-item-grid">'+fields+'</div><div class="text-end mt-2"><button type="button" class="btn btn-sm btn-outline-danger" data-remove><i class="bi bi-trash me-1"></i>ลบรายการ</button></div></div>');enhanceRich(document);enhanceEmployees(document);}
    var remove=e.target.closest('[data-remove]');if(remove)remove.closest('.jd-item').remove();
  });
  document.getElementById('jd-section-form').addEventListener('submit',function(){
    document.querySelectorAll('.jd-rte__area').forEach(function(a){a._source.value=(a.textContent.trim()||a.querySelector('li'))?a.innerHTML:'';});
    var output=document.getElementById('section-payload');if(!output)return;var payload={intro:document.querySelector('[data-intro]').value.trim(),items:[]};
    document.querySelectorAll('.jd-item').forEach(function(row){var item={};row.querySelectorAll('[data-key]').forEach(function(input){item[input.dataset.key]=(input.value||'').trim();if(input.dataset.key==='employee_id'){var option=input.options[input.selectedIndex];item.employee_name=option&&option.value?option.text.trim():'';}});payload.items.push(item);});output.value=JSON.stringify(payload);
  });
  enhanceRich(document);enhanceEmployees(document);
})();
JS);
?>
