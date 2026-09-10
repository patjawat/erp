<?php
use yii\helpers\Html;
$this->title='กำหนดเวลาทำงาน';
$targetName=$scope==='employee' ? $target->fullname : $target->name;
?>
<h1 class="h4"><?= Html::encode($this->title.' — '.$targetName) ?></h1>
<p>ตั้งแต่วันที่มีผล ระบบใช้ข้อมูลใหม่ ประวัติเดิมยังอยู่ การกำหนดระดับหน่วยงานใช้เฉพาะพนักงานประเภทปกติ</p>
<?php if (!$schedules): ?><div class="alert alert-info">ยังไม่มีชุดเวลาปกติ กรุณาให้ผู้ดูแลสร้างชุดเวลาก่อน <?= \app\modules\attendance\services\WorkScheduleService::manager() ? Html::a('ตั้งค่าเวลาทำงาน',['index'],['class'=>'alert-link']) : '' ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger" role="alert"><?= Html::encode($error) ?></div><?php endif; ?>
<?= Html::beginForm() ?>
<div class="mb-3"><?= Html::label('รูปแบบการทำงาน','assignment-mode',['class'=>'form-label']) ?><?= Html::dropDownList('Assignment[mode]',$values['mode']??'normal',$scope==='employee'?['normal'=>'เวลาปกติรายบุคคล','shift'=>'ตามตารางเวร','inherit'=>'ใช้ประเภทบุคลากรและเวลาของหน่วยงาน']:['normal'=>'เวลาปกติของหน่วยงาน'],['id'=>'assignment-mode','class'=>'form-select']) ?></div>
<div class="mb-3"><?= Html::label('ชุดเวลาปกติ','assignment-schedule',['class'=>'form-label']) ?><?= Html::dropDownList('Assignment[schedule_id]',$values['schedule_id']??'', $schedules,['prompt'=>'เลือกชุดเวลา (ใช้เมื่อเป็นเวลาปกติ)','id'=>'assignment-schedule','class'=>'form-select']) ?></div>
<div class="mb-3"><?= Html::label('วันที่เริ่มมีผล','assignment-date',['class'=>'form-label']) ?><?= Html::input('date','Assignment[effective_from]',$values['effective_from']??substr(\app\modules\attendance\services\AttendanceService::now(),0,10),['id'=>'assignment-date','required'=>true,'class'=>'form-control']) ?></div>
<div class="mb-3"><?= Html::label('เหตุผลในการกำหนดหรือเปลี่ยนแปลง','assignment-reason',['class'=>'form-label']) ?><?= Html::textarea('Assignment[reason]',$values['reason']??'',['id'=>'assignment-reason','required'=>true,'maxlength'=>2000,'class'=>'form-control']) ?></div>
<?= Html::submitButton('บันทึกการกำหนดเวลา',['class'=>'btn btn-primary']) ?>
<?= Html::endForm() ?>
<h2 class="h5 mt-4">ประวัติการกำหนด</h2><div class="table-responsive"><table class="table"><thead><tr><th>เริ่มมีผล</th><th>รูปแบบ / ชุดเวลา</th><th>เหตุผล</th><th>บันทึกเมื่อ / ผู้ใช้</th></tr></thead><tbody>
<?php foreach ($history as $row): ?><tr><td><?= Html::encode($row['effective_from']) ?></td><td><?= Html::encode(($row['mode']==='normal'?'ปกติ':($row['mode']==='shift'?'ตามตารางเวร':'ตามหน่วยงาน')).' / '.($schedules[$row['schedule_id']]??'—')) ?></td><td><?= Html::encode($row['reason']) ?></td><td><?= Html::encode($row['created_at'].' / #'.$row['created_by']) ?></td></tr><?php endforeach; ?>
</tbody></table></div>
