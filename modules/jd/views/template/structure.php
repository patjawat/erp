<?php

use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use app\modules\jd\models\JdTemplateBlock;
use app\modules\jd\services\JdAiDraftService;
use kartik\select2\Select2Asset;

/** @var yii\web\View $this */
/** @var app\modules\jd\models\JdTemplate $model */

$this->title = 'สร้าง Template JD: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Template JD', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$sectionHelp = [
    'job_identification' => 'ข้อมูลชื่อ รหัส ระดับ สังกัด ผู้บังคับบัญชา และใบอนุญาต',
    'job_purpose' => 'สรุปเหตุผลของตำแหน่งและผลลัพธ์สำคัญที่องค์กรคาดหวัง',
    'scope_overview' => 'จัดกลุ่มภาพรวมงานเพื่อให้เข้าใจขอบเขตได้อย่างรวดเร็ว',
    'responsibilities' => 'แบ่งหมวดย่อยและระบุหน้าที่สำคัญของแต่ละหมวด',
    'kpi' => 'กำหนดตัวชี้วัด เป้าหมาย และวิธีติดตามผล',
    'qualifications' => 'การศึกษา ใบอนุญาต ประสบการณ์ ความรู้ และทักษะ',
    'competencies' => 'สมรรถนะสำคัญพร้อมพฤติกรรมหรือคำอธิบาย',
    'role_boundary' => 'แยกสิ่งที่ตำแหน่งรับผิดชอบกับสิ่งที่ต้องประสาน',
    'working_conditions' => 'สถานที่ เวลา PPE ความเสี่ยง ความก้าวหน้า และ CPD',
    'approval' => 'ระบุผู้จัดทำ ผู้ตรวจสอบ และผู้อนุมัติเอกสาร',
];
$completedSections = 0;
foreach ($model->blocks as $summaryBlock) {
    $summaryData = $summaryBlock->getData();
    if (trim((string)($summaryData['intro'] ?? '')) !== '' || !empty($summaryData['items'])) {
        $completedSections++;
    }
}
?>
<?php $this->beginBlock('page-title'); ?>
<div>
        <div class="d-flex align-items-center gap-2 flex-wrap"><h4 class="fw-semibold mb-1"><?= Html::encode($model->name) ?></h4><span class="badge bg-secondary-subtle text-secondary-emphasis">ขั้นตอนที่ 2 จาก 2</span></div>
    <div class="text-muted small">
        <?= Html::encode($model->getPositionTitle()) ?> · Revision <?= (int) $model->revision_no ?> · <?= Html::encode($model->lifecycle_status) ?>
    </div>
</div>
<?php $this->endBlock(); ?>

