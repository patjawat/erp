<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use app\models\Categorise;

/** @var yii\web\View $this */
/** @var array $driverGroups รายชื่อผู้รับผิดชอบพร้อมรถที่ดูแล */
/** @var array $idleDrivers พนักงานขับรถที่ยังไม่มีรถในความรับผิดชอบ */

$carTypeList = ArrayHelper::map(Categorise::find()->where(['name' => 'car_type'])->all(), 'code', 'title');

// ชื่อเรียกประเภทรถ เช่น รถยนต์ / รถพยาบาล / รถจักรยานยนต์
$vehicleLabel = static function ($item) use ($carTypeList): string {
    $name = (string) ($item->asset_name ?? '');
    if (mb_strpos($name, 'จักรยานยนต์') !== false) {
        return 'รถจักรยานยนต์';
    }
    if (mb_strpos($name, 'พยาบาล') !== false) {
        return 'รถพยาบาล';
    }
    if (!empty($item->car_type) && isset($carTypeList[$item->car_type])) {
        return $carTypeList[$item->car_type];
    }
    return 'รถยนต์';
};

$vehicleIcon = static function ($item) use ($vehicleLabel): string {
    $label = $vehicleLabel($item);
    if ($label === 'รถจักรยานยนต์') {
        return 'bi-scooter';
    }
    if ($label === 'รถพยาบาล') {
        return 'bi-truck-front';
    }
    return 'bi-car-front';
};

$totalVehicles = 0;
foreach ($driverGroups as $group) {
    $totalVehicles += count($group['vehicles']);
}
?>

<div class="card mb-3">
    <div class="card-header bg-primary-gradient text-white">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="text-white mb-0">
                <i class="bi bi-person-badge"></i> พนักงานขับรถและรถยนต์ที่รับผิดชอบ
                <span class="badge text-bg-light ms-1"><?= count($driverGroups) ?></span> คน
                <span class="badge text-bg-light ms-1"><?= number_format($totalVehicles) ?></span> คัน
            </h6>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($driverGroups)): ?>
            <div class="text-body-secondary text-center py-4">
                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                ยังไม่มีการระบุผู้รับผิดชอบรถยนต์
            </div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-xxl-3 g-3">
                <?php foreach ($driverGroups as $group): ?>
                    <?php $employee = $group['employee']; ?>
                    <div class="col">
                        <div class="card h-100 border shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <?php if ($employee !== null): ?>
                                        <?= Html::img($employee->ShowAvatar(), [
                                            'class' => 'rounded-circle border flex-shrink-0',
                                            'style' => 'width:52px;height:52px;object-fit:cover;',
                                            'alt' => $employee->fullname,
                                            'onerror' => "this.onerror=null;this.src='" . Yii::getAlias('@web') . "/img/placeholder_cid.png';",
                                        ]) ?>
                                    <?php else: ?>
                                        <span class="rounded-circle border d-inline-flex align-items-center justify-content-center bg-body-secondary text-body-secondary"
                                            style="width:52px;height:52px;">
                                            <i class="bi bi-question-lg fs-5"></i>
                                        </span>
                                    <?php endif; ?>
                                    <div class="flex-grow-1" style="min-width:0;">
                                        <h6 class="mb-0 text-truncate">
                                            <?= Html::encode($employee !== null ? $employee->fullname : 'ยังไม่ระบุผู้รับผิดชอบ') ?>
                                        </h6>
                                        <div class="small text-body-secondary text-truncate">
                                            <?php if ($employee !== null): ?>
                                                <?= $employee->positionName() ?: 'ไม่ระบุตำแหน่ง' ?>
                                            <?php else: ?>
                                                รถที่ยังไม่ได้กำหนดผู้ดูแล
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($employee !== null && empty($group['isDriver'])): ?>
                                            <span class="badge rounded-pill text-bg-light border mt-1 fw-normal">ไม่ได้อยู่ในสิทธิพนักงานขับรถ</span>
                                        <?php endif; ?>
                                    </div>
                                    <span class="badge rounded-pill text-bg-primary align-self-start">
                                        <?= count($group['vehicles']) ?> คัน
                                    </span>
                                </div>

                                <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                                    <?php foreach ($group['vehicles'] as $item): ?>
                                        <li class="d-flex gap-2 align-items-start border-top pt-2">
                                            <i class="bi <?= $vehicleIcon($item) ?> text-primary mt-1"></i>
                                            <div class="flex-grow-1" style="min-width:0;">
                                                <a href="<?= Url::to(['/booking/asset/view', 'id' => $item->id]) ?>"
                                                    class="text-decoration-none fw-semibold" data-pjax="0">
                                                    <?= Html::encode($vehicleLabel($item)) ?> ทะเบียน
                                                    <?= Html::encode(trim((string) $item->license_plate)) ?>
                                                </a>
                                                <div class="small text-body-secondary text-truncate">
                                                    <?= Html::encode((string) $item->asset_name) ?>
                                                </div>
                                                <div class="small text-body-tertiary font-monospace">
                                                    <?= Html::encode((string) $item->code) ?>
                                                </div>
                                            </div>
                                            <span class="flex-shrink-0"><?= $item->getStatusBadge() ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($idleDrivers)): ?>
            <div class="border-top mt-3 pt-3">
                <div class="small text-body-secondary mb-2">
                    <i class="bi bi-person-dash"></i> พนักงานขับรถที่ยังไม่มีรถในความรับผิดชอบ
                    (<?= count($idleDrivers) ?> คน)
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($idleDrivers as $driver): ?>
                        <span class="badge rounded-pill text-bg-light border fw-normal">
                            <?= Html::encode($driver->fullname) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
