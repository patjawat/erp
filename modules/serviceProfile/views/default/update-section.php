<?php
use app\modules\serviceProfile\services\SectionDefinitionService;
use kartik\editors\Summernote;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Json;

$this->title = 'แก้ไขหัวข้อ';
$columns = SectionDefinitionService::columns($model->block_type);
$items = (array) ($model->getData()['items'] ?? []);
if ($columns && !$items) $items = [[]];
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?><?= Html::encode($model->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= Html::a('<i class="bi bi-arrow-left me-1"></i> กลับไปเอกสาร', ['view', 'id' => $model->service_profile_id, '#' => 'section-' . $model->id], ['class' => 'btn btn-outline-secondary']) ?>
<?php $this->endBlock(); ?>

<div class="card bg-body border shadow-sm">
<div class="card-body p-3 p-md-4">
<?php $form = ActiveForm::begin(['id' => 'sp-section-editor']); ?>
<?= $form->field($model, 'content')->widget(Summernote::class, [
    'useKrajeePresets' => false,
    'options' => ['id' => 'sp-section-content'],
    'pluginOptions' => [
        'height' => 300, 'minHeight' => 220, 'maxHeight' => 700,
        'disableDragAndDrop' => true,
        'toolbar' => [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'clear']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['insert', ['table']],
            ['history', ['undo', 'redo']],
        ],
    ],
])->label('เนื้อหา') ?>
<div class="form-text mb-2">ใช้เมนูรูปแบบสำหรับหัวข้อ ใช้ปุ่มรายการสำหรับ Bullet/ลำดับเลข และปุ่มตารางเพื่อแทรกตาราง</div>
<div class="border rounded-3 p-3 mb-4" id="sp-table-tools">
    <div class="d-flex flex-wrap align-items-center gap-2">
        <strong class="small me-1"><i class="bi bi-table me-1"></i> จัดการตาราง</strong>
        <button type="button" class="btn btn-sm btn-outline-primary" data-table-action="add-row" disabled><i class="bi bi-plus-lg me-1"></i> เพิ่มแถว</button>
        <button type="button" class="btn btn-sm btn-outline-primary" data-table-action="add-column" disabled><i class="bi bi-plus-lg me-1"></i> เพิ่มคอลัมน์</button>
        <button type="button" class="btn btn-sm btn-outline-danger" data-table-action="remove-row" disabled><i class="bi bi-dash-lg me-1"></i> ลบแถว</button>
        <button type="button" class="btn btn-sm btn-outline-danger" data-table-action="remove-column" disabled><i class="bi bi-dash-lg me-1"></i> ลบคอลัมน์</button>
    </div>
    <div id="sp-table-status" class="form-text mt-2" role="status" aria-live="polite">คลิกภายในช่องตารางที่ต้องการแก้ไขก่อน</div>
</div>

<?php if ($columns): ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div><h2 class="h6 fw-semibold mb-1">รายการข้อมูล</h2><p class="small text-body-secondary mb-0">เพิ่ม แก้ไข หรือลบรายการ แล้วกดบันทึกหัวข้อ</p></div>
    <button type="button" class="btn btn-sm btn-outline-primary" id="sp-add-row"><i class="bi bi-plus-lg me-1"></i> เพิ่มรายการ</button>
</div>
<div id="sp-rows" class="d-flex flex-column gap-3">
<?php foreach ($items as $index => $item): ?>
<div class="border rounded-3 p-3 sp-data-row">
    <input type="hidden" data-process-ref value="<?= Html::encode((string) ($item['_process_ref'] ?? '')) ?>">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <strong class="small sp-row-number">รายการที่ <?= $index + 1 ?></strong>
        <button type="button" class="btn btn-sm btn-outline-danger sp-remove-row" aria-label="ลบรายการที่ <?= $index + 1 ?>"><i class="bi bi-trash me-1"></i> ลบ</button>
    </div>
    <div class="row g-3">
    <?php foreach ($columns as $key => $label): ?>
        <div class="col-12 <?= count($columns) > 2 ? 'col-lg-6' : '' ?>">
            <label class="form-label small fw-semibold"><?= Html::encode($label) ?></label>
            <textarea class="form-control" rows="3" data-key="<?= Html::encode($key) ?>"><?= Html::encode(strip_tags((string) ($item[$key] ?? ''))) ?></textarea>
        </div>
    <?php endforeach; ?>
    </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?= Html::hiddenInput('section_payload', '', ['id' => 'sp-section-payload']) ?>
<div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
    <?= Html::a('ยกเลิก', ['view', 'id' => $model->service_profile_id], ['class' => 'btn btn-outline-secondary']) ?>
    <?= Html::submitButton('<i class="bi bi-check-lg me-1"></i> บันทึกหัวข้อ', ['class' => 'btn btn-primary', 'id' => 'sp-save-section']) ?>
</div>
<?php ActiveForm::end(); ?>
</div>
</div>

