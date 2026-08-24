<?php
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\select2\Select2;
$this->title='สร้าง Service Profile';
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?><?= Html::a('<i class="bi bi-arrow-left me-1"></i> กลับไปรายการ',['index'],['class'=>'btn btn-outline-secondary']) ?><?php $this->endBlock(); ?>
<div class="row justify-content-center"><div class="col-12 col-xl-9"><div class="card bg-body border shadow-sm"><div class="card-header bg-body-tertiary py-3"><h2 class="h5 fw-semibold mb-1">ข้อมูลฉบับใหม่</h2><p class="small text-body-secondary mb-0">ระบบใช้ Template ที่ประกาศใช้ล่าสุดของหน่วยงาน</p></div><div class="card-body p-3 p-md-4">
<?php $form=ActiveForm::begin(['id'=>'sp-create-form']); ?><div class="row g-3">
<div class="col-12 col-md-8"><?= $form->field($model,'owner_id')->widget(Select2::class,['data'=>$ownerOptions,'options'=>['placeholder'=>'ค้นหาหน่วยงาน ทีมประสาน หรือตัวย่อ','id'=>'sp-create-owner'],'pluginOptions'=>['allowClear'=>true]])->hint('ข้อมูลจากทะเบียนตั้งค่าหน่วยงานของปีงบประมาณ รวมหน่วยงานในโครงสร้างและทีมประสาน') ?></div><div class="col-12 col-md-4"><?= $form->field($model,'fiscal_year')->textInput(['type'=>'number','min'=>2500,'max'=>2700,'id'=>'sp-create-year']) ?></div>
<div class="col-12 col-md-6"><?= $form->field($model,'coordinator_id')->widget(Select2::class,['data'=>$employeeOptions,'options'=>['placeholder'=>'ค้นหาและเลือกผู้ประสานหลัก','id'=>'sp-create-coordinator'],'pluginOptions'=>['allowClear'=>true]]) ?></div><div class="col-12 col-md-6"><?= $form->field($model,'author_ids')->widget(Select2::class,['data'=>$employeeOptions,'options'=>['multiple'=>true,'placeholder'=>'ค้นหาและเลือกรายชื่อคณะทำงาน','id'=>'sp-create-authors'],'pluginOptions'=>['allowClear'=>true,'closeOnSelect'=>false]])->hint('เลือกจากบุคลากรในหน่วยงาน สมาชิกคณะกรรมการ/ทีมประสาน หรือกำหนดบุคลากรอื่นเพิ่มได้') ?></div>
<div class="col-12"><?= $form->field($model,'copy_latest')->checkbox(['label'=>'คัดลอกข้อมูลจากฉบับประกาศใช้ล่าสุด ถ้ามี']) ?></div>
</div><div class="d-flex flex-wrap justify-content-end gap-2 mt-4 pt-3 border-top"><?= Html::a('ยกเลิก',['index'],['class'=>'btn btn-outline-secondary']) ?><?= Html::submitButton('<i class="bi bi-file-earmark-plus me-1"></i> สร้างฉบับร่าง',['class'=>'btn btn-primary']) ?></div><?php ActiveForm::end(); ?>
</div></div></div></div>
<?php
$employeeUrl=Url::to(['employees']);
$ownerUrl=Url::to(['owners']);
$this->registerJs(<<<JS
function spReplaceGroupedOptions(select, groups) {
  select.innerHTML='';
  Object.keys(groups).forEach(function(label){var optgroup=document.createElement('optgroup');optgroup.label=label;Object.keys(groups[label]).forEach(function(key){optgroup.appendChild(new Option(groups[label][key],key));});select.appendChild(optgroup);});
  window.jQuery&&window.jQuery(select).val(null).trigger('change');
}
document.getElementById('sp-create-owner')?.addEventListener('change', async function(){
  var year=document.getElementById('sp-create-year')?.value||'';
  var response=await fetch('{$employeeUrl}?owner_id='+encodeURIComponent(this.value)+'&fiscal_year='+encodeURIComponent(year),{headers:{'X-Requested-With':'XMLHttpRequest'}}); var groups=await response.json();
  ['sp-create-coordinator','sp-create-authors'].forEach(function(id){var select=document.getElementById(id);if(select)spReplaceGroupedOptions(select,groups);});
});
document.getElementById('sp-create-year')?.addEventListener('change',async function(){
  var response=await fetch('{$ownerUrl}?fiscal_year='+encodeURIComponent(this.value),{headers:{'X-Requested-With':'XMLHttpRequest'}});var groups=await response.json();
  var owner=document.getElementById('sp-create-owner');if(owner)spReplaceGroupedOptions(owner,groups);
});
JS);
?>
