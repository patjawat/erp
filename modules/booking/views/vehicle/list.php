<?php

use yii\web\View;
use yii\helpers\Html;

$statusSummaryMap = [];
foreach (($statusSummary ?? []) as $row) {
    $statusSummaryMap[$row['status']] = (int) $row['total'];
}
$cancelledCount = (int) ($statusSummaryMap['Cancel'] ?? 0);
$models = $dataProvider->getModels();
$loggedItemsCount = 0;
$notLoggedItemsCount = 0;
foreach ($models as $model) {
    $hasLogged = false;
    foreach ($model->vehicleDetails as $detail) {
        $dist = $detail->distance_km ?? null;
        $oilPrice = $detail->oil_price ?? null;
        $oilLiter = $detail->oil_liter ?? null;

        if (
            ($dist !== null && (float) $dist > 0) ||
            ($oilPrice !== null && (float) $oilPrice > 0) ||
            ($oilLiter !== null && (float) $oilLiter > 0)
        ) {
            $hasLogged = true;
            break;
        }
    }
    if ($hasLogged) {
        $loggedItemsCount++;
    } else {
        $notLoggedItemsCount++;
    }
}
?>
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="m-0">
                <i class="bi bi-ui-checks"></i> ทะเบียนการขอใช้รถยนต์
                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill fw-medium px-2 py-1">
                    <?= number_format($dataProvider->getTotalCount(), 0) ?></span> รายการ
            </h6>
            <div class="d-flex justify-content-between">
                <button class="btn btn-success export-leave"><i class="fa-solid fa-file-excel"></i> Excel</button>
            </div>
        </div>
    </div>
</div>

<div class="row g-2 mb-3">
    <div class="col-12 col-md-6 col-xl-3">
        <div class="border border-danger-subtle rounded-3 p-3 h-100">
            <div class="small text-muted">งานรอดำเนินการจัดสรร</div>
            <div class="fs-5 fw-bold text-danger"><?= number_format((int) ($waitingAllocationCount ?? 0)) ?> รายการ</div>
            <div class="small text-muted">สถานะกลุ่มรออนุมัติ/รอจัดสรร และยังไม่มีรถหรือคนขับ</div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="border border-success-subtle rounded-3 p-3 h-100">
            <div class="small text-muted">งานที่จัดสรรแล้ว</div>
            <div class="fs-5 fw-bold text-success"><?= number_format((int) ($allocatedCount ?? 0)) ?> รายการ</div>
            <div class="small text-muted">มีการระบุรถหรือพนักงานขับแล้ว</div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="border border-secondary-subtle rounded-3 p-3 h-100">
            <div class="small text-muted">งานที่ยกเลิก</div>
            <div class="fs-5 fw-bold text-secondary"><?= number_format($cancelledCount) ?> รายการ</div>
            <div class="small text-muted">นับจากสถานะ Cancel ในรายการปัจจุบัน</div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="border border-primary-subtle rounded-3 p-3 h-100">
            <div class="small text-muted mb-2">บันทึกสรุปการเดินทาง</div>
            <div class="d-flex flex-wrap gap-1">
                <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill fw-medium px-2 py-1">
                    <i class="bi bi-check2-circle me-1 fs-6"></i>บันทึก <?= number_format((int) $loggedItemsCount) ?> รายการ
                </span>
                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle rounded-pill fw-medium px-2 py-1">
                    <i class="bi bi-slash-circle me-1 fs-6"></i>ยังไม่บันทึก <?= number_format((int) $notLoggedItemsCount) ?> รายการ
                </span>
            </div>
        </div>
    </div>
</div>

