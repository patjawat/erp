<?php
use yii\helpers\Url;
use yii\helpers\Html;
?>
<div class="d-flex flex-wrap gap-2 align-items-center">
    <a href="<?= Url::to(['/leave/default/dashboard']) ?>" class="btn <?= ($active ?? '') === 'dashboard' ? 'btn-primary' : 'btn-outline-primary' ?>">
        <i class="bi bi-calendar-check"></i> ภาพรวม
    </a>
    <a href="<?= Url::to(['/leave/default/index']) ?>" class="btn <?= ($active ?? '') === 'index' ? 'btn-primary' : 'btn-outline-primary' ?>">
        <i class="bi bi-list-ul"></i> ขอลา / รายการของฉัน
    </a>
    <a href="<?= Url::to(['/leave/calendar/index']) ?>" class="btn <?= ($active ?? '') === 'calendar' ? 'btn-primary' : 'btn-outline-primary' ?>">
        <i class="bi bi-calendar3"></i> ปฏิทินการลา
    </a>
    <?php if (Yii::$app->user->can('leave')): ?>
    <a href="<?= Url::to(['/leave/approver/index']) ?>" class="btn <?= ($active ?? '') === 'approver' ? 'btn-primary' : 'btn-outline-primary' ?>">
        <i class="bi bi-person-check"></i> ผู้ตรวจสอบวันลา
    </a>
    <a href="<?= Url::to(['/leave/report/index']) ?>" class="btn <?= ($active ?? '') === 'report' ? 'btn-primary' : 'btn-outline-primary' ?>">
        <i class="bi bi-pie-chart"></i> รายงานการลา
    </a>
    <div class="dropdown">
        <button class="btn <?= ($active ?? '') === 'setting' ? 'btn-primary' : 'btn-outline-primary' ?> dropdown-toggle" type="button" id="leaveMenuSetting" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-gear"></i> การตั้งค่า
        </button>
        <ul class="dropdown-menu" aria-labelledby="leaveMenuSetting">
            <li><?= Html::a('<i class="bi bi-caret-right me-1"></i> ประเภทการลา', ['/leave/leave-type/index'], ['class' => 'dropdown-item']) ?></li>
            <li><?= Html::a('<i class="bi bi-caret-right me-1"></i> นโยบายการลา', ['/leave/leave-policies/index'], ['class' => 'dropdown-item']) ?></li>
            <li><?= Html::a('<i class="bi bi-caret-right me-1"></i> กำหนดสิทธิลาพักผ่อน', ['/leave/leave-entitlements/index'], ['class' => 'dropdown-item']) ?></li>
            <li><?= Html::a('<i class="bi bi-caret-right me-1"></i> กำหนดเวร 8', ['/leave/work-shift/index'], ['class' => 'dropdown-item']) ?></li>
            <li><?= Html::a('<i class="bi bi-caret-right me-1"></i> วันหยุด', ['/leave/holiday/index'], ['class' => 'dropdown-item']) ?></li>
            <li><?= Html::a('<i class="bi bi-caret-right me-1"></i> ผู้อนุมัติใบลา', ['/leave/setting/approvers'], ['class' => 'dropdown-item']) ?></li>
            <li><?= Html::a('<i class="bi bi-caret-right me-1"></i> แบบฟอร์มใบลา', ['/leave/setting/leave-template'], ['class' => 'dropdown-item']) ?></li>
        </ul>
    </div>
    <?php endif; ?>
</div>
