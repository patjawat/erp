<?php

use yii\helpers\Html;
use app\components\ThaiDateHelper;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'รายงานทะเบียนครุภัณฑ์';
$this->params['breadcrumbs'][] = ['label' => 'ระบบบริหารทรัพย์สิน', 'url' => ['/am']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container-fluid px-2 px-md-3 pb-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><?= Html::encode($this->title) ?></h4>
        <?= Html::a('<i class="fa-solid fa-file-csv me-1"></i> Export CSV', ['register', 'format' => 'csv'] + $this->context->request->queryParams, ['class' => 'btn btn-outline-primary']) ?>
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
                            <th>ประเภท</th>
                            <th>หน่วยงาน</th>
                            <th>วันที่รับ</th>
                            <th class="text-end">ราคา</th>
                            <th>สถานะ</th>
                        </tr>
                    </thead>
                    <tbody class="align-middle table-group-divider">
                        <?php foreach ($models as $a): ?>
                        <tr>
                            <td><?= Html::encode($a->code ?? '') ?></td>
                            <td><?= Html::encode($a->asset_name ?? $a->AssetitemName() ?? '') ?></td>
                            <td><?= Html::encode($a->type_name ?? '') ?></td>
                            <td><?= Html::encode($a->departmentName() ?? '') ?></td>
                            <td><?= $a->receive_date ? ThaiDateHelper::formatThaiDate($a->receive_date) : '' ?></td>
                            <td class="text-end"><?= isset($a->price) ? number_format((float) $a->price, 2) : '' ?></td>
                            <td><?= Html::encode($a->lifecycle_status ?? $a->asset_status ?? '') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?= \yii\bootstrap5\LinkPager::widget(['pagination' => $dataProvider->pagination]) ?>
        </div>
    </div>
</div>
