<?php
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
$this->title = $source ? 'ปรับชุดเวลา' : 'สร้างชุดเวลา';
?>
<?= $this->render('_header', ['tab'=>'schedules']) ?>
<section class="card border-0 shadow-sm attendance-settings">
    <div class="card-header bg-body d-flex flex-wrap align-items-center justify-content-between gap-2 py-3">
        <h2 class="h5 fw-semibold mb-0"><?= Html::encode($this->title) ?></h2>
        <?= Html::a('กลับรายการชุดเวลา', ['index','tab'=>'schedules'], ['class'=>'btn btn-outline-secondary btn-sm']) ?>
    </div>
    <div class="card-body">
        <?php if ($source): ?><div class="alert alert-info">แก้ไขจากชุด <?= Html::encode($source->name) ?> แล้วบันทึกเป็นชุดใหม่ จากนั้นเลือกกำหนดให้หน่วยงานหรือบุคลากร ประวัติที่ใช้ชุดเดิมจะยังคงอยู่</div><?php endif; ?>
        <?php $form = ActiveForm::begin(['id'=>'schedule-form']); ?>
        <?= $form->errorSummary($model) ?>
        <div class="row g-3">
            <div class="col-lg-6"><?= $form->field($model,'name')->textInput(['maxlength'=>150,'placeholder'=>'เช่น เวลาปกติ 08:00–16:00']) ?></div>
            <?php foreach (['start_time'=>'08:00','end_time'=>'16:00'] as $attribute=>$placeholder): ?>
            <div class="col-sm-6 col-lg-3"><?= $form->field($model,$attribute)->textInput(['placeholder'=>$placeholder,'maxlength'=>5,'pattern'=>'(?:[01][0-9]|2[0-3]):[0-5][0-9]','title'=>'เวลา 24 ชั่วโมง เช่น '.$placeholder,'autocomplete'=>'off'])->hint('เวลา 24 ชั่วโมง เช่น '.$placeholder) ?></div>
            <?php endforeach; ?>
        </div>
        <fieldset class="mb-4"><legend class="fs-6 fw-semibold">วันทำงาน</legend>
            <?= Html::checkboxList('WorkSchedule[weekdays]',explode(',',(string)$model->weekdays),[1=>'จันทร์',2=>'อังคาร',3=>'พุธ',4=>'พฤหัสบดี',5=>'ศุกร์',6=>'เสาร์',7=>'อาทิตย์'],['class'=>'d-flex flex-wrap gap-3','itemOptions'=>['class'=>'form-check-input me-1']]) ?>
            <?= Html::error($model,'weekdays',['class'=>'text-danger']) ?>
        </fieldset>
        <div class="row g-3">
            <div class="col-lg-6"><?= $form->field($model,'grace_minutes')->input('number',['min'=>0,'max'=>120])->hint('กำหนด 0 หากไม่มีการผ่อนผัน') ?></div>
            <div class="col-lg-6"><?= $form->field($model,'holidays')->textarea(['rows'=>3])->hint('ใช้วันหยุดส่วนกลางอัตโนมัติ เพิ่มเฉพาะวันหยุดของกลุ่มนี้ เป็นวันที่ ค.ศ. YYYY-MM-DD หนึ่งวันต่อบรรทัด') ?></div>
        </div>
        <details class="mb-4" <?= $model->hasErrors('window_minutes') ? 'open' : '' ?>>
            <summary class="fw-semibold mb-3">การจับคู่เวลาสแกน</summary>
            <?= $form->field($model,'window_minutes')->input('number',['min'=>1,'max'=>360])->hint('จับคู่กับจุดเริ่มหรือจบงานที่ใกล้ที่สุดภายในช่วงนี้ หากใกล้เท่ากันจะรอตรวจสอบ ค่าเริ่มต้น 240 นาที ไม่ใช่เกณฑ์ OT') ?>
        </details>
        <div class="d-grid d-sm-flex justify-content-sm-end gap-2 border-top pt-3">
            <?= Html::submitButton($source ? 'บันทึกเป็นชุดเวลาใหม่' : 'สร้างชุดเวลา',['class'=>'btn btn-primary']) ?>
            <?= Html::a('ยกเลิก',['index','tab'=>'schedules'],['class'=>'btn btn-outline-secondary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</section>