<style>
.jd-editor-page{--ink-1:var(--bs-emphasis-color);--ink-2:var(--bs-secondary-color);--ink-3:var(--bs-secondary-color);--surface:var(--bs-body-bg);--surface-2:var(--bs-tertiary-bg);--surface-hover:var(--bs-secondary-bg);--line:var(--bs-border-color-translucent);--line-strong:var(--bs-border-color);--primary:var(--bs-primary);--primary-soft:rgba(var(--bs-primary-rgb),.12);padding:1rem 1rem 5rem}.jd-toolbar{display:flex;flex-wrap:wrap;align-items:center;gap:.5rem;background:var(--surface);border:1px solid var(--line);border-radius:10px;padding:.75rem 1rem;margin-bottom:1rem;box-shadow:var(--bs-box-shadow-sm)}.jd-toolbar__hint{margin-left:auto;color:var(--ink-3);font-size:.78rem}.jd-editor-layout{display:grid;grid-template-columns:250px minmax(0,1fr);gap:1rem;align-items:start}.jd-editor-nav{position:sticky;top:1rem;background:var(--surface);border:1px solid var(--line);border-radius:10px;padding:.5rem;box-shadow:var(--bs-box-shadow-sm)}.jd-editor-nav a{display:flex;align-items:flex-start;gap:.55rem;padding:.6rem .65rem;color:var(--ink-2);text-decoration:none;border-radius:8px;font-size:.84rem;line-height:1.35}.jd-editor-nav a:hover,.jd-editor-nav a:focus-visible{background:var(--surface-hover);color:var(--ink-1)}.jd-section{background:var(--surface);border:1px solid var(--line);border-radius:10px;margin-bottom:1rem;box-shadow:var(--bs-box-shadow-sm);scroll-margin-top:1rem}.jd-section__head{padding:.9rem 1rem;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;gap:1rem;background:var(--surface-2);border-radius:10px 10px 0 0}.jd-section__body{padding:1rem}.jd-item:not(tr){border:1px solid var(--line-strong);border-radius:8px;padding:.8rem;margin-bottom:.7rem}.jd-item-grid{display:grid;gap:.7rem;grid-template-columns:repeat(auto-fit,minmax(220px,1fr))}.jd-label{display:block;font-size:.8rem;font-weight:600;color:var(--ink-2);margin-bottom:.35rem}.jd-input{width:100%;min-height:42px;border:1px solid var(--line-strong);border-radius:8px;padding:.55rem .65rem;color:var(--ink-1);background:var(--surface)}.jd-input:focus{border-color:var(--primary);outline:3px solid var(--primary-soft)}.jd-table-editor{width:100%;border-collapse:separate;border-spacing:0;border:1px solid var(--line);border-radius:8px;overflow:hidden}.jd-table-editor th{padding:.55rem .65rem;background:var(--surface-2);color:var(--ink-2);font-size:.8rem;font-weight:600;border-bottom:1px solid var(--line)}.jd-table-editor td{padding:.5rem;border-bottom:1px solid var(--line);vertical-align:top}.jd-table-editor tr:last-child td{border-bottom:0}.jd-table-editor__action{width:54px;text-align:center}.jd-rte{border:1px solid var(--line-strong);border-radius:8px;background:var(--surface);overflow:hidden}.jd-rte:focus-within{border-color:var(--primary);box-shadow:0 0 0 3px var(--primary-soft)}.jd-rte__toolbar{display:flex;gap:.2rem;padding:.35rem;border-bottom:1px solid var(--line);background:var(--surface-2)}.jd-rte__btn{width:34px;height:34px;border:0;border-radius:6px;background:transparent;color:var(--ink-2)}.jd-rte__btn:hover,.jd-rte__btn:focus-visible{background:var(--surface-hover);color:var(--ink-1)}.jd-rte__area{min-height:86px;padding:.65rem;outline:0;line-height:1.55}.jd-rte__area ul,.jd-rte__area ol{margin-bottom:.4rem}.jd-actions{position:sticky;bottom:0;background:var(--surface);border:1px solid var(--line);border-radius:10px;padding:.75rem 1rem;z-index:10;box-shadow:var(--bs-box-shadow)}@media(max-width:991.98px){.jd-editor-page{padding:.75rem .75rem 5rem}.jd-editor-layout{grid-template-columns:1fr}.jd-editor-nav{position:static;display:flex;overflow:auto}.jd-editor-nav a{min-width:180px}.jd-toolbar__hint{width:100%;margin-left:0}}@media(max-width:767.98px){.jd-table-editor,.jd-table-editor tbody,.jd-table-editor tr,.jd-table-editor td{display:block;width:100%}.jd-table-editor thead{display:none}.jd-table-editor tr{padding:.75rem;border-bottom:1px solid var(--line)}.jd-table-editor tr:last-child{border-bottom:0}.jd-table-editor td{padding:0 0 .65rem;border:0}.jd-table-editor td::before{content:attr(data-label);display:block;margin-bottom:.3rem;color:var(--ink-2);font-size:.8rem;font-weight:600}.jd-table-editor .jd-table-editor__action{width:100%;padding:0;text-align:end}.jd-table-editor .jd-table-editor__action::before{display:none}}@media(prefers-reduced-motion:reduce){*{scroll-behavior:auto!important;transition:none!important}}
</style>

