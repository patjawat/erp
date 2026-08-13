<?php

use app\modules\roster\helpers\RosterAccess;
use yii\helpers\Html;
use yii\helpers\Url;

$active = $active ?? '';
?>
<div class="d-flex flex-wrap gap-2 align-items-center">
    <a href="<?= Url::to(['/roster/period/index']) ?>"
       class="btn <?= $active === 'period' ? 'btn-primary' : 'btn-outline-primary' ?>">
        <i class="bi bi-calendar3"></i> ทะเบียนรอบเวร
    </a>

    <?php if (RosterAccess::canSeeOverview()): ?>
        <a href="<?= Url::to(['/roster/overview/index']) ?>"
           class="btn <?= $active === 'overview' ? 'btn-primary' : 'btn-outline-primary' ?>">
            <i class="bi bi-clipboard-data"></i> ภาพรวม/ตรวจสอบ
            <?php if (!empty($pendingCount)): ?>
                <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis ms-1"><?= (int) $pendingCount ?></span>
            <?php endif; ?>
        </a>
    <?php endif; ?>

    <div class="dropdown">
        <button class="btn dropdown-toggle <?= $active === 'setting' ? 'btn-primary' : 'btn-outline-primary' ?>"
                type="button" id="rosterMenuSetting" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-gear"></i> การตั้งค่า
        </button>
        <ul class="dropdown-menu" aria-labelledby="rosterMenuSetting">
            <li><?= Html::a('<i class="bi bi-clock me-1"></i> เวลาเวร/จำนวนคน รายหน่วยงาน', ['/roster/setting/unit'], ['class' => 'dropdown-item']) ?></li>
            <li><?= Html::a('<i class="bi bi-shield-check me-1"></i> กฎการจัดเวร รายหน่วยงาน', ['/roster/setting/rule'], ['class' => 'dropdown-item']) ?></li>
            <?php if (RosterAccess::isGlobalViewer()): ?>
                <li><hr class="dropdown-divider"></li>
                <li><?= Html::a('<i class="bi bi-tags me-1"></i> ประเภทเวร (ช/บ/ด)', ['/roster/setting/shift-type'], ['class' => 'dropdown-item']) ?></li>
                <li><?= Html::a('<i class="bi bi-people me-1"></i> กำหนดเวร 8 (ใครขึ้นเวร)', ['/leave/work-shift/index'], ['class' => 'dropdown-item']) ?></li>
                <li><?= Html::a('<i class="bi bi-person-check me-1"></i> ผู้อนุมัติตารางเวร', ['/approve-v2/setting/levels', 'system' => 'roster'], ['class' => 'dropdown-item']) ?></li>
            <?php endif; ?>
        </ul>
    </div>
</div>
