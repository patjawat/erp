<?php
use yii\helpers\Html;
$this->title = 'ลงเวลาเข้า-ออก';
$this->params['breadcrumbs'][] = ['label' => 'ลงเวลา', 'url' => ['/attendance/default/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/attendance/menu', ['active' => 'checkin']) ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-title'); ?>
<h4 class="fw-semibold text-body mb-1"><?= Html::encode($this->title) ?></h4>
<p class="text-body-secondary mb-0">ลงเวลาตามตารางเวร พร้อมตรวจ GPS และส่งอนุมัติ</p>
<?php $this->endBlock(); ?>
<div class="row justify-content-center"><div class="col-12 col-lg-8 col-xl-6">
    <div class="card border-0 shadow-sm"><div class="card-body p-3 p-md-4">
        <?= $this->render('_clock_form') ?>
    </div></div>
</div></div>
