<?php

use app\modules\inventory\models\Store;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StoreSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$warehouse = Yii::$app->session->get('warehouse');
$this->title = $warehouse['warehouse_name'];
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

<?php yii\widgets\Pjax::begin(['id' => 'inventory']); ?>
<?php
// $selectWarehouse = Yii::$app->session->get('select-warehouse');
$cart = \Yii::$app->cart;
$products = $cart->getItems();
// echo "<pre>";
// print_r($selectWarehouse);
// echo "</pre>";
?>
 <?=$this->render('_search', ['model' => $searchModel])?>

<div class="row">
<div class="col-8">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <h6><i class="bi bi-ui-checks"></i> จำนวนวัสดุในคลัง <span class="badge rounded-pill text-bg-primary"><?=$dataProvider->getTotalCount()?> </span> รายการ</h6>
                <?=$this->render('_search', ['model' => $searchModel])?>
            </div>
            <div class="table-responsive">
                <table class="table table-primary">
                    <thead>
                        <tr>
                            <th scope="col">ชื่อรายการ</th>
                            <th class="text-start">ล๊อตปัจุบัน</th>
                            <th class="text-center">คงเหลือ</th>
                            <th class="text-center" style="width:90px">ดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($dataProvider->getModels() as $item):?>
                        <tr class="">
                            <td scope="row"><?=$item->product->Avatar()?>
                        </td>
                            <td class="text-start"> <?=$item->lot_number?></td>
                            <td class="text-center"><?=$item->qty?></td>
                            <!-- <td class="text-center"><?=$item->total?></td> -->
                            <td class="text-center">
                                <?=Html::a('<i class="fa-solid fa-cart-plus"></i> เพิ่ม',['/inventory/store/add-to-cart','id' => $item->id],['class' => 'add-cart btn btn-sm btn-primary shadow rounded-pill'])?>
                            </td>
                        </tr>
                        <?php endforeach;?>
                    </tbody>
                </table>
            </div>

        </div>
        
    </div>
  

        
</div>
<div class="col-4">
    <div id="viewCartShow"></div>
<?php // $this->render('view_cart')?>
</div>
</div>

<?php yii\widgets\Pjax::end(); ?>





