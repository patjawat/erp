<?php
use yii\helpers\Html;

/** @var app\modules\helpdesk2\models\Helpdesk $model */
/** @var string $titleText */
/** @var string $statusBadge */
/** @var string $priorityBadge */
/** @var string $slaBadgeHtml */

?>
<div class="card shadow-sm">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-start gap-3">
            <div>
                <h4 class="fw-bold mb-2 fs-4"><?= Html::encode($titleText) ?></h4>
                <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
                    <?= $statusBadge ?>
                    <?= $priorityBadge ?>
                    <?= $slaBadgeHtml ?>
                </div>
                <div class="text-muted small">
                    ศูนย์ควบคุมงานซ่อม: ตรวจสอบสถานะ, ปรับปรุงข้อมูล และสั่งงานลำดับถัดไป
                </div>
            </div>

            <div class="d-flex flex-column align-items-end gap-2">
                <div class="d-flex gap-2">
                    <?= Html::a(
                        '<i class="fa-solid fa-print me-1"></i>พิมพ์ใบส่งซ่อม',
                        ['/helpdesk/service/print-send-repair-pdf', 'id' => $model->id],
                        ['class' => 'btn btn-sm btn-outline-secondary', 'target' => '_blank']
                    ) ?>
                </div>
            </div>
        </div>
    </div>
</div>