<?php
$columnsJson = Json::htmlEncode($columns);
$this->registerJs(<<<JS
(function(){
var columns={$columnsJson},rows=document.getElementById('sp-rows'),form=document.getElementById('sp-section-editor'),activeCell=null;
function esc(v){var n=document.createElement('div');n.textContent=v==null?'':String(v);return n.innerHTML;}
function renumber(){if(!rows)return;rows.querySelectorAll('.sp-data-row').forEach(function(row,i){row.querySelector('.sp-row-number').textContent='รายการที่ '+(i+1);row.querySelector('.sp-remove-row').setAttribute('aria-label','ลบรายการที่ '+(i+1));});}
function addRow(){if(!rows)return;var fields='';Object.keys(columns).forEach(function(k){fields+='<div class="col-12 '+(Object.keys(columns).length>2?'col-lg-6':'')+'"><label class="form-label small fw-semibold">'+esc(columns[k])+'</label><textarea class="form-control" rows="3" data-key="'+esc(k)+'"></textarea></div>';});rows.insertAdjacentHTML('beforeend','<div class="border rounded-3 p-3 sp-data-row"><input type="hidden" data-process-ref value=""><div class="d-flex justify-content-between align-items-center mb-3"><strong class="small sp-row-number"></strong><button type="button" class="btn btn-sm btn-outline-danger sp-remove-row"><i class="bi bi-trash me-1"></i> ลบ</button></div><div class="row g-3">'+fields+'</div></div>');renumber();rows.lastElementChild.querySelector('textarea')?.focus();}
document.getElementById('sp-add-row')?.addEventListener('click',addRow);
rows?.addEventListener('click',function(e){var b=e.target.closest('.sp-remove-row');if(!b)return;b.closest('.sp-data-row').remove();renumber();});
function tableButtons(enabled){document.querySelectorAll('[data-table-action]').forEach(function(button){button.disabled=!enabled;});}
function selectCell(cell){activeCell=cell;tableButtons(true);var row=cell.parentElement;var table=row.closest('table');var rowNo=Array.prototype.indexOf.call(table.rows,row)+1;var colNo=cell.cellIndex+1;document.getElementById('sp-table-status').textContent='เลือกแถวที่ '+rowNo+' คอลัมน์ที่ '+colNo+' แล้ว';}
document.addEventListener('click',function(e){var editable=e.target.closest('.note-editable');if(!editable)return;var cell=e.target.closest('td,th');if(cell&&editable.contains(cell)){selectCell(cell);return;}if(!e.target.closest('#sp-table-tools')){activeCell=null;tableButtons(false);document.getElementById('sp-table-status').textContent='คลิกภายในช่องตารางที่ต้องการแก้ไขก่อน';}},true);
document.getElementById('sp-table-tools').addEventListener('click',function(e){var button=e.target.closest('[data-table-action]');if(!button||button.disabled||!activeCell||!activeCell.isConnected)return;var action=button.dataset.tableAction,row=activeCell.parentElement,table=row.closest('table'),index=activeCell.cellIndex;
if(action==='add-row'){var newRow=table.insertRow(row.rowIndex+1);for(var i=0;i<row.cells.length;i++){var cell=newRow.insertCell(-1);cell.innerHTML='<br>';};selectCell(newRow.cells[Math.min(index,newRow.cells.length-1)]);}
if(action==='remove-row'){if(table.rows.length<=1){table.remove();activeCell=null;tableButtons(false);document.getElementById('sp-table-status').textContent='ลบตารางแล้ว';}else{var next=table.rows[Math.max(0,row.rowIndex-1)];row.remove();selectCell(next.cells[Math.min(index,next.cells.length-1)]);}}
if(action==='add-column'){Array.prototype.forEach.call(table.rows,function(currentRow){var tag=currentRow.parentElement.tagName==='THEAD'?'th':'td';var cell=document.createElement(tag);cell.innerHTML='<br>';var reference=currentRow.cells[index+1]||null;currentRow.insertBefore(cell,reference);});selectCell(row.cells[index+1]);}
if(action==='remove-column'){var maxCells=0;Array.prototype.forEach.call(table.rows,function(currentRow){maxCells=Math.max(maxCells,currentRow.cells.length);});if(maxCells<=1){table.remove();activeCell=null;tableButtons(false);document.getElementById('sp-table-status').textContent='ลบตารางแล้ว';}else{Array.prototype.forEach.call(table.rows,function(currentRow){if(currentRow.cells[index])currentRow.deleteCell(index);});var target=row.cells[Math.max(0,index-1)];if(target)selectCell(target);}}
});
form.addEventListener('submit',function(){var items=[];rows?.querySelectorAll('.sp-data-row').forEach(function(row){var item={};row.querySelectorAll('[data-key]').forEach(function(input){item[input.dataset.key]=input.value.trim();});var ref=row.querySelector('[data-process-ref]')?.value||'';if(ref)item._process_ref=ref;items.push(item);});document.getElementById('sp-section-payload').value=JSON.stringify({items:items});document.getElementById('sp-save-section').disabled=true;});
renumber();
})();
JS);
?>