<div class="jd-editor-page">
<div class="jd-toolbar">
    <?= Html::a('<i class="bi bi-arrow-left me-1"></i>คลัง Template', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    <?= Html::a('<i class="bi bi-pencil me-1"></i>ข้อมูล Template', ['update', 'id' => $model->id], ['class' => 'btn btn-outline-secondary']) ?>
    <?= Html::beginForm(['copy', 'id' => $model->id], 'post', ['class' => 'd-inline']) ?><?= Html::submitButton('<i class="bi bi-copy me-1"></i>คัดลอก', ['class' => 'btn btn-outline-secondary']) ?><?= Html::endForm() ?>
    <?= Html::beginForm(['new-revision', 'id' => $model->id], 'post', ['class' => 'd-inline']) ?><?= Html::submitButton('<i class="bi bi-file-earmark-plus me-1"></i>สร้าง JD', ['class' => 'btn btn-outline-secondary']) ?><?= Html::endForm() ?>
    <?php if (JdAiDraftService::isConfigured()): ?>
        <?= Html::beginForm(['ai-draft', 'id' => $model->id], 'post', ['class' => 'd-inline']) ?>
        <?= Html::submitButton('<i class="bi bi-stars me-1"></i>สร้างร่างด้วย AI', ['class' => 'btn btn-outline-primary', 'data-confirm' => 'AI จะเติมข้อมูลฉบับร่างลงในทั้ง 10 หมวด และอาจแทนข้อมูลที่กำลังมีอยู่ ต้องการดำเนินการหรือไม่?']) ?>
        <?= Html::endForm() ?>
        <span class="jd-toolbar__hint">AI เป็นผู้ช่วยร่าง ผู้รับผิดชอบต้องตรวจสอบก่อนประกาศใช้</span>
    <?php else: ?>
        <span class="badge text-bg-warning align-self-center">AI ยังไม่ได้ตั้งค่า API key</span>
    <?php endif; ?>
</div>

<div class="d-flex align-items-center justify-content-between gap-3 mb-3 p-3 bg-white border rounded-3">
    <div><strong>ความครบถ้วนของ Template</strong><div class="small text-muted">กรอกแล้ว <?= $completedSections ?> จาก <?= count($model->blocks) ?> หมวด</div></div>
    <div class="progress flex-grow-1" style="max-width:320px;height:8px" role="progressbar" aria-label="ความครบถ้วน" aria-valuenow="<?= $completedSections ?>" aria-valuemin="0" aria-valuemax="<?= count($model->blocks) ?>"><div class="progress-bar" style="width:<?= count($model->blocks) ? round($completedSections * 100 / count($model->blocks)) : 0 ?>%"></div></div>
</div>

<?= Html::beginForm(['structure', 'id' => $model->id], 'post', ['id' => 'jd-structure-form']) ?>
<div class="jd-editor-layout">
    <nav class="jd-editor-nav" aria-label="สารบัญ Template">
        <?php foreach ($model->blocks as $i => $block): ?>
            <?php $navData = $block->getData(); $isComplete = trim((string)($navData['intro'] ?? '')) !== '' || !empty($navData['items']); ?>
            <a href="#block-<?= Html::encode($block->section_code) ?>"><span class="<?= $isComplete ? 'text-success' : 'text-muted' ?>"><i class="bi <?= $isComplete ? 'bi-check-circle-fill' : 'bi-circle' ?>"></i></span><span><?= Html::encode(preg_replace('/^\d+\.\s*/', '', $block->title)) ?></span></a>
        <?php endforeach; ?>
    </nav>
    <main>
        <?php foreach ($model->blocks as $block):
            $editorType = JdTemplateBlock::editorType($block->block_type, $block->section_code);
            $isTabular = JdTemplateBlock::isTabularType($editorType);
            $columns = JdTemplateBlock::editorColumns($editorType);
            $data = $block->getData() + ['intro' => '', 'items' => []];
            $items = $data['items'] ?? [];
            if (($isTabular || $editorType === 'approval') && $items === []) {
                $items = [[]];
            }
            $structuredHelp = match ($editorType) {
                'kpi' => 'ตัวชี้วัด 1 รายการต่อ 1 แถว สามารถเพิ่มได้มากกว่าหนึ่งรายการ',
                'competency' => 'สมรรถนะ 1 รายการต่อ 1 แถว ระบุพฤติกรรมที่แสดงออกและระดับที่ต้องการ แล้วเพิ่มรายการได้ตามตำแหน่ง',
                default => 'เลือกผู้ลงนามจากทะเบียนบุคลากรเท่านั้น ระบบจะบันทึกรหัสบุคลากรไว้สำหรับการยืนยันอ่านและลงนามเอกสาร',
            };
        ?>
        <section class="jd-section" id="block-<?= Html::encode($block->section_code) ?>" data-block data-code="<?= Html::encode($block->section_code) ?>" data-columns='<?= Html::encode(Json::htmlEncode($columns)) ?>'>
            <header class="jd-section__head">
                <div><h5 class="fw-semibold mb-1"><?= Html::encode($block->title) ?></h5><div class="small text-muted"><?= Html::encode($sectionHelp[$block->section_code] ?? 'เพิ่ม แก้ไข หรือลบรายการตามความเหมาะสมของตำแหน่ง') ?></div></div>
                <button type="button" class="btn btn-sm btn-outline-primary" data-add><i class="bi bi-plus-lg me-1"></i><?= $editorType === 'approval' ? 'เพิ่มผู้ลงนาม' : ($editorType === 'competency' ? 'เพิ่มสมรรถนะ' : 'เพิ่มรายการ') ?></button>
            </header>
            <div class="jd-section__body">
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
                <div data-items><?php foreach ($items as $item): ?><div class="jd-item"><div class="jd-item-grid"><?php foreach ($columns as $key => $label): ?><label><span class="jd-label"><?= Html::encode($label) ?></span><?php if ($editorType === 'approval' && $key === 'role'): ?><?= Html::dropDownList('', $item[$key] ?? '', ['ผู้จัดทำ' => 'ผู้จัดทำ', 'ผู้ตรวจสอบ' => 'ผู้ตรวจสอบ', 'ผู้อนุมัติ' => 'ผู้อนุมัติ'], ['class' => 'jd-input', 'data-key' => $key]) ?><?php elseif ($editorType === 'approval' && $key === 'employee_id'): ?><select class="jd-input jd-employee-picker" data-key="employee_id"><?php if (!empty($item['employee_id'])): ?><option value="<?= (int) $item['employee_id'] ?>" selected><?= Html::encode($item['employee_name'] ?? ('พนักงาน #' . $item['employee_id'])) ?></option><?php endif; ?></select><?php else: ?><textarea class="jd-input" rows="2" data-key="<?= Html::encode($key) ?>" data-richtext><?= Html::encode($item[$key] ?? '') ?></textarea><?php endif; ?></label><?php endforeach; ?></div><div class="text-end mt-2"><button type="button" class="btn btn-sm btn-outline-danger" data-remove><i class="bi bi-trash me-1"></i>ลบรายการ</button></div></div><?php endforeach; ?></div>
                <?php endif; ?>
                <?= Html::hiddenInput('blocks[' . $block->section_code . ']', '', ['data-output' => true]) ?>
            </div>
        </section>
        <?php endforeach; ?>
    </main>
</div>
<div class="jd-actions d-flex justify-content-end gap-2">
    <?= Html::a('กลับคลัง Template', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    <?= Html::submitButton('<i class="bi bi-check-lg me-1"></i>บันทึกทั้ง Template', ['class' => 'btn btn-primary']) ?>
</div>
<?= Html::endForm() ?>
</div>

<?php
$employeeUrl = Json::htmlEncode(Url::to(['/hr/organization/list-employee']));
Select2Asset::register($this);
$this->registerJs(<<<JS
(function(){
  var employeeUrl = {$employeeUrl};
  var rteCommands=[
    ['bold','type-bold','ตัวหนา'],['italic','type-italic','ตัวเอียง'],['underline','type-underline','ขีดเส้นใต้'],
    ['insertUnorderedList','list-ul','รายการสัญลักษณ์'],['insertOrderedList','list-ol','รายการลำดับเลข'],['removeFormat','eraser','ล้างรูปแบบ']
  ];
  function richField(key){
    return '<textarea class="jd-input" rows="2" data-key="'+escapeHtml(key)+'" data-richtext></textarea>';
  }
  function itemHtml(columns, type){
    var fields='';
    Object.keys(columns).forEach(function(key){
      var control=(type==='kpi'||type==='competency')?'<input type="text" class="jd-input" data-key="'+escapeHtml(key)+'" placeholder="'+escapeHtml(columns[key])+'">':richField(key);
      if(type==='approval'&&key==='role') control='<select class="jd-input" data-key="role"><option>ผู้จัดทำ</option><option>ผู้ตรวจสอบ</option><option>ผู้อนุมัติ</option></select>';
      if(type==='approval'&&key==='employee_id') control='<select class="jd-input jd-employee-picker" data-key="employee_id"></select>';
      fields+='<label><span class="jd-label">'+escapeHtml(columns[key])+'</span>'+control+'</label>';
    });
    return '<div class="jd-item"><div class="jd-item-grid">'+fields+'</div><div class="text-end mt-2"><button type="button" class="btn btn-sm btn-outline-danger" data-remove><i class="bi bi-trash me-1"></i>ลบรายการ</button></div></div>';
  }
  function escapeHtml(v){var d=document.createElement('div');d.textContent=v||'';return d.innerHTML;}
  function seedHtml(value){return /<(?:p|br|ul|ol|li|strong|em|b|i|u)\\b/i.test(value)?value:escapeHtml(value).replace(/\\r?\\n/g,'<br>');}
  function enhanceRichText(scope){
    (scope||document).querySelectorAll('[data-richtext]:not([data-rte-ready])').forEach(function(textarea){
      textarea.dataset.rteReady='1';
      var wrap=document.createElement('div');wrap.className='jd-rte';
      var toolbar=document.createElement('div');toolbar.className='jd-rte__toolbar';toolbar.setAttribute('role','toolbar');toolbar.setAttribute('aria-label','จัดรูปแบบข้อความ');
      rteCommands.forEach(function(c){var b=document.createElement('button');b.type='button';b.className='jd-rte__btn';b.dataset.command=c[0];b.title=c[2];b.setAttribute('aria-label',c[2]);b.innerHTML='<i class="bi bi-'+c[1]+'"></i>';toolbar.appendChild(b);});
      var area=document.createElement('div');area.className='jd-rte__area';area.contentEditable='true';area.setAttribute('role','textbox');area.setAttribute('aria-multiline','true');area.innerHTML=seedHtml(textarea.value);
      area._source=textarea;textarea.classList.add('visually-hidden');textarea.after(wrap);wrap.append(toolbar,area);
    });
  }
  function syncRichText(){document.querySelectorAll('.jd-rte__area').forEach(function(area){area._source.value=area.textContent.trim()||area.querySelector('li')?area.innerHTML:'';});}
  function enhanceEmployees(scope){
    if(!window.jQuery||!jQuery.fn.select2)return;
    jQuery(scope||document).find('.jd-employee-picker:not(.select2-hidden-accessible)').select2({
      width:'100%',placeholder:'ค้นหาและเลือกผู้ลงนาม',allowClear:true,minimumInputLength:1,
      ajax:{url:employeeUrl,dataType:'json',delay:250,data:function(p){return {q:p.term};},processResults:function(d){return {results:d.items||[]};}}
    });
  }
  document.addEventListener('click',function(e){
    var rte=e.target.closest('.jd-rte__btn');
    if(rte){e.preventDefault();var area=rte.closest('.jd-rte').querySelector('.jd-rte__area');area.focus();document.execCommand(rte.dataset.command,false,null);return;}
    var add=e.target.closest('[data-add]');
    if(add){var block=add.closest('[data-block]');var columns=JSON.parse(block.dataset.columns||'{}');var code=block.dataset.code;var type=code==='approval'?'approval':(code==='kpi'?'kpi':(code==='competencies'?'competency':'content'));var tabular=!['job_purpose','approval'].includes(code);if(tabular){var cells='';Object.keys(columns).forEach(function(key){cells+='<td data-label="'+escapeHtml(columns[key])+'"><textarea class="jd-input" rows="2" data-key="'+escapeHtml(key)+'" placeholder="'+escapeHtml(columns[key])+'"></textarea></td>';});block.querySelector('[data-items]').insertAdjacentHTML('beforeend','<tr class="jd-item">'+cells+'<td class="jd-table-editor__action"><button type="button" class="btn btn-sm btn-outline-danger" data-remove aria-label="ลบแถว"><i class="bi bi-trash"></i></button></td></tr>');}else{block.querySelector('[data-items]').insertAdjacentHTML('beforeend',itemHtml(columns,type));enhanceRichText(block);enhanceEmployees(block);}}
    var remove=e.target.closest('[data-remove]');if(remove){remove.closest('.jd-item').remove();}
  });
  document.getElementById('jd-structure-form').addEventListener('submit',function(){
    syncRichText();
    document.querySelectorAll('[data-block]').forEach(function(block){
      var payload={intro:block.querySelector('[data-intro]').value.trim(),items:[]};
      block.querySelectorAll('.jd-item').forEach(function(row){var item={},hasValue=false;row.querySelectorAll('[data-key]').forEach(function(input){item[input.dataset.key]=(input.value||'').trim();if(item[input.dataset.key])hasValue=true;if(input.dataset.key==='employee_id'){var opt=input.options[input.selectedIndex];item.employee_name=opt&&opt.value?opt.text.trim():'';}});if(hasValue)payload.items.push(item);});
      block.querySelector('[data-output]').value=JSON.stringify(payload);
    });
  });
  enhanceRichText();enhanceEmployees();
})();
JS);
?>
