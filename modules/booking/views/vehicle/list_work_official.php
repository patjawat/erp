<?php

use yii\helpers\Html;

$statusSummaryMap = [];
foreach (($statusSummary ?? []) as $row) {
    $statusSummaryMap[$row['status']] = (int) $row['total'];
}
$cancelledCount = (int) ($statusSummaryMap['Cancel'] ?? 0);
$pageTitle = $title ?? 'ทะเบียนการขอใช้รถยนต์ทั่วไป';
?>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="m-0">
                <i class="bi bi-ui-checks"></i> <?= Html::encode($pageTitle) ?>
                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill fw-medium px-2 py-1">
                    <?= number_format($dataProvider->getTotalCount(), 0) ?></span> รายการ
            </h6>
            <div class="d-flex justify-content-between">
                <button class="btn btn-success export-leave"><i class="fa-solid fa-file-excel"></i> Excel</button>
            </div>
        </div>
    </div>
</div>

<?php
$models = $dataProvider->getModels();
$distanceSum = 0.0;
$distanceLoggedCount = 0;
$oilPriceSum = 0.0;
$oilLiterSum = 0.0;
$oilLoggedCount = 0;
$loggedItemsCount = 0;
$notLoggedItemsCount = 0;

foreach ($models as $model) {
    $distanceVal = $model->distance_km ?? null;
    $oilPriceVal = $model->oil_price ?? null;
    $oilLiterVal = $model->oil_liter ?? null;

    $hasDistance = ($distanceVal !== null && (float) $distanceVal > 0);
    $hasOil = ($oilPriceVal !== null && (float) $oilPriceVal > 0) || ($oilLiterVal !== null && (float) $oilLiterVal > 0);

    if ($hasDistance) {
        $distanceLoggedCount++;
        $distanceSum += (float) $distanceVal;
    }

    if ($hasOil) {
        $oilLoggedCount++;
        $oilPriceSum += (float) $oilPriceVal;
        $oilLiterSum += (float) $oilLiterVal;
    }

    if ($hasDistance || $hasOil) {
        $loggedItemsCount++;
    } else {
        $notLoggedItemsCount++;
    }
}
?>

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
            <div class="small text-muted mb-2">สรุปผลการบันทึก</div>
            <div class="d-flex flex-wrap gap-1">
                <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill fw-medium px-2 py-1">
                    <i class="bi bi-check2-circle me-1 fs-6"></i>บันทึกแล้ว <?= number_format((int) $loggedItemsCount) ?>
                </span>
                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle rounded-pill fw-medium px-2 py-1">
                    <i class="bi bi-slash-circle me-1 fs-6"></i>ยังไม่บันทึก <?= number_format((int) $notLoggedItemsCount) ?>
                </span>
            </div>
        </div>
    </div>
</div>

