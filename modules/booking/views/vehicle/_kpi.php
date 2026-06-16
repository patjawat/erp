<?php
/**
 * KPI summary cards — pattern เดียวกับ am/equip/kpi_summary.php
 *
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var array $statusSummary
 * @var int   $waitingAllocationCount
 * @var int   $allocatedCount
 */

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

$waitingCount = (int) ($waitingAllocationCount ?? 0);
$allocCount   = (int) ($allocatedCount ?? 0);
?>

<div class="row g-3 mt-1 vehicle-kpi">
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body py-3">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <div class="d-flex flex-column gap-2">
                        <span class="fw-bold fs-3 text-dark"><?= number_format($waitingCount) ?></span>
                        <span class="text-danger">รอจัดสรร (รายการ)</span>
                    </div>
                    <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-pill" aria-hidden="true">
                        <i class="fa-solid fa-clock-rotate-left fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-xl-3">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body py-3">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <div class="d-flex flex-column gap-2">
                        <span class="fw-bold fs-3 text-dark"><?= number_format($allocCount) ?></span>
                        <span class="text-success">จัดสรรแล้ว (รายการ)</span>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success p-3 rounded-pill" aria-hidden="true">
                        <i class="fa-solid fa-circle-check fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-xl-3">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body py-3">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <div class="d-flex flex-column gap-2">
                        <span class="fw-bold fs-3 text-dark"><?= number_format($cancelledCount) ?></span>
                        <span class="text-secondary">ยกเลิก (รายการ)</span>
                    </div>
                    <div class="bg-secondary bg-opacity-10 text-secondary p-3 rounded-pill" aria-hidden="true">
                        <i class="fa-solid fa-ban fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-xl-3">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body py-3">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <div class="d-flex flex-column gap-2">
                        <span class="fw-bold fs-3 text-dark">
                            <?= number_format($loggedItemsCount) ?>
                            <span class="fs-6 text-muted fw-medium">/ <?= number_format($loggedItemsCount + $notLoggedItemsCount) ?></span>
                        </span>
                        <span class="text-info">บันทึกการเดินทาง (รายการ)</span>
                    </div>
                    <div class="bg-info bg-opacity-10 text-info p-3 rounded-pill" aria-hidden="true">
                        <i class="fa-solid fa-route fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
