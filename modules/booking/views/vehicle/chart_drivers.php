<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\VehicleSearch $searchModel */

$topLimit = 10;
$drivers = $searchModel->driverSummary($topLimit);
$maxTotal = !empty($drivers) ? max(array_map(fn($d) => (int) $d['total'], $drivers)) : 0;
$headingId = 'driver-workload-heading';
?>

<section class="card mb-3" aria-labelledby="<?= $headingId ?>">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
            <div>
                <h2 id="<?= $headingId ?>" class="h6 card-title mb-1">
                    <i class="fa-solid fa-user-tie text-primary" aria-hidden="true"></i> ปริมาณงานพนักงานขับรถ
                </h2>
                <div class="text-muted small">Top <?= $topLimit ?> · มากไปน้อย</div>
            </div>
            <?= $this->render('_search_year', ['model' => $searchModel]) ?>
        </div>

        <?php if (empty($drivers)): ?>
            <div class="text-muted text-center py-4 small">ยังไม่มีข้อมูลการจัดสรรงานพนักงานขับรถ</div>
        <?php else: ?>
            <ol class="d-flex flex-column gap-2 driver-workload-list list-unstyled mb-0" aria-label="อันดับพนักงานขับรถตามปริมาณงาน">
                <?php foreach ($drivers as $i => $d): ?>
                    <?php
                    $name = !empty($d['fullname']) ? $d['fullname'] : 'ไม่ระบุชื่อ (#' . $d['driver_id'] . ')';
                    $total = (int) $d['total'];
                    $pct = $maxTotal > 0 ? round(($total / $maxTotal) * 100) : 0;
                    $rank = $i + 1;

                    $badgeClass = match ($rank) {
                        1       => 'bg-warning text-dark',
                        2       => 'bg-secondary',
                        3       => 'bg-info',
                        default => 'bg-light text-dark border',
                    };

                    $driverUrl = Url::to([
                        '/booking/vehicle/driver-work',
                        'driver_id' => $d['driver_id'],
                        'thai_year' => $searchModel->thai_year,
                    ]);
                    $linkLabel = sprintf('ดูรายการภารกิจของ %s %s งาน', $name, number_format($total));
                    ?>
                    <li>
                        <a href="<?= $driverUrl ?>"
                            data-size="modal-xl"
                            aria-label="<?= Html::encode($linkLabel) ?>"
                            class="driver-workload-item d-flex align-items-center gap-3 p-2 rounded text-decoration-none text-body open-modal">
                            <span class="driver-rank-badge badge rounded-circle d-inline-flex justify-content-center align-items-center flex-shrink-0 <?= $badgeClass ?>"
                                aria-hidden="true">
                                <?= $rank ?>
                            </span>
                            <div class="flex-grow-1 driver-workload-body" aria-hidden="true">
                                <div class="d-flex justify-content-between align-items-center gap-2 mb-1">
                                    <span class="fw-semibold text-truncate"><?= Html::encode($name) ?></span>
                                    <span class="text-primary fw-bold flex-shrink-0">
                                        <?= number_format($total) ?> งาน
                                    </span>
                                </div>
                                <div class="progress driver-progress">
                                    <div class="progress-bar bg-primary" style="width:<?= $pct ?>%;"></div>
                                </div>
                            </div>
                            <i class="fa-solid fa-chevron-right text-muted small driver-workload-chevron" aria-hidden="true"></i>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>
    </div>
</section>
