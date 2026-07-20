<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use app\modules\am\models\DepreciationProfile;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'เกณฑ์ค่าเสื่อมราคา';
$statusBadge = static function ($s) {
    $map = [
        DepreciationProfile::STATUS_ACTIVE => 'success',
        DepreciationProfile::STATUS_INACTIVE => 'secondary',
        DepreciationProfile::STATUS_DRAFT => 'warning',
    ];
    $labels = DepreciationProfile::statusOptions();
    return '<span class="badge bg-' . ($map[$s] ?? 'secondary') . '">' . ($labels[$s] ?? $s) . '</span>';
};

$this->params['breadcrumbs'][] = ['label' => 'ค่าเสื่อมราคา', 'url' => ['/am/asset-depreciation/overview']];
$this->params['breadcrumbs'][] = $this->title;

$this->beginBlock('page-title'); ?>
<h4 class="fw-semibold text-body d-flex align-items-center gap-2 mb-0">
    <span class="text-primary"><i data-lucide="percent"></i></span>
    <?= Html::encode($this->title) ?>
</h4>
<?php $this->endBlock();

$this->beginBlock('action'); ?>
<?= Html::a('<i data-lucide="plus"></i> เพิ่มเกณฑ์', ['create'], ['class' => 'btn btn-primary']) ?>
<?= $this->render('@app/modules/am/menu', ['active' => 'depreciation']) ?>
<?php $this->endBlock(); ?>
<div class="container-fluid py-3">

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success"><?= Yii::$app->session->getFlash('success') ?></div>
    <?php endif; ?>
    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger"><?= Yii::$app->session->getFlash('error') ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'tableOptions' => ['class' => 'table table-hover align-middle'],
                'layout' => "{items}\n{pager}",
                'columns' => [
                    ['attribute' => 'code', 'label' => 'รหัส'],
                    ['attribute' => 'name', 'label' => 'ชื่อเกณฑ์'],
                    [
                        'label' => 'วิธี',
                        'value' => fn($m) => DepreciationProfile::methodOptions()[$m->method] ?? $m->method,
                    ],
                    [
                        'label' => 'อายุ (เดือน)',
                        'value' => fn($m) => $m->useful_life_months ?: '-',
                        'contentOptions' => ['class' => 'text-end'],
                    ],
                    [
                        'label' => 'อัตรา/ปี',
                        'value' => fn($m) => $m->annual_rate !== null ? number_format($m->annual_rate, 2) . '%' : '-',
                        'contentOptions' => ['class' => 'text-end'],
                    ],
                    [
                        'label' => 'ฐานคำนวณ',
                        'value' => fn($m) => DepreciationProfile::basisOptions()[$m->calculation_basis] ?? $m->calculation_basis,
                    ],
                    [
                        'label' => 'สถานะ',
                        'format' => 'raw',
                        'value' => fn($m) => $statusBadge($m->status),
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{view} {update} {delete}',
                        'urlCreator' => fn($action, $model) => Url::to([$action, 'id' => $model->id]),
                        'buttons' => [
                            'delete' => fn($url) => Html::a('<i data-lucide="trash-2"></i>', $url, [
                                'class' => 'text-danger',
                                'data' => ['method' => 'post', 'confirm' => 'ยืนยันการลบเกณฑ์นี้?'],
                            ]),
                        ],
                    ],
                ],
            ]) ?>
        </div>
    </div>
</div>
