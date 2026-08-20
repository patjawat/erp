<?php

use yii\helpers\Html;
use app\modules\hr\models\Employees;

/** @var app\modules\booking\models\VehicleDetail $model */

$vehicle = $model->vehicle;
$driverName = '-';
if ($model->driver_id && ($driver = Employees::findOne($model->driver_id))) {
    $driverName = (string) ($driver->fullname ?? '-');
}
$rows = [
    ['bi-hash', 'รหัสคำขอ', (string) ($vehicle->code ?? '-')],
    ['bi-calendar-check', 'วันที่เดินทาง', $model->showDate() . ' ' . ($model->viewTime()['full'] ?? '')],
    ['bi-geo-alt', 'สถานที่ไป', (string) ($vehicle->locationOrg?->title ?? '-')],
    ['bi-card-text', 'วัตถุประสงค์', (string) ($vehicle->reason ?? '-')],
    ['bi-car-front', 'ทะเบียนรถ', (string) ($model->license_plate ?? '-')],
    ['bi-person-badge', 'พนักงานขับรถ', $driverName],
];
?>
<div class="bg-light rounded-3 p-3">
    <div class="row g-2">
        <?php foreach ($rows as [$icon, $label, $value]): ?>
            <div class="col-12 col-md-6">
                <div class="small text-muted"><i class="bi <?= $icon ?> me-1"></i><?= Html::encode($label) ?></div>
                <div class="fw-medium"><?= Html::encode($value !== '' ? $value : '-') ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
