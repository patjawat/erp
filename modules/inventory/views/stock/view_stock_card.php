<?php

use yii\helpers\Html;
use yii\db\Expression;
use yii\widgets\DetailView;
use app\modules\inventory\models\StockEvent;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\Stock $model */

$warehouse = Yii::$app->session->get('warehouse');
$this->title = $warehouse['warehouse_name'];
$this->params['breadcrumbs'][] = ['label' => 'Stocks', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-cubes-stacked"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('../default/menu') ?>
<?php $this->endBlock(); ?>

<?php
// $stockEvents = StockEvent::find()
// ->select([
//     'stock_events.*',
//     new Expression('SUM(qty * unit_price) AS total')
// ])
// ->where([
//     'asset_item' => $model->asset_item,
//     'warehouse_id' => $warehouse->id,
//     'order_status' => 'success'
// ])
// ->groupBy('id')
// ->orderBy(['movement_date' => SORT_ASC])
// ->all();

// $balance = 0;
// $balanceQty = 0;
?>


<div class="row">
    <div class="col-3">
        <div class="card border border-primary border-4 border-top-0 border-end-0 border-start-0">
            <div class="card-body">
                <div class="d-flex justify-content-between">

                    <?=$model->product->Avatar()?>
                    <span class="fs-1"><?=$model->SumQty()?></span>
                </div>
            </div>
            <div class="card-footer border-0">
                จำนวนคงเหลือ
            </div>
        </div>
    </div>
    <div class="col-3">
        <div class="card border border-primary border-4 border-top-0 border-end-0 border-start-0">
            <div class="card-body">
                <h1 class="text-center"><?=$model->SumPriceByItem()?></h1>
            </div>
            <div class="card-footer border-0">
                มูลค่าวัสดุคงเหลือ
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <!-- อันเดิม -->
            <?php // count($model->getStockCard())?>
            <h6><i class="bi bi-ui-checks"></i> ทั้งหมดจำนวน <span class="badge rounded-pill text-bg-primary">
                    <?=count($dataProvider->getModels())?> </span> รายการ</h6>
            <div>
                <?php  echo $this->render('_search_stock', ['searchModel' => $searchModel,'model' => $model]); ?>
            </div>
        </div>
<?=$this->render('list_stock',['asset_item' => $model->asset_item])?>

    </div>
</div>





<!-- SELECT 
    t.asset_item,
    t.warehouse_id,
    t.transaction_type,
    t.qty,
    @running_total := IF(t.transaction_type = 'IN', @running_total + t.qty, @running_total - t.qty) AS running_total
FROM 
    stock_events t
JOIN 
    (SELECT @running_total := 0) r
    WHERE t.asset_item = '7110-007-0006' AND t.name = 'order_item'
ORDER BY 
    t.created_at, t.id
LIMIT 10; -->