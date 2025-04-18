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
$stockEvents = StockEvent::find()
->select([
    'stock_events.*',
    new Expression('SUM(qty * unit_price) AS total')
])
->where([
    'asset_item' => $model->asset_item,
    'warehouse_id' => $warehouse->id,
    'order_status' => 'success'
])
->groupBy('id')
->orderBy(['movement_date' => SORT_ASC])
->all();

$balance = 0;
$balanceQty = 0;
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
        
<table class="table">
            <thead>
                <tr>
                    <th class="fw-semibold" scope="col" style="width:130px">ความเคลื่อนไหว</th>
                    <th class="fw-semibold" scope="col" style="width:120px">ปีงบประมาณ</th>
                    <th class="fw-semibold" scope="col" style="width:180px">วันที่รับ/จ่าย</th>
                    <th class="fw-semibold" scope="col" style="width:130px">หมายเลขล็อต</th>
                    <th class="fw-semibold" scope="col">เลขที่เอกสาร</th>
                    <th class="fw-semibold" scope="col">ผู้สั่งจ่าย</th>
                    <th class="text-end fw-semibold" scope="col">ราคาต่อหน่วย</th>
                    <th class="fw-semibold" scope="col" class="text-center">จำนวนเข้า</th>
                    <th class="fw-semibold" scope="col" class="text-center">จำนวนออก</th>
                    <th class="fw-semibold" scope="col" class="text-center">คงเหลือ</th>
                    <th class="fw-semibold text-end" scope="col">มูลค่าวัสดุคงเหลือ</th>
                </tr>
            </thead>
            <tbody class="align-middle table-group-divider">
                <?php foreach($stockEvents as $item2):?>
                    <?php
                        if ($item2->transaction_type == 'IN') {
                            $balance += $item2['total'];
                            $balanceQty += $item2->qty;
                        } elseif ($item2->transaction_type == 'OUT') {
                            $balance -= $item2['total'];
                            $balanceQty -= $item2->qty;
                        }
                    ?>
                <tr>
        <td>
            <?php if ($item2->transaction_type == 'IN'): ?>
                <div class="badge rounded-pill badge-soft-primary text-primary fs-13"><i
                        class="fa-solid fa-circle-plus"></i> รับ</div>
            <?php else: ?>
                <div class="badge rounded-pill badge-soft-danger text-danger fs-13"><i
                        class="fa-solid fa-circle-minus"></i> จ่าย</div>
            <?php endif ?>
        </td>
        <td><?=$item2->thai_year?></td>
                    <td>
                        <?php
                                try {
                                // Yii::$app->thaiFormatter->asDateTime($item->, 'short')
                                echo $item2->ViewReceiveDate();
                                } catch (\Throwable $th) {
                                //throw $th;
                                }
                            ?>
                    </td>
                    <td><?=$item2->lot_number?></td>
                    <td class="text-center  fw-semibold">
                        <?=$item2->transaction_type == 'IN' ? $item2->qty : ''?>
                    </td>
                        <td class="text-center  fw-semibold"><?php
                        try {
                            echo $item2->transaction_type == 'OUT' ? -ABS($item2->qty) : '';
                        } catch (\Throwable $th) {
                            //throw $th;
                        }
                        ?>
      </td>
        <td class="fw-semibold text-end"><?=number_format($item2->unit_price, 2)?></td>
        <td class="fw-semibold text-center"><?=$item2->transaction_type == 'IN' ? $item2->qty : ''?></td>
        <td class="fw-semibold text-center"><?=$item2->transaction_type == 'OUT' ? -ABS($item2->qty) : ''?></td>
        <td class="fw-semibold text-center"><?= $balanceQty?></td>
        <td class="fw-semibold text-end">
            <?php
            // echo $item2['total'];
            echo number_format($balance, 2)?></td>
    </tr>
                <?php endforeach;?>
            </tbody>
        </table>


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