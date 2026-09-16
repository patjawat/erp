<?php
use yii\helpers\Html;
use app\modules\attendance\services\WorkScheduleService;
if (!$model->id || !WorkScheduleService::ready()) return;
$resolved=WorkScheduleService::resolve($model->getAttributes(),substr(\app\modules\attendance\services\AttendanceService::now(),0,10));
$schedule=$resolved['schedule'];
?>
<section class="my-3 p-3 border rounded-3 bg-body">
<h2 class="h6">รูปแบบและเวลาทำงาน</h2>
<p class="mb-1"><?= Html::encode($resolved['mode']==='shift'?'ตามตารางเวร':($resolved['mode']==='normal'?'เวลางานปกติ':'ยังไม่กำหนดประเภทการทำงาน')) ?></p>
<p class="text-body-secondary"><?= Html::encode($schedule ? $schedule->name.' '.$schedule->start_time.'–'.$schedule->end_time.' · กำหนดจาก'.$resolved['source'].' · มีผล '.$resolved['assignment']['effective_from'] : 'ยังไม่มีเวลาปกติที่มีผล — ระบบใช้ตารางเวรที่ประกาศ หรือเก็บเวลาสแกนรอตรวจสอบ') ?></p>
<?php if (WorkScheduleService::canAssign('employee',(int)$model->id)): ?>
<?= Html::a('กำหนดเวลาทำงาน',['/attendance/schedule/assign','scope'=>'employee','id'=>$model->id],['class'=>'btn btn-outline-primary btn-sm','data-pjax'=>0]) ?>
<?php endif; ?>
<?php if ($model->department && WorkScheduleService::canAssign('department',(int)$model->department)): ?>
<?= Html::a('กำหนดเวลาของหน่วยงาน',['/attendance/schedule/assign','scope'=>'department','id'=>$model->department],['class'=>'btn btn-outline-secondary btn-sm','data-pjax'=>0]) ?>
<?php endif; ?>
</section>
