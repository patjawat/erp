<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use app\components\SiteHelper;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'ทะเบียนครุภัณฑ์ที่จำหน่ายแล้ว';
$this->params['breadcrumbs'][] = ['label' => 'ระบบบริหารทรัพย์สิน', 'url' => ['/am']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="asset-disposal-index container-fluid px-2 px-md-3 pb-3">

    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-3 w-100 mb-3">
        <h4 class="fw-semibold text-body d-flex align-items-center gap-2 mb-0">
            <span class="text-danger"><i class="fa-solid fa-trash-can"></i></span>
            <?= Html::encode($this->title) ?>
        </h4>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php Pjax::begin(); ?>

            <div class="table-responsive">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'summary' => 'แสดง {begin} - {end} จากทั้งหมด <b>{totalCount}</b> รายการ',
                    'tableOptions' => ['class' => 'table table-hover table-bordered'],
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],

                        'code',
                        'asset_name',
                        [
                            'attribute' => 'department',
                            'value' => function ($model) {
                                return $model->departmentName ?? '-';
                            }
                        ],
                        'price:currency',
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{view}',
                            'buttons' => [
                                'view' => function ($url, $model) {
                                    // Link to the main asset view instead of a dedicated disposal view, or disposal view
                                    return Html::a('<i class="fa-regular fa-eye"></i>', ['/am/equip/view-asset', 'id' => $model->id], [
                                        'title' => 'ดูรายละเอียด',
                                        'class' => 'btn btn-sm btn-outline-primary',
                                        'data-pjax' => 0
                                    ]);
                                }
                            ]
                        ],
                    ],
                ]); ?>
            </div>

            <?php Pjax::end(); ?>
        </div>
    </div>
</div>
