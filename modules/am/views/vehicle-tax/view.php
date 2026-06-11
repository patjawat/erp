<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\AppHelper;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetDetail $model */

\yii\web\YiiAsset::register($this);

$dataJson = is_array($model->data_json) ? $model->data_json : [];

$fmtDate = function ($date) {
    if (empty($date)) {
        return '-';
    }
    try {
        return Yii::$app->thaiFormatter->asDate($date, 'medium');
    } catch (\Throwable $th) {
        return AppHelper::DateFormDb($date);
    }
};

$asset = $model->asset;
?>
<div class="vehicle-tax-view">

    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-light text-dark border rounded px-3 py-2"
                        style="font-size:14px;font-weight:600;">
                        <?= Html::encode($asset->license_plate ?? $model->code) ?>
                    </span>
                    <div>
                        <div class="fw-semibold"><?= Html::encode($asset->asset_name ?? '-') ?></div>
                        <div class="text-muted small">รหัสครุภัณฑ์: <?= Html::encode($model->code) ?></div>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <?= Html::a(
                        '<i class="fa-solid fa-plus"></i> เพิ่มรายการใหม่',
                        ['create', 'code' => $model->code, 'title' => '<i class="fa-solid fa-tag"></i> เพิ่มข้อมูลการต่อภาษี/พ.ร.บ./ประกัน'],
                        ['class' => 'btn btn-primary btn-sm open-modal', 'data' => ['size' => 'modal-lg']]
                    ) ?>
                    <?= Html::a(
                        '<i class="fa-regular fa-pen-to-square"></i> แก้ไขรายการนี้',
                        ['update', 'id' => $model->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไขข้อมูลการต่อภาษี/พ.ร.บ./ประกัน'],
                        ['class' => 'btn btn-outline-secondary btn-sm open-modal', 'data' => ['size' => 'modal-lg']]
                    ) ?>
                </div>
            </div>
        </div>
    </div>

    <h6 class="mt-3"><i class="fa-solid fa-tag"></i> ข้อมูลการต่อภาษี</h6>
    <table class="table table-sm">
        <tbody>
            <tr>
                <th style="width:30%;">วันที่ต่อภาษี</th>
                <td><?= $fmtDate($model->date_start) ?></td>
            </tr>
            <tr>
                <th>ครบกำหนด</th>
                <td><?= $fmtDate($model->date_end) ?></td>
            </tr>
            <tr>
                <th>ค่าภาษี</th>
                <td><?= isset($dataJson['price']) ? Html::encode($dataJson['price']) . ' บาท' : '-' ?></td>
            </tr>
        </tbody>
    </table>

    <h6 class="mt-3"><i class="fa-solid fa-user-injured"></i> พ.ร.บ.</h6>
    <table class="table table-sm">
        <tbody>
            <tr>
                <th style="width:30%;">บริษัท</th>
                <td><?= Html::encode($dataJson['company1'] ?? '-') ?></td>
            </tr>
            <tr>
                <th>กรมธรรม์เลขที่</th>
                <td><?= Html::encode($dataJson['number1'] ?? '-') ?></td>
            </tr>
            <tr>
                <th>เบี้ยประกัน</th>
                <td><?= isset($dataJson['price1']) ? Html::encode($dataJson['price1']) . ' บาท' : '-' ?></td>
            </tr>
            <tr>
                <th>วันที่</th>
                <td><?= $fmtDate($dataJson['date_start1'] ?? null) ?> ถึง <?= $fmtDate($dataJson['date_end1'] ?? null) ?></td>
            </tr>
            <tr>
                <th>ตัวแทน</th>
                <td><?= Html::encode($dataJson['sale1'] ?? '-') ?></td>
            </tr>
            <tr>
                <th>โทร</th>
                <td><?= Html::encode($dataJson['phone1'] ?? '-') ?></td>
            </tr>
        </tbody>
    </table>

    <h6 class="mt-3"><i class="fa-solid fa-car-burst"></i> ประกันภัยรถ</h6>
    <table class="table table-sm">
        <tbody>
            <tr>
                <th style="width:30%;">บริษัท</th>
                <td><?= Html::encode($dataJson['company2'] ?? '-') ?></td>
            </tr>
            <tr>
                <th>กรมธรรม์เลขที่</th>
                <td><?= Html::encode($dataJson['number2'] ?? '-') ?></td>
            </tr>
            <tr>
                <th>เบี้ยประกัน</th>
                <td><?= isset($dataJson['price2']) ? Html::encode($dataJson['price2']) . ' บาท' : '-' ?></td>
            </tr>
            <tr>
                <th>วันที่</th>
                <td><?= $fmtDate($dataJson['date_start2'] ?? null) ?> ถึง <?= $fmtDate($dataJson['date_end2'] ?? null) ?></td>
            </tr>
            <tr>
                <th>ตัวแทน</th>
                <td><?= Html::encode($dataJson['sale2'] ?? '-') ?></td>
            </tr>
            <tr>
                <th>โทร</th>
                <td><?= Html::encode($dataJson['phone2'] ?? '-') ?></td>
            </tr>
        </tbody>
    </table>

    <label class="form-label fw-bold mt-3">รูปภาพหรือไฟล์ที่เกี่ยวข้อง</label>
    <?= $model->Upload(['view' => true]) ?>
</div>
