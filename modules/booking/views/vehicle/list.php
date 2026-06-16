<?php

use yii\web\View;
use yii\helpers\Html;

$models = $dataProvider->getModels();
?>
<div class="vehicle-list-wrap">
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
                    <th scope="col" class="pe-3 text-center vehicle-actions-th">การจัดการ</th>
                </tr>
            </thead>
            <tbody>
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
                    <tr>
                        <td class="ps-3">
                            <div class="text-primary fw-bold lh-sm">#<?= (($dataProvider->pagination->offset + 1) + $key) ?></div>
                            <div class="vlt-primary lh-sm" title="รหัสขอใช้รถ"><?= Html::encode($item->code) ?></div>
                        </td>

                        <td>
                            <?php if ($item->is_shared == 1): ?>
                                <span class="badge bg-info bg-opacity-10 text-info border border-info-subtle rounded-pill fw-medium px-2 py-1">
                                    <i class="fa-solid fa-user-group me-1"></i>จัดสรรร่วม
                                </span>
                            <?php else: ?>
                                <?= $item->viewStatus()['view'] ?? '-' ?>
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
                                        'src' => $requester['photo']
                                    ]
                                ]); ?>
                                <div class="min-w-0">
                                    <div class="vlt-primary text-truncate"><?= Html::encode($requester['fullname']) ?></div>
                                    <div class="vlt-secondary text-primary text-truncate"><?= Html::encode($requester['department']) ?></div>
                                    <div class="vlt-meta">
                                        <i class="bi bi-calendar-check me-1"></i>จองเมื่อ <?= Html::encode($created['full'] ?? '-') ?>
                                        <span class="ms-1">· ผ่านมา <?= number_format($daysPassed) ?> วัน</span>
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td class="d-none d-md-table-cell">
                            <div class="vlt-primary text-truncate" style="max-width: 240px;" title="<?= Html::encode($item->reason) ?>">
                                <?= Html::encode($item->reason) ?>
                            </div>
                            <div class="vlt-secondary mt-1">ความเร่งด่วน <?= $item->viewUrgent() ?></div>
                        </td>

                        <td class="d-none d-md-table-cell">
                            <div class="vlt-primary text-truncate" style="max-width: 200px;" title="<?= Html::encode($item->locationOrg?->title ?? '-') ?>">
                                <i class="bi bi-geo-alt text-danger me-1"></i><?= Html::encode($item->locationOrg?->title ?? '-') ?>
                            </div>
                        </td>

                        <td class="d-none d-sm-table-cell">
                            <div class="vlt-primary"><?= $item->showDateRange() ?></div>
                            <div class="vlt-secondary"><?= $item->viewTime()['full'] ?></div>
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
                                <?php foreach ($item->vehicleDetails as $detail): ?>
                                    <?php if (!empty($detail->driver)): ?>
                                        <?= Html::a(
                                            Html::img('@web/img/loading.gif', [
                                                'class' => 'avatar-sm rounded-circle shadow-sm lazyload',
                                                'data' => [
                                                    'expand' => '-20',
                                                    'sizes' => 'auto',
                                                    'src' => $detail->driver->showAvatar(),
                                                ],
                                            ]),
                                            ['/booking/vehicle/work-update', 'id' => $detail->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> บันทึกภาระกิจการใช้รถยนต์'],
                                            ['class' => 'open-modal', 'data' => ['size' => 'modal-lg'], 'title' => 'บันทึกภาระกิจการใช้รถ']
                                        ) ?>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </td>

                        <td class="text-center align-middle vehicle-actions-cell px-2 px-md-3">
                            <div class="vehicle-actions-inner d-flex flex-row flex-wrap justify-content-center align-items-center gap-2">
                                <?= Html::a('<i class="fa-regular fa-eye"></i>',
                                    ['/booking/vehicle/view', 'id' => $item->id],
                                    ['class' => 'btn btn-sm btn-primary open-modal', 'data' => ['size' => 'modal-lg'], 'title' => 'แสดงรายละเอียด', 'aria-label' => 'แสดงรายละเอียด']) ?>
                                <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>',
                                    ['/booking/vehicle/update', 'id' => $item->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไขการจงรถ'],
                                    ['class' => 'btn btn-sm btn-warning open-modal', 'data' => ['size' => 'modal-xl'], 'title' => 'แก้ไข', 'aria-label' => 'แก้ไข']) ?>

                                <div class="dropdown flex-grow-1 flex-sm-grow-0">
                                    <button class="btn btn-sm btn-secondary dropdown-toggle action-dropdown-toggle w-100 w-sm-auto"
                                            type="button"
                                            data-bs-toggle="dropdown"
                                            aria-expanded="false"
                                            aria-label="เมนูเพิ่มเติม">
                                        <i class="fa-solid fa-angle-down"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                        <li>
                                            <?= Html::a('<i class="fa-solid fa-print me-2 text-dark"></i>พิมพ์ใบขอใช้รถ',
                                                ['/booking/vehicle/print', 'id' => $item->id, 'title' => 'ใบขอใช้รถยนต์'],
                                                ['class' => 'dropdown-item', 'target' => '_blank', 'rel' => 'noopener noreferrer', 'data-pjax' => '0']) ?>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <?= Html::a('<i class="fa-regular fa-circle-xmark me-2"></i>ยกเลิกรายการ',
                                                ['/booking/vehicle/cancel', 'id' => $item->id],
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
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="fa-solid fa-inbox fs-3 d-block mb-2"></i>
                            ไม่พบรายการขอใช้รถยนต์
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
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
    z-index: 1080;
}
.vehicle-list-table .dropdown-item { padding: .45rem .85rem; }
.vehicle-list-table .vehicle-actions-cell { position: relative; }
.vehicle-list-wrap { overflow: visible; }
@media (prefers-reduced-motion: reduce) {
    .vehicle-list-table tbody tr { transition: none; }
}
</style>
