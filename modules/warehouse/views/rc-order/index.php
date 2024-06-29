<?php

use app\modules\warehouse\models\Order;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\warehouse\models\OrderSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'รับสินค้า';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-cubes-stacked"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('../default/menu') ?>
<?php $this->endBlock(); ?>



<div class="order-index">

<div class="card">
    <div class="card-body">
        <?= Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใบรับสินค้า', ['/warehouse/rc-order/create', 'title' => '<i class="fa-solid fa-cubes-stacked"></i> ใบรับสินค้า'], ['class' => 'btn btn-primary open-modal', 'data' => ['size' => 'modal-md']]) ?>
    </div>
</div>


    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'code',
                'header' => 'เลขที่รับสิ้นค้า',
                'value' => function ($model) {
                    return $model->code;
                }
            ],
            [
                'attribute' => 'category_id',
                'header' => 'ใบสั่งซื้อ',
                'value' => function ($model) {
                    return $model->po_number;
                }
            ],
            [
                // 'attribute' => 'category_id',
                'header' => 'สาขา',
                'value' => function ($model) {
                    return $model->category_id;
                }
            ],
            [
                'header' => 'วันที่',
                'value' => function ($model) {
                    return $model->category_id;
                }
            ],
            [
                'header' => 'ผู้จำหน่วย',
                'value' => function ($model) {
                    return $model->category_id;
                }
            ],
            [
                'header' => 'หมายเหตุ',
                'value' => function ($model) {
                    return $model->category_id;
                }
            ],
            [
                'header' => 'สถานะ',
                'value' => function ($model) {
                    return $model->viewStatus();
                }
            ],
            [
                'header' => '',
                'value' => function ($model) {
                    return $model->category_id;
                }
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