<div>
    <div class="row g-2 mb-3">
        <div class="col-12 col-md-6">
            <div class="border border-info-subtle rounded-3 p-3 h-100">
                <div class="small text-muted">สรุประยะทางที่บันทึกการใช้งาน</div>
                <div class="fs-5 fw-bold text-info">
                    <?= number_format($distanceSum, 2) ?> กม.
                </div>
                <div class="small text-muted">
                    จากรายการที่บันทึกระยะทาง <?= (int) $distanceLoggedCount ?> รายการ
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="border border-warning-subtle rounded-3 p-3 h-100">
                <div class="small text-muted">สรุปค่าน้ำมันที่บันทึกการใช้งาน</div>
                <div class="fs-5 fw-bold text-warning">
                    <?= number_format($oilPriceSum, 2) ?> บาท
                </div>
                <div class="small text-muted">
                    <?= number_format($oilLiterSum, 2) ?> ลิตร
                </div>
                <div class="small text-muted">
                    จากรายการที่บันทึกค่าน้ำมัน <?= (int) $oilLoggedCount ?> รายการ
                </div>
            </div>
        </div>
    </div>

    <div class="overflow-auto" style="max-height: 68vh;">
        <div class="row g-2">
            <?php foreach ($models as $key => $item): ?>
                <?php
                $vehicle = $item->vehicle;

                $created = $vehicle?->viewCreated();
                $requester = $vehicle?->userRequest() ?? [];

                $daysPassed = 0;
                try {
                    $createdAt = new \DateTime((string) ($vehicle?->created_at ?? ''));
                    $now = new \DateTime();
                    $daysPassed = (int) $createdAt->diff($now)->days;
                } catch (\Throwable $th) {
                    $daysPassed = 0;
                }

                $carImgPlaceholder = \Yii::getAlias('@web') . '/img/placeholder-img.jpg';

                $hasAssignedDriver = false;
                $assignedPlates = [];
                $assignedCarImages = [];

                $plate = trim((string) ($item->license_plate ?? ''));
                $plateOk = $plate !== '' && $plate !== ' ';

                if ($plateOk) {
                    $assignedPlates[$plate] = true;
                    $assignedCarImages[$plate] = !empty($item->car)
                        ? ($item->car->ShowImg()['image'] ?? $carImgPlaceholder)
                        : $carImgPlaceholder;
                }

                if (!empty($item->driver_id) || $plateOk) {
                    $hasAssignedDriver = true;
                }

                $driverCount = !empty($item->driver_id) ? 1 : 0;

                $plateCount = count($assignedPlates);
                $plateKeys = array_keys($assignedPlates);
                $plateSummary = $plateCount > 0 ? ($plateKeys[0]) : '-';

                $carCount = count($assignedCarImages);
                $carKeys = array_keys($assignedCarImages);

                $isOvernight = (string) ($vehicle?->go_type ?? '') === '2';
                $waitingStatusValue = $vehicle?->status ?? $item->status;
                $isWaitingStatus = in_array((string) $waitingStatusValue, ['Pending', 'Pass', 'Approve'], true);

                $startDate = (string) ($vehicle?->date_start ?? $item->date_start ?? '');
                $startTime = (string) ($vehicle?->time_start ?? $item->time_start ?? '');
                $requestStartTs = strtotime(trim($startDate . ' ' . $startTime));
                $isStartPassed = $requestStartTs !== false ? $requestStartTs < time() : false;

                $isAllocationOverdue = $isOvernight && $isWaitingStatus && !$hasAssignedDriver && $isStartPassed;

                $queueNo = ($dataProvider->pagination->offset + 1) + $key;
                $statusView = $item->viewStatus()['view'] ?? '-';

                $distanceKmVal = $item->distance_km ?? null;
                $oilPriceVal = $item->oil_price ?? null;
                $oilLiterVal = $item->oil_liter ?? null;

                $distanceKmText = $distanceKmVal !== null && (float) $distanceKmVal > 0
                    ? number_format((float) $distanceKmVal, 2)
                    : '-';
                $oilPriceText = $oilPriceVal !== null && (float) $oilPriceVal > 0
                    ? number_format((float) $oilPriceVal, 2)
                    : '-';
                $oilLiterText = $oilLiterVal !== null && (float) $oilLiterVal > 0
                    ? number_format((float) $oilLiterVal, 2)
                    : '-';

                $locationTitle = $vehicle?->locationOrg?->title ?? '-';
                $reason = $vehicle?->reason ?? '-';
                $urgent = $vehicle?->viewUrgent() ?? '';

                $showDateRange = $vehicle?->showDateRange() ?? '-';
                $showTimeFull = $vehicle?->viewTime()['full'] ?? '-';

                $workUpdateUrl = ['/booking/vehicle/work-update', 'id' => $item->id, 'title' => 'บันทึกภาระกิจการใช้รถยนต์'];
                $viewUrl = ['view', 'id' => $item->id];
                $cancelUrl = ['/booking/vehicle-detail/cancel', 'id' => $item->id];
                $printUrl = $vehicle?->id ? ['/booking/vehicle/print', 'id' => $vehicle->id] : null;
                ?>

                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-2">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-1">
                                <div class="fw-bold text-primary small">
                                    #<?= (int) $queueNo ?> · รหัสขอใช้รถ <?= Html::encode($vehicle?->code ?? '-') ?>
                                </div>
                                <div>
                                    <?php if (($vehicle?->is_shared ?? 0) == 1): ?>
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info-subtle rounded-pill fw-medium px-2 py-1">
                                            <i class="fa-solid fa-user-group me-1"></i>จัดสรรร่วม
                                        </span>
                                    <?php else: ?>
                                        <?= $statusView ?>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="row g-2 align-items-start">
                                <div class="col-12 col-lg-2">
                                    <div class="d-flex align-items-center">
                                        <?= Html::img('@web/img/loading.gif', [
                                            'class' => 'rounded-3 me-2 shadow-sm lazyload',
                                            'width' => '32',
                                            'height' => '32',
                                            'data' => [
                                                'expand' => '-20',
                                                'sizes' => 'auto',
                                                'src' => $requester['photo'] ?? '',
                                            ],
                                        ]); ?>
                                        <div>
                                            <div class="fw-bold mb-0 small"><?= Html::encode($requester['fullname'] ?? '-') ?></div>
                                            <small class="text-primary d-block"><?= Html::encode($requester['department'] ?? '-') ?></small>
                                        </div>
                                    </div>
                                    <div class="small text-muted mt-1">
                                        <i class="bi bi-calendar-check me-1"></i>จองเมื่อ <?= Html::encode($created['full'] ?? '-') ?>
                                    </div>
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info-subtle rounded-pill fw-medium px-2 py-1 mt-1">
                                        ผ่านมาแล้ว <?= number_format($daysPassed) ?> วัน
                                    </span>
                                </div>

                                <div class="col-12 col-lg-2">
                                    <div class="small text-muted mb-1">รายละเอียด</div>
                                    <div class="small text-muted text-truncate"><?= Html::encode($reason) ?></div>
                                    <div class="small mt-1">ความเร่งด่วน <?= Html::encode((string) $urgent) ?></div>
                                </div>

                                <div class="col-12 col-lg-2">
                                    <div class="small text-muted mb-1">สถานที่ไป</div>
                                    <div class="fw-bold text-truncate small">
                                        <i class="bi bi-geo-alt text-danger me-1"></i><?= Html::encode($locationTitle) ?>
                                    </div>

                                    <div class="small text-muted mt-2 mb-1">วันเวลาที่ต้องการใช้รถ</div>
                                    <div class="fw-medium text-dark small">
                                        <?= Html::encode($showDateRange) ?> <?= Html::encode($showTimeFull) ?>
                                    </div>

                                    <?php if ($isAllocationOverdue): ?>
                                        <div class="mt-1">
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle rounded-pill fw-medium px-2 py-1">
                                                เกินกำหนดจัดสรรรถ โปรดเร่งจัดรถ/คนขับ
                                            </span>
                                        </div>
                                    <?php endif; ?>

                                    <!-- ปุ่ม action จะอยู่ในคอลัมน์ "ข้อมูลการจัดสรร" (ให้ตำแหน่งตรงกับหน้า index) -->
                                </div>

                                <div class="col-12 col-lg-2">
                                    <div class="small text-muted mb-1">บันทึกสรุปการใช้รถ</div>
                                    <?php if (!$hasDistance && !$hasOil): ?>
                                        <div class="mt-2">
                                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle rounded-pill fw-medium px-2 py-1">
                                                <i class="bi bi-slash-circle me-1"></i>ยังไม่สรุปการเดินทาง
                                            </span>
                                        </div>
                                    <?php else: ?>
                                        <div class="small text-muted mt-2 mb-1">บันทึกสรุปการใช้รถ</div>
                                        <div class="small fw-medium text-dark text-truncate">
                                            <i class="bi bi-signpost-2 me-1 text-primary"></i>ระยะทาง <?= Html::encode($distanceKmText) ?> กม.
                                        </div>
                                        <div class="small text-muted text-truncate">
                                            <i class="bi bi-currency-baht me-1 text-warning"></i>ค่าน้ำมัน <?= Html::encode($oilPriceText) ?> บาท
                                            (<?= Html::encode($oilLiterText) ?> ลิตร)
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-12 col-lg-4">
                                    <div class="small text-muted mb-1">ข้อมูลการจัดสรร</div>

                                    <div class="mt-2 d-flex flex-wrap align-items-start gap-2">
                                        <div class="d-flex flex-column flex-grow-1 min-w-0">
                                            <div class="small text-muted">รถยนต์</div>
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

                                            <div class="small fw-medium text-dark text-truncate mt-1">
                                                <i class="bi bi-car-front me-1 text-primary"></i><?= Html::encode($plateSummary) ?>
                                            </div>
                                        </div>

                                        <div class="d-flex flex-column ms-auto">
                                            <div class="small text-muted">พขร</div>
                                            <div class="small text-muted mt-1">
                                                <?= $driverCount > 0 ? ($driverCount . ' คน') : '-' ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex flex-wrap gap-1 mt-1 align-items-center">
                                        <?php if (!empty($item->driver)): ?>
                                            <?= Html::a(
                                                Html::img('@web/img/loading.gif', [
                                                    'class' => 'avatar-sm rounded-circle shadow lazyload',
                                                    'data' => [
                                                        'expand' => '-20',
                                                        'sizes' => 'auto',
                                                        'src' => $item->driver->showAvatar(),
                                                    ],
                                                ]),
                                                $workUpdateUrl,
                                                ['class' => 'open-modal', 'data' => ['size' => 'modal-lg']]
                                            ) ?>
                                        <?php endif; ?>
                                    </div>

                                    <div class="d-flex flex-wrap gap-1 justify-content-end mt-2">
                                        <?= Html::a('<i class="fa-solid fa-eye me-1"></i>แสดง', $viewUrl, ['class' => 'btn btn-sm btn-outline-secondary open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                                        <?= Html::a('<i class="fa-solid fa-pen-to-square me-1"></i>บันทึก', $workUpdateUrl, ['class' => 'btn btn-sm btn-outline-primary open-modal', 'data' => ['size' => 'modal-xl']]) ?>
                                        <?php if (!empty($printUrl)): ?>
                                            <?= Html::a('<i class="fa-solid fa-print me-1"></i>พิมพ์', $printUrl, ['class' => 'btn btn-sm btn-outline-dark', 'title' => 'ใบขอใช้รถยนต์', 'target' => '_blank']) ?>
                                        <?php endif; ?>
                                        <?= Html::a('<i class="fa-regular fa-circle-xmark me-1"></i>ยกเลิก', $cancelUrl, ['class' => 'btn btn-sm btn-outline-danger cancel-order', 'data' => ['size' => 'modal-lg']]) ?>
                                    </div>
                                </div>
                            </div>
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

