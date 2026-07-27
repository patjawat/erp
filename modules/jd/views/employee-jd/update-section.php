<?php

use app\modules\jd\models\JdTemplateBlock;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use kartik\select2\Select2Asset;

/** @var app\modules\hr\models\Employees $employee */
/** @var app\modules\jd\models\JdEmployee $jd */
/** @var app\modules\jd\models\JdEmployeeSection $section */

$this->title = 'แก้ไขหัวข้อ — ' . $section->title;
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนบุคลากร', 'url' => ['/hr/employees/index']];
$this->params['breadcrumbs'][] = ['label' => $employee->fullname, 'url' => ['/hr/employees/view', 'id' => $employee->id]];
$this->params['breadcrumbs'][] = ['label' => 'JD', 'url' => ['view', 'emp_id' => $employee->id]];
$this->params['breadcrumbs'][] = 'แก้ไขหัวข้อ';

$isStructured = (bool) $section->section_code;
$editorType = JdTemplateBlock::editorType($section->block_type ?: 'named_items', $section->section_code);
$isTabular = JdTemplateBlock::isTabularType($editorType);
$columns = $isStructured ? JdTemplateBlock::editorColumns($editorType) : [];
$data = $section->getData() + ['intro' => '', 'items' => []];
$items = $data['items'] ?? [];
if (($isTabular || $editorType === 'approval') && $items === []) {
    $items = [[]];
}
$structuredHelp = match ($editorType) {
    'kpi' => 'ตัวชี้วัด 1 รายการต่อ 1 แถว สามารถเพิ่มได้มากกว่าหนึ่งรายการ',
    'competency' => 'สมรรถนะ 1 รายการต่อ 1 แถว ระบุพฤติกรรมที่แสดงออกและระดับที่ต้องการ แล้วเพิ่มรายการได้ตามตำแหน่ง',
    default => 'เลือกผู้ลงนามจากทะเบียนบุคลากรเท่านั้น ระบบจะบันทึกรหัสบุคลากรไว้สำหรับการยืนยันอ่านและลงนามเอกสาร',
};
Select2Asset::register($this);
$employeeUrl = Json::htmlEncode(Url::to(['/hr/organization/list-employee']));
?>

