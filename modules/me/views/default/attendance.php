<?php
use yii\helpers\Html;
use yii\helpers\Url;
$todayCount = isset($todayCheckinCount) ? (int)$todayCheckinCount : 0;
?>
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <div class="d-flex align-items-center gap-2 mb-3">
            <div class="bg-primary-subtle text-primary rounded-4 d-flex align-items-center justify-content-center shadow-sm" style="width: 42px; height: 42px;">
                <i class="fa-solid fa-stopwatch"></i>
            </div>
            <div>
                <h6 class="fw-black text-dark mb-0" style="font-size: 1rem;">บันทึกเวลาเข้างาน</h6>
                <p class="text-muted mb-0 small">ลงเวลาแล้ว <?= $todayCount ?> ครั้งวันนี้</p>
            </div>
        </div>
        <div class="d-flex flex-column gap-2">
            <?= Html::a('<i class="fa-solid fa-clock me-2"></i> ลงเวลา / ประวัติ', Url::to(['/attendance/default/index']), ['class' => 'btn btn-primary rounded-4 fw-bold py-2']) ?>
        </div>
    </div>
</div>