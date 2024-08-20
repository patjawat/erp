<?php

use app\modules\inventory\models\StockEvent;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockEventSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Stock Ins';
$this->params['breadcrumbs'][] = $this->title;
$createIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-plus-2"><path d="M4 22h14a2 2 0 0 0 2-2V7l-5-5H6a2 2 0 0 0-2 2v4"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M3 15h6"/><path d="M6 12v6"/></svg>';
?>

<div class="card">
  <div class="card-body">
    <div class="d-flex gap-3">
      <?=Html::a($createIcon.' สร้างเอกสารตรวจรับ',['/inventory/stock-in/create','name' => 'order','title' => $createIcon.' สร้างเอกสารรับวัสดุ'],['class' => 'btn btn-primary rounded-pill shadow open-modal','data' => ['size' => 'modal-md']])?>
      <?=Html::a($createIcon.' ตรวจรับจากการสั่งซื้อ <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger text-white">99</span>',['/inventory/stock-in/list-pending-order','name' => 'order','title' => '<i class="bi bi-ui-checks"></i> รายการตรวจรับ'],['class' => 'btn btn-primary rounded-pill shadow open-modal position-relative','data' => ['size' => 'modal-xl']])?>
    </div>
  </div>
</div>
<div class="stock-in-index">


    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
 
    <div class="card">
      
      <div class="card-body">
        
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'code',
            //'from_warehouse_id',
            //'qty',
            //'total_price',
            //'unit_price',
            //'receive_type',
            //'movement_date',
            //'lot_number',
            //'expiry_date',
            //'category_id',
            //'order_status',
            //'ref',
            'thai_year',
            //'data_json',
            //'created_at',
            //'updated_at',
            //'created_by',
            //'updated_by',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, StockEvent $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>


</div>
    </div>
    <?php Pjax::end(); ?>

</div>
