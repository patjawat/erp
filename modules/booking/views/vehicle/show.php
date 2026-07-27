<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\Vehicle $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Vehicles', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

// ผู้ร่วมเดินทาง: resolve จาก single source บน model (Vehicle::companions())
$companions = $model->companions();
?>

<div class="vehicle-show">
    <section class="vehicle-show-summary p-3 rounded-3 mb-3">
        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill fw-medium px-2 py-1">
                <i class="fa-solid fa-hashtag me-1"></i>เลขที่คำขอ <?= Html::encode($model->code) ?>
            </span>
        </div>
        <p class="mb-0 text-body-emphasis">
            <span class="fw-semibold"><?= Html::encode($model->userRequest()['fullname']) ?></span>
            ขอใช้<span class="fw-semibold"><?= Html::encode($model->carType->title) ?></span>
            ไป<span class="fw-semibold"><?= Html::encode($model->locationOrg?->title ?? '-') ?></span>
            วันที่ <span class="fw-semibold"><?= $model->showDateRange() ?></span>
        </p>
    </section>

    <section class="vehicle-show-allocation">
        <h6 class="mb-2 fw-semibold text-body-emphasis">
            <i class="fa-solid fa-car me-2 text-primary"></i>การจัดสรรรถ
        </h6>
        <div class="table-responsive">
            <table class="table align-middle mb-0 vehicle-show-table" id="details-table">
                <thead>
                    <tr class="small text-muted">
                        <th scope="col">วันที่</th>
                        <th scope="col">รถ</th>
                        <th scope="col">พนักงานขับรถ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($model->vehicleDetails ?? [] as $index => $detail): ?>
                        <tr class="detail-row">
                            <td>
                                <span class="small fw-medium text-body-emphasis"><?= $detail->showDate() ?></span>
                                <input type="hidden" name="vehicleDetails[<?= $index ?>][id]" value="<?= $detail->id ?>">
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <?= Html::img($detail->car?->ShowImg(), ['class' => 'avatar rounded border', 'alt' => 'รถ']) ?>
                                    <div class="d-flex flex-column min-w-0">
                                        <span class="small text-body-emphasis text-truncate"><?= Html::encode($detail->car?->data_json['brand'] ?? '-') ?></span>
                                        <span class="small fw-semibold text-primary"><?= Html::encode($detail->license_plate ?? '-') ?></span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?= $detail->driver?->getAvatar(false, ($detail->driver->phone)) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($model->vehicleDetails)): ?>
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">
                                <i class="fa-solid fa-inbox d-block mb-1"></i>
                                ยังไม่ได้จัดสรรรถและพนักงานขับ
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <?php if ($companions['count'] > 0): ?>
        <section class="vehicle-show-companions mt-3 pt-3 border-top">
            <h6 class="mb-2 fw-semibold text-body-emphasis">
                <i class="fa-solid fa-user-group me-2 text-primary"></i>ผู้ร่วมเดินทาง
                <span class="text-muted fw-normal small">(<?= (int) $companions['count'] ?> คน)</span>
            </h6>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($companions['items'] as $companion): ?>
                    <?php $emp = $companion['employee']; ?>
                    <span class="vs-companion">
                        <?php if ($emp): ?>
                            <img src="<?= Html::encode($emp->showAvatar()) ?>" class="vs-companion__avatar" alt="" loading="lazy">
                        <?php else: ?>
                            <span class="vs-companion__avatar vs-companion__avatar--guest"><i class="fa-solid fa-user"></i></span>
                        <?php endif; ?>
                        <span class="vs-companion__name text-body-emphasis"><?= Html::encode($companion['name']) ?></span>
                    </span>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if (!empty($model->data_json['remarks'])): ?>
        <section class="vehicle-show-remarks mt-3 pt-3 border-top">
            <h6 class="mb-2 fw-semibold text-body-emphasis">
                <i class="fa-regular fa-note-sticky me-2 text-secondary"></i>หมายเหตุ
            </h6>
            <div class="small text-muted"><?= $model->data_json['remarks'] ?></div>
        </section>
    <?php endif; ?>
</div>

<style>
.vehicle-show .vehicle-show-summary {
    background-color: var(--bs-tertiary-bg, #f8f9fa);
    border: 1px solid var(--bs-border-color-translucent, rgba(0,0,0,.08));
}
.vehicle-show-table thead th {
    background-color: var(--bs-tertiary-bg, #f8f9fa);
    font-weight: 600;
    letter-spacing: 0.04em;
    border-bottom: 1px solid var(--bs-border-color, #dee2e6);
    padding-top: .55rem;
    padding-bottom: .55rem;
    white-space: nowrap;
}
.vehicle-show-table tbody td {
    vertical-align: middle;
    padding-top: .65rem;
    padding-bottom: .65rem;
    border-bottom: 1px solid var(--bs-border-color-translucent, rgba(0,0,0,.075));
}
.vehicle-show .min-w-0 { min-width: 0; }
.vehicle-show .vs-companion {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .25rem .6rem .25rem .25rem;
    border: 1px solid var(--bs-border-color-translucent, rgba(0,0,0,.08));
    border-radius: 999px;
    background: var(--bs-body-bg, #fff);
}
.vehicle-show .vs-companion__avatar {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
}
.vehicle-show .vs-companion__avatar--guest {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--bs-tertiary-bg, #f1f3f5);
    color: var(--bs-secondary-color, #6c757d);
    font-size: .75rem;
}
.vehicle-show .vs-companion__name { font-size: .8125rem; font-weight: 500; }
</style>
