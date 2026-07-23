<?php
use yii\helpers\Html;
use yii\helpers\Url;
?>
<div class="d-flex gap-2">
    <a href="<?= Url::to(['/attendance/default/index']) ?>" class="btn <?= ($active ?? '') !== 'checkin' ? 'btn-outline-primary' : 'btn-primary' ?>">
        <i class="bi bi-clock-history"></i> ลงเวลา
    </a>
    <a href="<?= Url::to(['/attendance/checkin/index']) ?>" class="btn <?= ($active ?? '') !== 'history' ? 'btn-outline-primary' : 'btn-primary' ?>">
        <i class="bi bi-list-ul"></i> ประวัติของฉัน
    </a>
    <?php if (Yii::$app->user->can('admin') || Yii::$app->user->can('hr')): ?>
    <a href="<?= Url::to(['/attendance/checkin/report']) ?>" class="btn <?= ($active ?? '') !== 'report' ? 'btn-outline-primary' : 'btn-primary' ?>">
        <i class="bi bi-people"></i> ทั้งหน่วยงาน
    </a>
    <a href="<?= Url::to(['/attendance/checkin/monthly']) ?>" class="btn <?= ($active ?? '') !== 'monthly' ? 'btn-outline-primary' : 'btn-primary' ?>">
        <i class="bi bi-calendar3"></i> สรุปรายเดือน
    </a>
    <a href="<?= Url::to(['/attendance/location/index']) ?>" class="btn btn-outline-primary">
        <i class="bi bi-geo-alt"></i> จุดลงเวลา
    </a>
    <a href="<?= Url::to(['/attendance/checkin/import-form']) ?>" class="btn btn-outline-primary">
        <i class="bi bi-upload"></i> นำเข้า CSV
    </a>
    <?php endif; ?>
</div>
