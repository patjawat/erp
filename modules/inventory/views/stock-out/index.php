<?php

use app\modules\inventory\models\StockEvent;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockEventSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$warehouse = Yii::$app->session->get('warehouse');
$this->title = 'ขอเบิก'.$warehouse['warehouse_name'];
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



<!-- <div class="card p-0">
  <div class="card-body">
      <?=Html::a($createIcon.' สร้างเอกการเบิก',['/inventory/stock-order/create','name' => 'order','type' => 'OUT','title' => $createIcon.' สร้างเอกสารเบิกวัสดุ'],['class' => 'btn btn-primary rounded-pill shadow open-modal','data' => ['size' => 'modal-md']])?>
    </div>
  </div>
</div> -->
<div class="stock-in-index">
    <?php Pjax::begin(['id' => 'inventory']); ?>

    <div class="row">


        <div class="col-12">

            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <h6><i class="bi bi-ui-checks"></i> ขอเบิกจำนวน <span class="badge rounded-pill text-bg-primary"> <?=$dataProvider->getTotalCount()?></span>รายการ</h6>
                        <div>
                           <?=Html::a('เลือกรายการ',['/inventory/stock-out/stock'],['class' => 'btn btn-sm btn-primary shadow rounded-pill'])?>
                        </div>

                    </div>
                    <table class="table table-primary mb-5">
                        <thead>
                            <tr>
                                <th style="width:130px">รหัส</th>
                                <th style="width:130px" class="text-center">ปีงบประมาณ</th>
                                <th style="width:400px" scope="col">รายการ</th>
                                <th>จาก</th>
                                <th>สถานะ</th>
                                <th style="width:100px">ดำเนินการ</th>
                            </tr>
                        </thead>
                        <tbody class="align-middle table-group-divider">
                            <?php foreach ($dataProvider->getModels() as $item): ?>
                            <tr>
                                <td><?=$item->code?></td>
                                <td class="text-center"><?=$item->thai_year?></td>
                                <td><?=$item->CreateBy($item->fromWarehouse->warehouse_name.' | '.$item->created_at)['avatar']?>
                                </td>
                                <td><?=$item->fromWarehouse->warehouse_name?></td>

                                <td><?=$item->viewstatus()?></td>
                                <td>
                                    <div class="btn-group">
                                        <?=Html::a('<i class="fa-regular fa-pen-to-square text-primary"></i>',['/inventory/stock-order/view','id' => $item->id],['class'=> 'btn btn-light'])?>

                                        <button type="button"
                                            class="btn btn-light dropdown-toggle dropdown-toggle-split"
                                            data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
                                            <i class="bi bi-caret-down-fill"></i>
                                        </button>
                                        <ul class="dropdown-menu">

                                            <li><?= Html::a('<i class="fa-solid fa-print me-1"></i> พิมพ์ใบเบิก', ['/inventory/document/stock-out','id' => $item->id], ['class' => 'dropdown-item open-modal','data' => ['size' => 'modal-lg']]) ?>
                                            </li>


                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                </div>
            </div>
        </div>



    </div>




    <?php

use yii\web\View;
// $showReceivePendingOrderUrl = Url::to(['/inventory/receive/list-pending-order']);
// $listOrderRequestUrl = Url::to(['/inventory/stock/list-order-request']);

$StoreInWarehouseUrl = Url::to(['/inventory/stock/warehouse']);
$chartUrl = Url::to(['/inventory/stock/view-chart']);
$getStock = Url::to(['/inventory/stock-out/stock']);
// $chartSummeryIn = Json::encode($chartSummary['in']);
// $chartSummeryOut = Json::encode($chartSummary['out']);
$js = <<< JS
 
  JS;
$this->registerJS($js, View::POS_END);
?>


    <?php Pjax::end(); ?>

</div>