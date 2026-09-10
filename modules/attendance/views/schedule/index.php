<?php
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
$this->title = 'ตั้งค่าเวลาทำงาน';
?>
<h1 class="h4"><?= Html::encode($this->title) ?></h1>
<?= $this->render('@app/modules/attendance/menu') ?>
<p class="mt-3">สร้างชุดเวลาแล้วกำหนดให้หน่วยงานหรือพนักงาน ชุดที่สร้างแล้วเก็บเป็นประวัติ หากเปลี่ยนเวลาให้สร้างชุดใหม่และกำหนดวันที่เริ่มมีผล</p>
<section class="my-4">
<h2 class="h5">ชุดเวลาปกติ</h2>
<?php if (!$schedules): ?><p class="text-body-secondary">ยังไม่มีชุดเวลา กรุณาสร้างด้านล่าง ระบบจะไม่สมมติเวลาทำงานให้</p><?php endif; ?>
<div class="table-responsive"><table class="table"><thead><tr><th>ชื่อ</th><th>เวลา</th><th>วันทำงาน</th><th>ผ่อนผันสาย</th></tr></thead><tbody>
<?php foreach ($schedules as $s): ?><tr><td><?= Html::encode($s->name) ?></td><td><?= Html::encode($s->start_time.'–'.$s->end_time) ?></td><td><?= Html::encode($s->weekdayLabel) ?></td><td><?= (int)$s->grace_minutes ?> นาที</td></tr><?php endforeach; ?>
</tbody></table></div>
</section>
<section class="my-4"><h2 class="h5">กำหนดชุดเวลาให้หน่วยงานหรือพนักงาน</h2>
<?= Html::beginForm(['choose'], 'get', ['class'=>'row g-3']) ?>
<div class="col-md-9"><?= Html::label('ค้นหาหน่วยงานหรือพนักงาน','schedule-target',['class'=>'form-label']) ?><?= \kartik\select2\Select2::widget(['name'=>'target','data'=>$targets,'options'=>['id'=>'schedule-target','placeholder'=>'พิมพ์ชื่อเพื่อค้นหา','required'=>true]]) ?></div>
<div class="col-md-3 align-self-center"><?= Html::submitButton('เปิดหน้ากำหนดเวลา',['class'=>'btn btn-outline-primary']) ?></div>
<?= Html::endForm() ?></section>
<section class="my-4"><h2 class="h5">สร้างชุดเวลาใหม่</h2>
<?php $form=ActiveForm::begin(); ?>
<?= $form->errorSummary($model) ?>
<div class="row"><div class="col-md-6"><?= $form->field($model,'name') ?></div><div class="col-md-3"><?= $form->field($model,'start_time')->input('time') ?></div><div class="col-md-3"><?= $form->field($model,'end_time')->input('time') ?></div></div>
<fieldset class="mb-3"><legend class="fs-6">วันทำงาน</legend><?= Html::checkboxList('WorkSchedule[weekdays]',explode(',',(string)$model->weekdays),[1=>'จันทร์',2=>'อังคาร',3=>'พุธ',4=>'พฤหัสบดี',5=>'ศุกร์',6=>'เสาร์',7=>'อาทิตย์'],['class'=>'d-flex flex-wrap gap-3']) ?><?= Html::error($model,'weekdays',['class'=>'text-danger']) ?></fieldset>
<?= $form->field($model,'holidays')->textarea(['rows'=>4])->hint('ระบบใช้วันหยุดจากปฏิทินส่วนกลางด้วย ช่องนี้สำหรับวันหยุดเฉพาะกลุ่มเพิ่มเติม ระบุวันที่ ค.ศ. YYYY-MM-DD หนึ่งวันต่อบรรทัด') ?>
<div class="row"><div class="col-md-6"><?= $form->field($model,'grace_minutes')->input('number',['min'=>0,'max'=>120]) ?></div><div class="col-md-6"><?= $form->field($model,'window_minutes')->input('number',['min'=>1,'max'=>360])->hint('จับคู่กับเวลาเริ่มหรือจบที่ใกล้ที่สุด หากเท่ากันจะรอตรวจสอบ ค่าเริ่มต้น 240 นาที ยังไม่ใช่เกณฑ์ OT') ?></div></div>
<?= Html::submitButton('สร้างชุดเวลา',['class'=>'btn btn-primary']) ?>
<?php ActiveForm::end(); ?></section>