<style>
.jd-section-editor{--ink-1:var(--bs-emphasis-color);--ink-2:var(--bs-secondary-color);--surface:var(--bs-body-bg);--surface-2:var(--bs-tertiary-bg);--line:var(--bs-border-color-translucent);--line-strong:var(--bs-border-color);--primary:var(--bs-primary);--primary-soft:rgba(var(--bs-primary-rgb),.12);max-width:1100px}.jd-editor-panel{background:var(--surface);border:1px solid var(--line);border-radius:10px;box-shadow:var(--bs-box-shadow-sm)}.jd-editor-panel__head{padding:.9rem 1rem;border-bottom:1px solid var(--line);background:var(--surface-2);border-radius:10px 10px 0 0}.jd-editor-panel__body{padding:1rem}.jd-item:not(tr){border:1px solid var(--line-strong);border-radius:8px;padding:.85rem;margin-bottom:.75rem}.jd-item-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:.75rem}.jd-label{display:block;color:var(--ink-2);font-size:.8rem;font-weight:600;margin-bottom:.35rem}.jd-input{width:100%;min-height:42px;border:1px solid var(--line-strong);border-radius:8px;padding:.55rem .65rem;color:var(--bs-body-color);background:var(--surface)}.jd-input:focus{border-color:var(--primary);outline:3px solid var(--primary-soft)}.jd-table-editor{width:100%;border-collapse:separate;border-spacing:0;border:1px solid var(--line);border-radius:8px;overflow:hidden}.jd-table-editor th{padding:.55rem .65rem;background:var(--surface-2);color:var(--ink-2);font-size:.8rem;font-weight:600;border-bottom:1px solid var(--line)}.jd-table-editor td{padding:.5rem;border-bottom:1px solid var(--line);vertical-align:top}.jd-table-editor tr:last-child td{border-bottom:0}.jd-table-editor .jd-input{min-height:40px}.jd-table-editor__action{width:54px;text-align:center}.jd-rte{border:1px solid var(--line-strong);border-radius:8px;overflow:hidden;background:var(--surface)}.jd-rte:focus-within{border-color:var(--primary);box-shadow:0 0 0 3px var(--primary-soft)}.jd-rte__toolbar{display:flex;flex-wrap:wrap;gap:.2rem;padding:.35rem;border-bottom:1px solid var(--line);background:var(--surface-2)}.jd-rte__button{width:36px;height:36px;border:0;border-radius:6px;background:transparent;color:var(--ink-2)}.jd-rte__button:hover,.jd-rte__button:focus-visible,.jd-rte__button.is-active{background:var(--surface-2);color:var(--ink-1)}.jd-rte__area{min-height:110px;padding:.75rem;outline:0;line-height:1.6}.jd-rte__area p:last-child,.jd-rte__area ul:last-child,.jd-rte__area ol:last-child{margin-bottom:0}.jd-savebar{position:sticky;bottom:0;padding:.75rem 1rem;background:var(--surface);border:1px solid var(--line);border-radius:10px;box-shadow:var(--bs-box-shadow);z-index:10}@media(max-width:767.98px){.jd-editor-panel__body{padding:.8rem}.jd-savebar .btn{min-height:44px}.jd-table-editor,.jd-table-editor tbody,.jd-table-editor tr,.jd-table-editor td{display:block;width:100%}.jd-table-editor thead{display:none}.jd-table-editor tr{padding:.75rem;border-bottom:1px solid var(--line)}.jd-table-editor tr:last-child{border-bottom:0}.jd-table-editor td{padding:0 0 .65rem;border:0}.jd-table-editor td::before{content:attr(data-label);display:block;margin-bottom:.3rem;color:var(--ink-2);font-size:.8rem;font-weight:600}.jd-table-editor .jd-table-editor__action{width:100%;padding:0;text-align:end}.jd-table-editor .jd-table-editor__action::before{display:none}}@media(prefers-reduced-motion:reduce){.jd-section-editor *{transition:none!important}}
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
                <?php if (!in_array($editorType, ['kpi', 'competency', 'approval'], true)): ?>
                    <label class="jd-label">บทนำหรือหมายเหตุของหมวด</label>
                    <textarea class="jd-input mb-3" rows="2" data-intro data-richtext><?= Html::encode($data['intro'] ?? '') ?></textarea>
                <?php else: ?>
                    <input type="hidden" data-intro value="">
                    <p class="small text-muted mb-3"><?= Html::encode($structuredHelp) ?></p>
                <?php endif; ?>
                <?php if ($isTabular): ?>
                <table class="jd-table-editor mb-3">
                    <thead><tr><?php foreach ($columns as $label): ?><th><?= Html::encode($label) ?></th><?php endforeach; ?><th class="jd-table-editor__action"><span class="visually-hidden">จัดการ</span></th></tr></thead>
                    <tbody data-items>
                    <?php foreach ($items as $item): ?>
                        <tr class="jd-item"><?php foreach ($columns as $key => $label): ?><td data-label="<?= Html::encode($label) ?>"><textarea class="jd-input" rows="2" data-key="<?= Html::encode($key) ?>"><?= Html::encode($item[$key] ?? ($key === 'expectation' ? ($item['measurement'] ?? '') : '')) ?></textarea></td><?php endforeach; ?><td class="jd-table-editor__action"><button type="button" class="btn btn-sm btn-outline-danger" data-remove aria-label="ลบแถว"><i class="bi bi-trash"></i></button></td></tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div data-items>
                    <?php foreach ($items as $item): ?>
                    <div class="jd-item"><div class="jd-item-grid"><?php foreach ($columns as $key => $label): ?><label><span class="jd-label"><?= Html::encode($label) ?></span><?php if ($editorType === 'approval' && $key === 'role'): ?><?= Html::dropDownList('', $item[$key] ?? '', ['ผู้จัดทำ' => 'ผู้จัดทำ', 'ผู้ตรวจสอบ' => 'ผู้ตรวจสอบ', 'ผู้อนุมัติ' => 'ผู้อนุมัติ'], ['class' => 'jd-input', 'data-key' => $key]) ?><?php elseif ($editorType === 'approval' && $key === 'employee_id'): ?><select class="jd-input jd-employee-picker" data-key="employee_id"><?php if (!empty($item['employee_id'])): ?><option value="<?= (int) $item['employee_id'] ?>" selected><?= Html::encode($item['employee_name'] ?? ('พนักงาน #' . $item['employee_id'])) ?></option><?php endif; ?></select><?php else: ?><textarea class="jd-input" rows="2" data-key="<?= Html::encode($key) ?>" data-richtext><?= Html::encode($item[$key] ?? '') ?></textarea><?php endif; ?></label><?php endforeach; ?></div><div class="text-end mt-2"><button type="button" class="btn btn-sm btn-outline-danger" data-remove><i class="bi bi-trash me-1"></i>ลบรายการ</button></div></div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <button type="button" class="btn btn-sm btn-outline-primary mb-3" data-add><i class="bi bi-plus-lg me-1"></i><?= $editorType === 'approval' ? 'เพิ่มผู้ลงนาม' : ($editorType === 'competency' ? 'เพิ่มสมรรถนะ' : 'เพิ่มรายการ') ?></button>
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
$blockType = Json::htmlEncode($editorType);
$tabular = Json::htmlEncode($isTabular);
$this->registerJs(<<<JS
(function(){
  var columns={$columnsJson}, blockType={$blockType}, tabular={$tabular}, employeeUrl={$employeeUrl};
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
  function field(key,label){if(blockType==='approval'&&key==='role')return '<label><span class="jd-label">'+esc(label)+'</span><select class="jd-input" data-key="role"><option>ผู้จัดทำ</option><option>ผู้ตรวจสอบ</option><option>ผู้อนุมัติ</option></select></label>';if(blockType==='approval'&&key==='employee_id')return '<label><span class="jd-label">'+esc(label)+'</span><select class="jd-input jd-employee-picker" data-key="employee_id"></select></label>';if(blockType==='kpi'||blockType==='competency')return '<label><span class="jd-label">'+esc(label)+'</span><input type="text" class="jd-input" data-key="'+esc(key)+'" placeholder="'+esc(label)+'"></label>';return '<label><span class="jd-label">'+esc(label)+'</span><textarea class="jd-input" rows="2" data-key="'+esc(key)+'" data-richtext></textarea></label>';}
  function tableRow(){var cells='';Object.keys(columns).forEach(function(k){cells+='<td data-label="'+esc(columns[k])+'"><textarea class="jd-input" rows="2" data-key="'+esc(k)+'" placeholder="'+esc(columns[k])+'"></textarea></td>';});return '<tr class="jd-item">'+cells+'<td class="jd-table-editor__action"><button type="button" class="btn btn-sm btn-outline-danger" data-remove aria-label="ลบแถว"><i class="bi bi-trash"></i></button></td></tr>';}
  document.addEventListener('mousedown',function(e){if(e.target.closest('.jd-rte__button'))e.preventDefault();});
  document.addEventListener('click',function(e){
    var cmd=e.target.closest('.jd-rte__button');if(cmd){var area=cmd.closest('.jd-rte').querySelector('.jd-rte__area');area.focus();document.execCommand(cmd.dataset.command,false,null);return;}
    if(e.target.closest('[data-add]')){if(tabular){document.querySelector('[data-items]').insertAdjacentHTML('beforeend',tableRow());}else{var fields='';Object.keys(columns).forEach(function(k){fields+=field(k,columns[k]);});document.querySelector('[data-items]').insertAdjacentHTML('beforeend','<div class="jd-item"><div class="jd-item-grid">'+fields+'</div><div class="text-end mt-2"><button type="button" class="btn btn-sm btn-outline-danger" data-remove><i class="bi bi-trash me-1"></i>ลบรายการ</button></div></div>');enhanceRich(document);enhanceEmployees(document);}}
    var remove=e.target.closest('[data-remove]');if(remove)remove.closest('.jd-item').remove();
  });
  document.getElementById('jd-section-form').addEventListener('submit',function(){
    document.querySelectorAll('.jd-rte__area').forEach(function(a){a._source.value=(a.textContent.trim()||a.querySelector('li'))?a.innerHTML:'';});
    var output=document.getElementById('section-payload');if(!output)return;var payload={intro:document.querySelector('[data-intro]').value.trim(),items:[]};
    document.querySelectorAll('.jd-item').forEach(function(row){var item={},hasValue=false;row.querySelectorAll('[data-key]').forEach(function(input){item[input.dataset.key]=(input.value||'').trim();if(item[input.dataset.key])hasValue=true;if(input.dataset.key==='employee_id'){var option=input.options[input.selectedIndex];item.employee_name=option&&option.value?option.text.trim():'';}});if(hasValue)payload.items.push(item);});output.value=JSON.stringify(payload);
  });
  enhanceRich(document);enhanceEmployees(document);
})();
JS);
?>
