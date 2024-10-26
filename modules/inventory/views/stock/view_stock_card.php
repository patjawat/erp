<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

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
            <h6><i class="bi bi-ui-checks"></i> ทั้งหมดจำนวน <span class="badge rounded-pill text-bg-primary"> <?=count($model->getStockCard())?> </span> รายการ</h6>
            <div>
            <?php  echo $this->render('_search', ['searchModel' => $searchModel,'model' => $model]); ?>
            </div>
          </div>
<table class="table">
  <thead>
    <tr>
      <th scope="col" style="width:130px">ความเคลื่อนไหว</th>
      <th scope="col" style="width:120px">ปีงบประมาณ</th>
      <th scope="col" style="width:180px">วันที่รับ/จ่าย</th>
      <th scope="col" style="width:130px">หมายเลขล็อต</th>
      <th scope="col">เลขที่เอกสาร</th>
      <th scope="col">ผู้สั่งจ่าย</th>
      <th class="text-end" scope="col">ราคาต่อหน่วย</th>
      <th scope="col" class="text-center">จำนวนเข้า</th>
      <th scope="col" class="text-center">จำนวนออก</th>
      <th scope="col" class="text-center">คงเหลือ</th>
      <th scope="col" class="text-center">มูลค่าวัสดุคงเหลือ</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($dataProvider->getModels() as $item):?>
    <tr>
      <th scope="row">
        <?php

      if($item['transaction_type'] == 'IN'){
        echo 'เข้า <svg class="text-success" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-input"><path d="M4 22h14a2 2 0 0 0 2-2V7l-5-5H6a2 2 0 0 0-2 2v4"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M2 15h10"/><path d="m9 18 3-3-3-3"/></svg>';
    }else{
        echo '<svg class="text-danger" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-output"><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M4 7V4a2 2 0 0 1 2-2 2 2 0 0 0-2 2"/><path d="M4.063 20.999a2 2 0 0 0 2 1L18 22a2 2 0 0 0 2-2V7l-5-5H6"/><path d="m5 11-3 3"/><path d="m5 17-3-3h10"/></svg> ออก';
      }
      ?></th>
      <td><?=$item['thai_year']?></td>
      <td>
        <?php
        try {
          // Yii::$app->thaiFormatter->asDateTime($item->created_at, 'short')
          echo $item->created_at;
        } catch (\Throwable $th) {
          //throw $th;
        }
      ?>
      </td>
      <td><?=$item['lot_number']?></td>
      <td>
      <?=Html::a($item['code'],['/inventory/stock-in/view','id' =>$item['category_id']],['class' => 'open-modal','data' => ['size' => 'modal-xl']])?>  
</td>
      <td><?=$item['warehouse_name']?></td>
      <td class="text-end"><?php
      try {
        echo number_format($item['unit_price'],2);
      } catch (\Throwable $th) {
        //throw $th;
      }
      ?></td>
 
      <td class="text-center"><?=
      $item['transaction_type'] == 'IN' ? $item['qty'] : ''?></td>
      <td class="text-center"><?php
      try {
        echo $item['transaction_type'] == 'OUT' ? -ABS($item['qty']) : '';
      } catch (\Throwable $th) {
        //throw $th;
      }
      ?></td>
      <td class="text-center"><?=$item['total']?></td>
      <td class="text-end">
        <?php
      try {
         echo number_format($item['total_price'],2);
      } catch (\Throwable $th) {
        //throw $th;
      }
      ?></td>

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