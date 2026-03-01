<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var array $summaryByStatus [['status' => code, 'cnt' => n], ...] */
/** @var array $summaryByLeaveType [['leave_type_id' => code, 'cnt' => n], ...] */
/** @var array $listLeaveStatus code => title */
/** @var array $listLeaveType code => title */
/** @var array $leaveTypeColors code => Bootstrap color (จาก data_json.color) */

$statusColorMap = [
    'Pending' => 'warning',
    'Approve' => 'success',
    'Reject' => 'danger',
    'Cancel' => 'secondary',
    'ReqCancel' => 'info',
];
$defaultStatusColor = 'primary';
$defaultTypeColor = 'info'; // fallback เมื่อไม่มี leaveTypeColors

if (empty($summaryByStatus) && empty($summaryByLeaveType)) {
    return;
}
?>
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body py-3 px-4">
        <div class="row g-3 align-items-start">
            <?php if (!empty($summaryByStatus)): ?>
            <div class="col-12 col-md-auto">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="small text-muted me-1">ตามสถานะ:</span>
                    <?php foreach ($summaryByStatus as $row): ?>
                        <?php
                        $code = $row['status'] ?? '';
                        $cnt = (int) ($row['cnt'] ?? 0);
                        $label = $listLeaveStatus[$code] ?? $code ?: '-';
                        $color = $statusColorMap[$code] ?? $defaultStatusColor;
                        $url = Url::current(array_merge(Yii::$app->request->queryParams, ['LeaveSearch' => array_merge(Yii::$app->request->get('LeaveSearch', []), ['status' => $code])]));
                        ?>
                        <a href="<?= Html::encode($url) ?>" class="text-decoration-none">
                            <span class="badge bg-<?= $color ?> bg-opacity-10 text-<?= $color ?> border border-<?= $color ?>-subtle rounded-pill fw-medium px-2 py-1">
                                <?= Html::encode($label) ?> <strong><?= number_format($cnt) ?></strong>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            <?php if (!empty($summaryByLeaveType)): ?>
            <div class="col-12 col-md-auto">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="small text-muted me-1">ตามประเภทการลา:</span>
                    <?php foreach ($summaryByLeaveType as $row): ?>
                        <?php
                        $code = $row['leave_type_id'] ?? '';
                        $cnt = (int) ($row['cnt'] ?? 0);
                        $label = $listLeaveType[$code] ?? $code ?: '-';
                        $color = isset($leaveTypeColors[$code]) ? $leaveTypeColors[$code] : $defaultTypeColor;
                        $url = Url::current(array_merge(Yii::$app->request->queryParams, ['LeaveSearch' => array_merge(Yii::$app->request->get('LeaveSearch', []), ['leave_type_id' => $code])]));
                        ?>
                        <a href="<?= Html::encode($url) ?>" class="text-decoration-none">
                            <span class="badge bg-<?= $color ?> bg-opacity-10 text-<?= $color ?> border border-<?= $color ?>-subtle rounded-pill fw-medium px-2 py-1">
                                <?= Html::encode($label) ?> <strong><?= number_format($cnt) ?></strong>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
