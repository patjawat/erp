<?php

use yii\helpers\Html;
use app\components\ThaiDateHelper;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var int $fiscalYear */

$this->title = 'รายงานค่าเสื่อม (ชุดใหม่)';
$this->params['breadcrumbs'][] = ['label' => 'ระบบบริหารทรัพย์สิน', 'url' => ['/am']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container-fluid px-2 px-md-3 pb-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><?= Html::encode($this->title) ?></h4>
        <?= Html::a('<i class="fa-solid fa-file-csv me-1"></i> Export CSV', ['depreciation-report', 'format' => 'csv'] + $this->context->request->queryParams, ['class' => 'btn btn-outline-primary']) ?>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php $models = $dataProvider->getModels(); ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>รหัส</th>
                            <th>ชื่อ</th>
                            <th class="text-end">ราคาทุน</th>
                            <th class="text-end">มูลค่าซาก</th>
                            <th class="text-center">อายุ(ปี)</th>
                            <th class="text-end">ค่าเสื่อม/ปี</th>
                            <th>วันที่รับ</th>
                        </tr>
                    </thead>
                    <tbody class="align-middle table-group-divider">
                        <?php foreach ($models as $a): ?>
                        <?php $annual = \app\modules\am\services\AssetDepreciationService::getAnnualDepreciationForAsset($a); ?>
                        <tr>
                            <td><?= Html::encode($a->code ?? '') ?></td>
                            <td><?= Html::encode($a->asset_name ?? $a->AssetitemName() ?? '') ?></td>
                            <td class="text-end"><?= number_format((float) ($a->price ?? 0), 2) ?></td>
                            <td class="text-end">1.00</td>
                            <td class="text-center"><?= (int) ($a->useful_life ?? 0) ?></td>
                            <td class="text-end"><?= $annual !== null ? number_format($annual, 2) : '-' ?></td>
                            <td><?= $a->receive_date ? ThaiDateHelper::formatThaiDate($a->receive_date) : '' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?= \yii\bootstrap5\LinkPager::widget(['pagination' => $dataProvider->pagination]) ?>
        </div>
    </div>
</div>
