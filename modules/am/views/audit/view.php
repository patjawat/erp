<?php

use app\components\AppHelper;
use app\modules\am\models\AssetAudit;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var AssetAudit $model */

$this->title = $model->audit_no;
$this->params['breadcrumbs'][] = ['label' => 'ตรวจนับพัสดุประจำปี', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$steps = [
    AssetAudit::STATUS_DRAFT  => ['label' => 'ฉบับร่าง',   'icon' => 'bi-pencil-square', 'color' => 'warning'],
    AssetAudit::STATUS_ACTIVE => ['label' => 'ดำเนินการ',  'icon' => 'bi-check2-circle', 'color' => 'success'],
    AssetAudit::STATUS_CLOSED => ['label' => 'ปิดแล้ว',    'icon' => 'bi-lock',          'color' => 'secondary'],
];
$statusKeys = array_keys($steps);
$currentIdx = (int) array_search($model->status, $statusKeys);

$condColors = [
    'ดี'         => 'success',
    'พอใช้'      => 'info',
    'ชำรุด'      => 'danger',
    'รอจำหน่าย' => 'warning',
    'เสื่อมสภาพ' => 'warning',
];

$conditionSummary = [];
foreach ($model->auditItems as $item) {
    $condName = $item->condition->name ?? $item->asset_condition ?? 'ไม่ระบุ';
    $conditionSummary[$condName] = ($conditionSummary[$condName] ?? 0) + 1;
}
$totalItems = count($model->auditItems);
?>

<div class="audit-view">

    <!-- Action Bar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="mb-1 fw-bold"><?= Html::encode($model->audit_no) ?></h5>
                    <div class="text-muted small">
                        <i class="bi bi-calendar3 me-1"></i>ปีงบประมาณ <?= Html::encode($model->fiscal_year) ?>
                        <?php if ($model->audit_date): ?>
                            <span class="mx-2 opacity-50">·</span>
                            <i class="bi bi-calendar-event me-1"></i>วันที่ตรวจนับ <?= Html::encode(AppHelper::convertToThai($model->audit_date)) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <?= Html::a('<i class="bi bi-file-earmark-excel me-1"></i> Export Excel', ['export-excel', 'id' => $model->id], ['class' => 'btn btn-success']) ?>
                    <?= Html::a('<i class="bi bi-pencil me-1"></i> แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('<i class="bi bi-trash me-1"></i> ลบ', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-outline-danger',
                        'data' => [
                            'confirm' => 'ต้องการลบใบตรวจนับ ' . $model->audit_no . ' หรือไม่?\nการลบไม่สามารถย้อนกลับได้',
                            'method' => 'post',
                        ],
                    ]) ?>
                    <?= Html::a('<i class="bi bi-arrow-left me-1"></i> กลับ', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Stepper -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-4">
            <div class="d-flex align-items-center justify-content-center">
                <?php foreach ($steps as $statusKey => $step):
                    $stepIdx     = (int) array_search($statusKey, $statusKeys);
                    $isDone      = $stepIdx < $currentIdx;
                    $isCurrent   = $stepIdx === $currentIdx;
                    $circleClass = $isCurrent
                        ? 'bg-' . $step['color'] . ' text-white'
                        : ($isDone ? 'bg-success text-white' : 'bg-light text-muted border');
                    $labelClass  = $isCurrent
                        ? 'fw-bold text-' . $step['color']
                        : ($isDone ? 'text-success' : 'text-muted');
                ?>
                <div class="d-flex flex-column align-items-center" style="min-width:100px">
                    <div class="rounded-circle d-flex align-items-center justify-content-center <?= $circleClass ?>"
                         style="width:48px;height:48px;font-size:1.2rem">
                        <?php if ($isDone): ?>
                            <i class="bi bi-check-lg"></i>
                        <?php else: ?>
                            <i class="bi <?= $step['icon'] ?>"></i>
                        <?php endif; ?>
                    </div>
                    <div class="mt-2 small <?= $labelClass ?> text-center"><?= $step['label'] ?></div>
                    <?php if ($isCurrent): ?>
                        <div class="mt-1">
                            <span class="badge bg-<?= $step['color'] ?><?= $step['color'] === 'warning' ? ' text-dark' : '' ?> small">
                                ปัจจุบัน
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if ($stepIdx < count($steps) - 1):
                    $lineClass = $isDone ? 'bg-success' : 'bg-light';
                ?>
                <div class="flex-grow-1 <?= $lineClass ?>" style="height:3px;max-width:80px;min-width:30px;margin-bottom:28px"></div>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">

        <!-- Info Card -->
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <span class="fw-semibold"><i class="bi bi-info-circle me-2 text-primary"></i>ข้อมูลการตรวจนับ</span>
                </div>
                <div class="card-body">
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="text-muted small mb-1"><i class="bi bi-hash me-1"></i>เลขที่ตรวจนับ</div>
                        <div class="fw-semibold fs-6 font-monospace"><?= Html::encode($model->audit_no) ?></div>
                    </div>
                    <div class="row g-3 mb-3 pb-3 border-bottom">
                        <div class="col-6">
                            <div class="text-muted small mb-1"><i class="bi bi-calendar-event me-1"></i>ปีงบประมาณ</div>
                            <div class="fw-semibold"><?= Html::encode($model->fiscal_year) ?></div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small mb-1"><i class="bi bi-calendar3 me-1"></i>วันที่ตรวจนับ</div>
                            <div class="fw-semibold"><?= Html::encode($model->audit_date ? AppHelper::convertToThai($model->audit_date) : '-') ?></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="text-muted small mb-1"><i class="bi bi-building me-1"></i>หน่วยงาน</div>
                        <div class="fw-semibold"><?= Html::encode($model->departmentRef->name ?? '-') ?></div>
                    </div>
                    <div class="mb-3">
                        <div class="text-muted small mb-1"><i class="bi bi-person me-1"></i>ผู้ตรวจนับ</div>
                        <div class="fw-semibold"><?= Html::encode($model->auditorLabel) ?></div>
                    </div>
                    <div class="mb-3">
                        <div class="text-muted small mb-1"><i class="bi bi-flag me-1"></i>สถานะ</div>
                        <?php
                        $badgeClass = match ($model->status) {
                            AssetAudit::STATUS_ACTIVE => 'bg-success',
                            AssetAudit::STATUS_CLOSED => 'bg-secondary',
                            default => 'bg-warning text-dark',
                        };
                        ?>
                        <span class="badge <?= $badgeClass ?>"><?= Html::encode($model->getStatusLabel()) ?></span>
                    </div>
                    <?php if ($model->summary_note): ?>
                    <div>
                        <div class="text-muted small mb-1"><i class="bi bi-chat-text me-1"></i>หมายเหตุรวม</div>
                        <div class="bg-light rounded p-2 small"><?= nl2br(Html::encode($model->summary_note)) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Summary Stats -->
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <span class="fw-semibold"><i class="bi bi-bar-chart me-2 text-primary"></i>สรุปผลการตรวจนับ</span>
                </div>
                <div class="card-body">
                    <!-- Total count -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted small">จำนวนรายการทั้งหมด</span>
                        <span class="fw-bold fs-4 text-primary"><?= $totalItems ?> <span class="fs-6 fw-normal text-muted">รายการ</span></span>
                    </div>
                    <?php if ($totalItems > 0): ?>
                    <div class="progress mb-4" style="height:8px">
                        <div class="progress-bar bg-primary" style="width:100%"></div>
                    </div>
                    <?php endif; ?>

                    <!-- Condition breakdown -->
                    <?php if (!empty($conditionSummary)): ?>
                    <div class="text-muted small mb-2">สรุปตามสภาพพัสดุ</div>
                    <div class="d-flex flex-column gap-2">
                        <?php foreach ($conditionSummary as $condName => $condCount):
                            $color = $condColors[$condName] ?? 'secondary';
                            $pct = $totalItems > 0 ? round($condCount / $totalItems * 100) : 0;
                        ?>
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small d-flex align-items-center gap-2">
                                    <span class="badge bg-<?= $color ?> bg-opacity-15 text-<?= $color ?> border border-<?= $color ?> border-opacity-25">
                                        <?= Html::encode($condName) ?>
                                    </span>
                                </span>
                                <span class="small fw-semibold"><?= $condCount ?> <span class="text-muted fw-normal">(<?= $pct ?>%)</span></span>
                            </div>
                            <div class="progress" style="height:5px">
                                <div class="progress-bar bg-<?= $color ?>" style="width:<?= $pct ?>%"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php elseif ($totalItems === 0): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-inbox fs-2 d-block mb-2 opacity-25"></i>
                        <div class="small">ยังไม่มีรายการตรวจนับ</div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
            <span class="fw-semibold">
                <i class="bi bi-list-check me-2 text-primary"></i>รายการที่ตรวจนับ
            </span>
            <span class="badge bg-primary bg-opacity-10 text-primary"><?= $totalItems ?> รายการ</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width:50px">#</th>
                        <th style="width:20%">รหัสครุภัณฑ์</th>
                        <th>ชื่อครุภัณฑ์</th>
                        <th class="text-center" style="width:130px">สภาพ</th>
                        <th>หมายเหตุ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($model->auditItems as $i => $item):
                        $condName  = $item->condition->name ?? $item->asset_condition ?? '';
                        $condColor = $condColors[$condName] ?? 'secondary';
                        $hasNote   = !empty($item->note);
                    ?>
                    <tr>
                        <td class="text-center text-muted small"><?= $i + 1 ?></td>
                        <td class="font-monospace small fw-semibold text-primary"><?= Html::encode($item->asset_code) ?></td>
                        <td><?= Html::encode($item->asset_name) ?></td>
                        <td class="text-center">
                            <?php if ($condName): ?>
                                <span class="badge bg-<?= $condColor ?> bg-opacity-15 text-<?= $condColor ?> border border-<?= $condColor ?> border-opacity-25 small">
                                    <?= Html::encode($condName) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted small">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="small <?= $hasNote ? 'text-dark' : 'text-muted' ?>">
                            <?php if ($hasNote): ?>
                                <i class="bi bi-chat-text me-1 text-warning"></i><?= nl2br(Html::encode($item->note)) ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($model->auditItems)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-5">
                            <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                            ไม่มีรายการตรวจนับ
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
