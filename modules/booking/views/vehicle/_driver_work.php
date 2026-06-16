<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\Vehicle[] $trips */
/** @var string $driverName */

$totalTrips = count($trips);
?>

<div class="driver-work-modal">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <div class="fw-semibold"><?= Html::encode($driverName) ?></div>
            <div class="text-muted small">รวม <?= number_format($totalTrips) ?> ภารกิจที่ได้รับจัดสรร</div>
        </div>
    </div>

    <?php if (empty($trips)): ?>
        <div class="text-center text-muted py-5">
            <i class="fa-regular fa-folder-open fs-2 d-block mb-2" aria-hidden="true"></i>
            ยังไม่มีรายการภารกิจของพนักงานขับรถคนนี้
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-sm align-middle driver-work-table mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:40px;" class="text-center">#</th>
                        <th style="width:180px;">วันที่</th>
                        <th>สถานที่ที่ไป</th>
                        <th>วัตถุประสงค์</th>
                        <th style="width:100px;" class="text-center">สถานะ</th>
                        <th style="width:60px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($trips as $i => $trip): ?>
                        <?php
                        $dateRange = $trip->showDateRange();
                        $location = $trip->locationOrg?->title ?: ($trip->location ?: '-');
                        $reason = trim((string) $trip->reason) ?: '-';

                        $statusLabel = $trip->vehicleStatus?->title ?? $trip->status;
                        $statusClass = match ($trip->status) {
                            'Approve' => 'bg-primary-subtle text-primary',
                            'Pass'    => 'bg-info-subtle text-info',
                            'Success' => 'bg-success-subtle text-success',
                            default   => 'bg-light text-muted border',
                        };

                        $viewUrl = Url::to([
                            '/booking/vehicle/view',
                            'id' => $trip->id,
                            'title' => '<i class="fa-solid fa-car"></i> รายละเอียดการใช้ยานพาหนะ',
                        ]);
                        ?>
                        <tr>
                            <td class="text-center text-muted small"><?= $i + 1 ?></td>
                            <td>
                                <div class="fw-semibold"><?= Html::encode($dateRange) ?></div>
                                <div class="text-muted small">
                                    <?= Html::encode(trim(($trip->time_start ?: '') . ' - ' . ($trip->time_end ?: ''), ' -')) ?: '-' ?>
                                </div>
                            </td>
                            <td>
                                <div class="text-truncate" style="max-width:280px;" title="<?= Html::encode($location) ?>">
                                    <?= Html::encode($location) ?>
                                </div>
                            </td>
                            <td>
                                <div class="text-truncate" style="max-width:320px;" title="<?= Html::encode($reason) ?>">
                                    <?= Html::encode($reason) ?>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge rounded-pill <?= $statusClass ?>">
                                    <?= Html::encode($statusLabel) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="<?= $viewUrl ?>"
                                    class="btn btn-sm btn-icon btn-ghost-secondary open-modal"
                                    data-size="modal-lg"
                                    aria-label="ดูรายละเอียดภารกิจวันที่ <?= Html::encode($dateRange) ?>">
                                    <i class="fa-solid fa-eye" aria-hidden="true"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
