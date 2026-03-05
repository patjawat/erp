<?php
use yii\helpers\Html;
use yii\helpers\Url;

/** @var app\modules\hr\models\Employees $model */
/** @var string $name */
?>
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-primary text-white py-2 px-3">
        <h6 class="mb-0 small fw-normal">คำอธิบายงาน (Job Description)</h6>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">
            โหลด template ตามตำแหน่งงานปัจจุบัน แล้วแก้ไขหรือเพิ่มหัวข้อได้ตามความเหมาะสม
        </p>
        <?= Html::a(
            '<i class="bi bi-file-earmark-text me-1"></i> ดู/แก้ไข คำอธิบายงาน (JD)',
            ['/jd/employee-jd/view', 'emp_id' => $model->id],
            ['class' => 'btn btn-primary']
        ) ?>
    </div>
</div>
