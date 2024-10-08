<?php

use app\modules\inventory\models\StockEvent;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use app\modules\inventory\models\Warehouse;
use app\modules\purchase\models\Order;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockEventSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$warehouse = Yii::$app->session->get('warehouse');
$this->title = 'รับเข้า'.$warehouse['warehouse_name'];
$this->params['breadcrumbs'][] = $this->title;
$createIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-plus-2"><path d="M4 22h14a2 2 0 0 0 2-2V7l-5-5H6a2 2 0 0 0-2 2v4"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M3 15h6"/><path d="M6 12v6"/></svg>';


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

 $warehouseModel = Warehouse::findOne($warehouse['warehouse_id']);
 if(isset($warehouseModel->data_json['item_type'])){
     

 $item = $warehouseModel->data_json['item_type'];

 $models = Order::find()
     ->where(['name' => 'order', 'status' => 4])
     ->andWhere(['IN', 'category_id', $item])
     ->all();
     $count = is_array($models) ? count($models) : 0;
    }
    $count = 0;

?>

<div class="card">
  <div class="card-body">
    <div class="d-flex gap-3">
      <?= Html::a($createIcon . 'สร้างรายการรับเข้า', ['/inventory/stock-in/create', 'name' => 'order','type' => 'IN','title' => '<i class="bi bi-ui-checks"></i> สร้างใบรับเข้า'], ['class' => 'btn btn-primary rounded-pill shadow open-modal position-relative', 'data' => ['size' => 'modal-md']]) ?>
      <?= Html::a($createIcon . 'การสั่งซื้อ <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger text-white">'.$count.'</span>', ['/inventory/stock-in/list-pending-order', 'name' => 'order', 'title' => '<i class="bi bi-ui-checks"></i> รายการตรวจรับ'], ['class' => 'btn btn-primary rounded-pill shadow open-modal position-relative', 'data' => ['size' => 'modal-xl']]) ?>
    </div>
  </div>
</div>
<div class="stock-in-index">


  <?php Pjax::begin(); ?>
  <?php // echo $this->render('_search', ['model' => $searchModel]); 
  ?>


  <div class="card">
    <div class="card-body">

      <div class="d-flex justify-content-between">
        <h6><i class="bi bi-ui-checks"></i> รับเข้าจำนวน <span class="badge rounded-pill text-bg-primary"> <?=$dataProvider->getTotalCount()?></span> รายการ</h6>
        <div>
          <!-- <button class="btn btn-sm btn-primary rounded-pill"><i class="fa-solid fa-plus"></i>
          เลือกรายการ</button> -->
        </div>
        
      </div>
      <div class="table-responsive-sm">
        <table class="table table-primary">
          <thead>
            <tr>
              <th scope="col">รหัส</th>
              <th>เลขทะเบียนคุม</th>
              <th>วันที่ตรวจรับ</th>
              <th>ประเภทวัสดุ</th>
              <th>คลังที่รับเข้า</th>
              <th>รับจาก</th>
              <th>เจ้าหน้าที่</th>
              <th>มูลค่า</th>
              <th style="width:100px">ดำเนินการ</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($dataProvider->getModels() as $item): ?>
              <tr>
            <td>
                  <?php
                 // echo $item->CreateBy($item->ViewReceiveDate())['avatar'];
                   // $item->CreateBy(isset($item->data_json['receive_date']) ? $item->data_json['receive_date'] : '')['avatar'];
                  ?>
                  </td>
                <td><?=$item->code?></td>
                <td>
                  <?php
                  try {
                    echo $item->purchase->pq_number;
                  } catch (\Throwable $th) {
                    //throw $th;
                  }?>
                   
                  </td>
                  <td>
                  <?php
                    echo $item->ViewReceiveDate();
                  try {
                  } catch (\Throwable $th) {
                    //throw $th;
                  }?>
                  </td>
                  <td><?=$item->thai_year?></td>
                <td><?php
                try {
                  echo number_format($item->getTotalOrderPrice(),2);
                } catch (\Throwable $th) {

                }

                ?></td>
                <td><?= $item->viewStatus();?></td>
                <td>
                <?=Html::a('<i class="fa-regular fa-pen-to-square text-primary"></i>',['/inventory/stock-in/view','id' => $item->id],['class'=> 'btn btn-light'])?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

    </div>
  </div>


  <?php Pjax::end(); ?>

</div>