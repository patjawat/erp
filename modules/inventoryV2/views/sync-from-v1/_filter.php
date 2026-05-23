<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\select2\Select2;

/** @var yii\web\View $this */
/** @var string $dateFrom */
/** @var string $dateTo */
/** @var int|null $whId */
/** @var array $warehouseOptions */
/** @var string $action */
?>

<form method="get" action="<?= Url::to([$action]) ?>" class="row g-2 align-items-end bg-light p-3 rounded mb-3">
    <div class="col-12 col-md-3">
        <label class="form-label small mb-1">ตั้งแต่วันที่</label>
        <input type="date" class="form-control" name="date_from" value="<?= Html::encode($dateFrom) ?>">
    </div>
    <div class="col-12 col-md-3">
        <label class="form-label small mb-1">ถึงวันที่</label>
        <input type="date" class="form-control" name="date_to" value="<?= Html::encode($dateTo) ?>">
    </div>
    <div class="col-12 col-md-4">
        <label class="form-label small mb-1">คลังหลัก (V1)</label>
        <?= Select2::widget([
            'name' => 'warehouse_id',
            'value' => $whId,
            'data' => $warehouseOptions,
            'options' => ['placeholder' => 'ทุกคลัง'],
            'pluginOptions' => ['allowClear' => true],
        ]) ?>
    </div>
    <div class="col-12 col-md-2">
        <button type="submit" class="btn btn-primary w-100">
            <i class="fa-solid fa-magnifying-glass"></i> ค้นหา
        </button>
    </div>
</form>