<div>
    <div class="overflow-auto" style="max-height: 68vh;">
        <div class="row g-2">
            <?php foreach ($models as $key => $item): ?>
                <?php
                $created = $item->viewCreated();
                $requester = $item->userRequest();
                $daysPassed = 0;
                try {
                    $createdAt = new \DateTime((string) $item->created_at);
                    $now = new \DateTime();
                    $daysPassed = (int) $createdAt->diff($now)->days;
                } catch (\Throwable $th) {
                    $daysPassed = 0;
                }

                $hasAssignedDriver = false;
                $assignedDriverIds = [];
                $assignedPlates = [];
                $assignedCarImages = [];
                $carImgPlaceholder = \Yii::getAlias('@web') . '/img/placeholder-img.jpg';
                foreach ($item->vehicleDetails as $detail) {
                    if (!empty($detail->driver_id)) {
                        $assignedDriverIds[(string) $detail->driver_id] = true;
                    }

                    $plate = trim((string) ($detail->license_plate ?? ''));
                    if ($plate !== '' && $plate !== ' ') {
                        $assignedPlates[$plate] = true;
                        if (!isset($assignedCarImages[$plate])) {
                            $assignedCarImages[$plate] = !empty($detail->car)
                                ? ($detail->car->ShowImg()['image'] ?? $carImgPlaceholder)
                                : $carImgPlaceholder;
                        }
                    }

                    if (!empty($detail->driver_id) || ($plate !== '' && $plate !== ' ')) {
                        $hasAssignedDriver = true;
                    }
                }

                $driverCount = count($assignedDriverIds);
                $plateCount = count($assignedPlates);
                $plateKeys = array_keys($assignedPlates);
                $plateSummary = $plateCount > 0 ? ($plateKeys[0] . ($plateCount > 1 ? ' +' . ($plateCount - 1) : '')) : '-';
                $carCount = count($assignedCarImages);
                $carKeys = array_keys($assignedCarImages);

                $isOvernight = (string) $item->go_type === '2';
                $isWaitingStatus = in_array((string) $item->status, ['Pending', 'Pass', 'Approve'], true);
                $requestStartTs = strtotime((string) $item->date_start . ' ' . (string) $item->time_start);
                $isStartPassed = $requestStartTs !== false ? $requestStartTs < time() : false;
                $isAllocationOverdue = $isOvernight && $isWaitingStatus && !$hasAssignedDriver && $isStartPassed;
                ?>
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-2">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-1">
                                <div class="fw-bold text-primary small">
                                    #<?= (($dataProvider->pagination->offset + 1) + $key) ?> · รหัสขอใช้รถ <?= Html::encode($item->code) ?>
                                </div>
                                <div>
                                    <?php if ($item->is_shared == 1): ?>
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info-subtle rounded-pill fw-medium px-2 py-1">
                                            <i class="fa-solid fa-user-group me-1"></i>จัดสรรร่วม
                                        </span>
                                    <?php else: ?>
                                        <?= $item->viewStatus()['view'] ?? '-' ?>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="row g-2 align-items-start">
                                <div class="col-12 col-lg-3">
                                    <div class="d-flex align-items-center">
                                        <?= Html::img('@web/img/loading.gif', [
                                            'class' => 'rounded-3 me-2 shadow-sm lazyload',
                                            'width' => '32',
                                            'height' => '32',
                                            'data' => [
                                                'expand' => '-20',
                                                'sizes' => 'auto',
                                                'src' => $requester['photo']
                                            ]
                                        ]); ?>
                                        <div>
                                            <div class="fw-bold mb-0 small"><?= Html::encode($requester['fullname']) ?></div>
                                            <small class="text-primary d-block"><?= Html::encode($requester['department']) ?></small>
                                        </div>
                                    </div>
                                    <div class="small text-muted mt-1">
                                        <i class="bi bi-calendar-check me-1"></i>จองเมื่อ <?= Html::encode($created['full'] ?? '-') ?>
                                    </div>
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info-subtle rounded-pill fw-medium px-2 py-1 mt-1">
                                        ผ่านมาแล้ว <?= number_format($daysPassed) ?> วัน
                                    </span>
                                </div>

                                <div class="col-12 col-lg-3">
                                    <div class="small text-muted mb-1">รายละเอียด</div>
                                    <div class="text-primary text-truncate"><?= Html::encode($item->reason) ?></div>
                                    <div class="small mt-1">ความเร่งด่วน <?= $item->viewUrgent() ?></div>
                                </div>

                                <div class="col-12 col-lg-3">
                                    <div class="text-muted mb-1">สถานที่ไป</div>
                                    <div class="fw-bold text-truncate small">
                                        <i class="bi bi-geo-alt text-danger me-1"></i><?= Html::encode($item->locationOrg?->title ?? '-') ?>
                                    </div>

                                    <div class="small text-muted mt-2 mb-1">วันเวลาที่ต้องการใช้รถ</div>
                                    <div class="fw-medium text-dark small">
                                        <?= $item->showDateRange() ?> <?= $item->viewTime()['full'] ?>
                                    </div>

                                    <?php if ($isAllocationOverdue): ?>
                                        <div class="mt-1">
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle rounded-pill fw-medium px-2 py-1">
                                                เกินกำหนดจัดสรรรถ โปรดเร่งจัดรถ/คนขับ
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-12 col-lg-3">
                                    <div class="small text-muted mb-1">ข้อมูลการจัดสรร</div>

                                    <div class="mt-2 d-flex flex-wrap align-items-start gap-2">
                                        <div class="d-flex flex-column flex-grow-1 min-w-0">
                                            <div class="small text-muted">รถยนต์ : <span class="fw-bold"><?= Html::encode($plateSummary) ?></span></div>
                                            <div class="d-flex flex-wrap gap-1 align-items-center mt-1">
                                                <?php
                                                $thumbKeys = array_slice($carKeys, 0, 3);
                                                foreach ($thumbKeys as $plateKey):
                                                    $imgSrc = $assignedCarImages[$plateKey] ?? $carImgPlaceholder;
                                                ?>
                                                    <?= Html::img('@web/img/loading.gif', [
                                                        'class' => 'avatar-sm rounded-circle shadow lazyload',
                                                        'alt' => '',
                                                        'data' => [
                                                            'expand' => '-20',
                                                            'sizes' => 'auto',
                                                            'src' => $imgSrc,
                                                        ],
                                                    ]) ?>
                                                <?php endforeach; ?>

                                                <?php if ($carCount > 3): ?>
                                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill fw-medium px-2 py-1">
                                                        +<?= (int) ($carCount - 3) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                            <div class="d-flex flex-wrap gap-1 mt-1 align-items-center">
                                                <?php foreach ($item->vehicleDetails as $detail): ?>
                                                    <?php if (!empty($detail->driver)): ?>
                                                        <?= Html::a(
                                                            Html::img('@web/img/loading.gif', [
                                                                'class' => 'avatar-sm rounded-circle shadow lazyload',
                                                                'data' => [
                                                                    'expand' => '-20',
                                                                    'sizes' => 'auto',
                                                                    'src' => $detail->driver->showAvatar(),
                                                                ],
                                                            ]),
                                                            ['/booking/vehicle/work-update', 'id' => $detail->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> บันทึกภาระกิจการใช้รถยนต์'],
                                                            ['class' => 'open-modal', 'data' => ['size' => 'modal-lg']]
                                                        ) ?>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </div>
                                    </div>



                                    <div class="d-flex flex-wrap gap-1 justify-content-end mt-2">
                                        <?= Html::a('<i class="fa-solid fa-eye me-1"></i>แสดง', ['/booking/vehicle/view', 'id' => $item->id], ['class' => 'btn btn-sm btn-outline-secondary open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                                        <?= Html::a('<i class="fa-solid fa-pen-to-square me-1"></i>แก้ไข', ['/booking/vehicle/update', 'id' => $item->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไขการจงรถ'], ['class' => 'btn btn-sm btn-outline-primary open-modal', 'data' => ['size' => 'modal-xl']]) ?>
                                        <?= Html::a('<i class="fa-solid fa-print me-1"></i>พิมพ์', ['/booking/vehicle/print', 'id' => $item->id, 'title' => 'ใบขอใช้รถยนต์'], ['class' => 'btn btn-sm btn-outline-dark', 'target' => '_blank', 'rel' => 'noopener noreferrer', 'data-pjax' => '0']) ?>
                                        <?= Html::a('<i class="fa-regular fa-circle-xmark me-1"></i>ยกเลิก', ['/booking/vehicle/cancel', 'id' => $item->id], ['class' => 'btn btn-sm btn-outline-danger cancel-order', 'data' => ['size' => 'modal-lg']]) ?>
                                    </div>
                                </div>
                            </div>

                            <!-- moved "วันเวลาที่ต้องการใช้รถ" ไปอยู่กับช่อง "สถานที่ไป" แล้ว -->
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>

    <div class="body-footer mt-2">
        <div class="d-flex justify-content-center">
            <?= yii\bootstrap5\LinkPager::widget([
                'pagination' => $dataProvider->pagination,
                'firstPageLabel' => 'หน้าแรก',
                'lastPageLabel' => 'หน้าสุดท้าย',
                'options' => [
                    'class' => 'pagination pagination-sm',
                ],
            ]); ?>
        </div>
    </div>
</div>
<?php
$js = <<< JS


JS;
$this->registerJS($js, View::POS_END);
?>