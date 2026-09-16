<?php
use yii\helpers\Html;
$this->params['breadcrumbs'][] = ['label'=>'ระบบลงเวลา','url'=>['/attendance/default/index']];
$this->params['breadcrumbs'][] = ['label'=>'ตั้งค่าเวลาทำงาน','url'=>['/attendance/schedule/index']];
?>
<?php $this->beginBlock('page-title'); ?>
<h4 class="fw-semibold mb-1 text-body"><i class="bi bi-calendar-week me-2" aria-hidden="true"></i>ตั้งค่าเวลาทำงาน</h4>
<p class="small text-body-secondary mb-0">กำหนดเวลาปกติให้หน่วยงานหรือบุคลากร และเก็บประวัติการเปลี่ยนแปลง</p>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/attendance/menu', ['active'=>'schedule']) ?>
<?php $this->endBlock(); ?>
<?php if (\app\modules\attendance\services\WorkScheduleService::manager()): ?>
<nav class="nav nav-tabs mb-4" aria-label="การตั้งค่าเวลาทำงาน">
<?php foreach (['departments'=>'หน่วยงาน','employees'=>'รายบุคคล','schedules'=>'ชุดเวลา'] as $key=>$label): ?>
    <?= Html::a($label, ['index','tab'=>$key], ['class'=>'nav-link'.(($tab ?? '') === $key ? ' active' : ''),'aria-current'=>($tab ?? '') === $key ? 'page' : null]) ?>
<?php endforeach; ?>
</nav>
<?php endif; ?>
