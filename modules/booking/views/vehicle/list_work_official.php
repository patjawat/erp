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
                <button type="button" class="btn btn-success export-leave"><i class="fa-solid fa-file-excel"></i> Excel</button>
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

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 vehicle-list-table">
            <thead class="vehicle-list-thead">
                <tr class="small text-muted">
                    <th scope="col" class="ps-3" style="width: 96px;">ลำดับ</th>
                    <th scope="col" style="width: 110px;">สถานะ</th>
                    <th scope="col" style="min-width: 220px;">ผู้ขอใช้รถ</th>
                    <th scope="col" class="d-none d-md-table-cell" style="min-width: 200px;">รายละเอียดการใช้รถ</th>
                    <th scope="col" class="d-none d-md-table-cell" style="min-width: 160px;">ปลายทาง</th>
                    <th scope="col" class="d-none d-sm-table-cell" style="min-width: 180px;">วันเวลาที่ใช้รถ</th>
                    <th scope="col" class="d-none d-lg-table-cell" style="min-width: 220px;">การจัดสรรรถและพนักงานขับ</th>
                    <th scope="col" class="d-none d-xl-table-cell" style="min-width: 200px;">สรุปการใช้รถ</th>
                    <th scope="col" class="pe-3 text-center vehicle-actions-th">การจัดการ</th>
                </tr>
            </thead>
            <tbody>
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
                    <tr>
                        <td class="ps-3">
                            <div class="text-primary fw-bold lh-sm">#<?= (int) $queueNo ?></div>
                            <div class="vlt-primary lh-sm" title="รหัสขอใช้รถ"><?= Html::encode($vehicle?->code ?? '-') ?></div>
                        </td>

                        <td>
                            <?php if (($vehicle?->is_shared ?? 0) == 1): ?>
                                <span class="badge bg-info bg-opacity-10 text-info border border-info-subtle rounded-pill fw-medium px-2 py-1">
                                    <i class="fa-solid fa-user-group me-1"></i>จัดสรรร่วม
                                </span>
                            <?php else: ?>
                                <?= $statusView ?>
                            <?php endif; ?>
                        </td>

                        <td>
                            <div class="d-flex align-items-center">
                                <?= Html::img('@web/img/loading.gif', [
                                    'class' => 'rounded-3 me-2 shadow-sm lazyload flex-shrink-0',
                                    'width' => '36',
                                    'height' => '36',
                                    'data' => [
                                        'expand' => '-20',
                                        'sizes' => 'auto',
                                        'src' => $requester['photo'] ?? '',
                                    ],
                                ]); ?>
                                <div class="min-w-0">
                                    <div class="vlt-primary text-truncate"><?= Html::encode($requester['fullname'] ?? '-') ?></div>
                                    <div class="vlt-secondary text-primary text-truncate"><?= Html::encode($requester['department'] ?? '-') ?></div>
                                    <div class="vlt-meta">
                                        <i class="bi bi-calendar-check me-1"></i>จองเมื่อ <?= Html::encode($created['full'] ?? '-') ?>
                                        <span class="ms-1">· ผ่านมา <?= number_format($daysPassed) ?> วัน</span>
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td class="d-none d-md-table-cell">
                            <div class="vlt-primary text-truncate" style="max-width: 240px;" title="<?= Html::encode($reason) ?>">
                                <?= Html::encode($reason) ?>
                            </div>
                            <div class="vlt-secondary mt-1">ความเร่งด่วน <?= Html::encode((string) $urgent) ?></div>
                        </td>

                        <td class="d-none d-md-table-cell">
                            <div class="vlt-primary text-truncate" style="max-width: 200px;" title="<?= Html::encode($locationTitle) ?>">
                                <i class="bi bi-geo-alt text-danger me-1"></i><?= Html::encode($locationTitle) ?>
                            </div>
                        </td>

                        <td class="d-none d-sm-table-cell">
                            <div class="vlt-primary"><?= Html::encode($showDateRange) ?></div>
                            <div class="vlt-secondary"><?= Html::encode($showTimeFull) ?></div>
                            <?php if ($isAllocationOverdue): ?>
                                <div class="mt-1">
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle rounded-pill fw-medium px-2 py-1">
                                        <i class="bi bi-exclamation-triangle me-1"></i>เกินกำหนดจัดสรร
                                    </span>
                                </div>
                            <?php endif; ?>
                        </td>

                        <td class="d-none d-lg-table-cell">
                            <div class="d-flex flex-wrap align-items-center gap-1">
                                <?php
                                $thumbKeys = array_slice($carKeys, 0, 3);
                                foreach ($thumbKeys as $plateKey):
                                    $imgSrc = $assignedCarImages[$plateKey] ?? $carImgPlaceholder;
                                ?>
                                    <?= Html::img('@web/img/loading.gif', [
                                        'class' => 'avatar-sm rounded-circle shadow-sm lazyload',
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
                                <?php if ($plateCount === 0): ?>
                                    <span class="vlt-secondary fst-italic">ยังไม่จัดสรรรถ</span>
                                <?php endif; ?>
                            </div>
                            <?php if ($plateCount > 0): ?>
                                <div class="vlt-primary mt-1">
                                    <i class="bi bi-car-front me-1 text-primary"></i><?= Html::encode($plateSummary) ?>
                                </div>
                            <?php endif; ?>
                            <div class="d-flex flex-wrap gap-1 mt-1 align-items-center">
                                <?php if (!empty($item->driver)): ?>
                                    <?= Html::a(
                                        Html::img('@web/img/loading.gif', [
                                            'class' => 'avatar-sm rounded-circle shadow-sm lazyload',
                                            'data' => [
                                                'expand' => '-20',
                                                'sizes' => 'auto',
                                                'src' => $item->driver->showAvatar(),
                                            ],
                                        ]),
                                        $workUpdateUrl,
                                        ['class' => 'open-modal', 'data' => ['size' => 'modal-lg'], 'title' => 'บันทึกภาระกิจการใช้รถ']
                                    ) ?>
                                    <?php if ($driverCount > 0): ?>
                                        <span class="small text-muted ms-1"><?= $driverCount ?> คน</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </td>

                        <td class="d-none d-xl-table-cell">
                            <?php if (!$hasDistance && !$hasOil): ?>
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle rounded-pill fw-medium px-2 py-1">
                                    <i class="bi bi-slash-circle me-1"></i>ยังไม่สรุปการเดินทาง
                                </span>
                            <?php else: ?>
                                <div class="vlt-primary">
                                    <i class="bi bi-signpost-2 me-1 text-primary"></i>ระยะ <?= Html::encode($distanceKmText) ?> กม.
                                </div>
                                <div class="vlt-secondary">
                                    <i class="bi bi-currency-baht me-1 text-warning"></i>น้ำมัน <?= Html::encode($oilPriceText) ?> บาท
                                    <span>(<?= Html::encode($oilLiterText) ?> ล.)</span>
                                </div>
                            <?php endif; ?>
                        </td>

                        <td class="text-center align-middle vehicle-actions-cell px-2 px-md-3">
                            <div class="vehicle-actions-inner d-flex flex-row flex-wrap justify-content-center align-items-center gap-2">
                                <?= Html::a('<i class="fa-regular fa-eye"></i>',
                                    $viewUrl,
                                    ['class' => 'btn btn-sm btn-primary open-modal', 'data' => ['size' => 'modal-lg'], 'title' => 'แสดงรายละเอียด', 'aria-label' => 'แสดงรายละเอียด']) ?>
                                <?= Html::a('<i class="fa-solid fa-key"></i>',
                                    $workUpdateUrl,
                                    ['class' => 'btn btn-sm btn-warning open-modal', 'data' => ['size' => 'modal-xl'], 'title' => 'บันทึกภาระกิจ', 'aria-label' => 'บันทึกภาระกิจ']) ?>

                                <div class="dropdown flex-grow-1 flex-sm-grow-0">
                                    <button class="btn btn-sm btn-secondary dropdown-toggle action-dropdown-toggle w-100 w-sm-auto"
                                            type="button"
                                            data-bs-toggle="dropdown"
                                            aria-expanded="false"
                                            aria-label="เมนูเพิ่มเติม">
                                        <i class="fa-solid fa-angle-down"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                        <?php if (!empty($printUrl)): ?>
                                            <li>
                                                <?= Html::a('<i class="fa-solid fa-print me-2 text-dark"></i>พิมพ์ใบขอใช้รถ',
                                                    $printUrl,
                                                    ['class' => 'dropdown-item', 'target' => '_blank']) ?>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                        <?php endif; ?>
                                        <li>
                                            <?= Html::a('<i class="fa-regular fa-circle-xmark me-2"></i>ยกเลิกรายการ',
                                                $cancelUrl,
                                                ['class' => 'dropdown-item text-danger cancel-order', 'data' => ['size' => 'modal-lg']]) ?>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($models)): ?>
                    <tr>
                        <td colspan="9" class="text-center text-muted py-5">
                            <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                            ไม่พบรายการขอใช้รถยนต์
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="body-footer p-2 border-top">
        <div class="d-flex justify-content-center">
            <?= yii\bootstrap5\LinkPager::widget([
                'pagination' => $dataProvider->pagination,
                'firstPageLabel' => 'หน้าแรก',
                'lastPageLabel' => 'หน้าสุดท้าย',
                'options' => [
                    'class' => 'pagination pagination-sm mb-0',
                ],
            ]); ?>
        </div>
    </div>
</div>

<style>
.vehicle-list-table thead.vehicle-list-thead th {
    position: sticky;
    top: 0;
    z-index: 2;
    background-color: var(--bs-tertiary-bg, #f8f9fa);
    color: var(--bs-secondary-color);
    font-size: 0.8125rem;
    font-weight: 600;
    letter-spacing: 0.04em;
    border-bottom: 1px solid var(--bs-border-color, #dee2e6);
    padding: .9rem 1rem;
    white-space: nowrap;
}
.vehicle-list-table tbody td {
    vertical-align: middle;
    padding: 1rem 1rem;
    font-size: 1rem;
    color: var(--bs-body-color);
    border-bottom: 1px solid var(--bs-border-color, #dee2e6);
}
.vehicle-list-table tbody tr:last-child td { border-bottom: none; }
.vehicle-list-table .vlt-primary { color: var(--bs-emphasis-color, #212529); font-weight: 600; }
.vehicle-list-table .vlt-secondary { font-size: .8125rem; color: var(--bs-secondary-color); }
.vehicle-list-table .vlt-meta { font-size: .75rem; color: var(--bs-secondary-color); }
.vehicle-list-table tbody tr {
    transition: background-color 150ms cubic-bezier(.16,1,.3,1);
}
.vehicle-list-table .min-w-0 { min-width: 0; }

.vehicle-list-table th.vehicle-actions-th,
.vehicle-list-table td.vehicle-actions-cell { width: 150px; white-space: nowrap; }

.vehicle-list-table .vehicle-actions-inner .btn {
    flex-shrink: 0;
    min-width: 2.375rem;
    min-height: 2.375rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
}
.vehicle-list-table .vehicle-actions-inner .action-dropdown-toggle::after {
    display: none;
}
.vehicle-list-table .dropdown-menu {
    font-size: .9rem;
    min-width: 12rem;
    z-index: 1055;
}
.vehicle-list-table .dropdown-item { padding: .45rem .85rem; }
.vehicle-list-table .vehicle-actions-cell { position: relative; }
@media (prefers-reduced-motion: reduce) {
    .vehicle-list-table tbody tr { transition: none; }
}
</style>
<?php
$js = <<< JS
function initVehicleWorkOfficialDropdowns() {
    if (!window.bootstrap || !window.bootstrap.Dropdown) return;
    document.querySelectorAll('.vehicle-list-table .action-dropdown-toggle').forEach(function (el) {
        var existing = bootstrap.Dropdown.getInstance(el);
        if (existing) {
            try { existing.dispose(); } catch (e) {}
        }
        new bootstrap.Dropdown(el, { popperConfig: { strategy: 'fixed' } });
    });
}
initVehicleWorkOfficialDropdowns();
\$(document).off('pjax:success.vehicleWorkOfficialDropdowns').on('pjax:success.vehicleWorkOfficialDropdowns', initVehicleWorkOfficialDropdowns);
JS;
$this->registerJS($js, yii\web\View::POS_READY);
?>
