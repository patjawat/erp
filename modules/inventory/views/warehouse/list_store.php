<?php

use app\modules\inventory\models\Stock;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\web\View;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$warehouse = Yii::$app->session->get('warehouse');
$this->title = $warehouse['warehouse_name'];
$this->params['breadcrumbs'][] = $this->title;


?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-cubes-stacked"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?= $this->render('../default/menu') ?>
<?php $this->endBlock(); ?>
<?php // Pjax::begin(['id' => 'store-container']); ?>
<?php
$cart = Yii::$app->cartSub;
$products = $cart->getItems();
?>
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h6><i class="bi bi-ui-checks"></i> วัสดุในสต๊อก <span class="badge rounded-pill text-bg-primary">
                    <?=$dataProvider->getTotalCount();?> </span> รายการ</h6>
            <div class="d-flex">
                    <?=Html::a('<button type="button" class="btn btn-primary rounded-pill">
                    <i class="fa-solid fa-cart-plus"></i> เลือก <span class="badge text-bg-danger">'.$cart->getCount().'</span> รายการ
                    </button>',['/inventory/sub-stock/show-cart'],['class' => 'brn btn-primary rounded-pill shadow open-modal','data' => ['size' => 'modal-lg']])?>

                <?php // $this->render('_search', ['model' => $searchModel]); ?>
                <?php // echo Html::a('<i class="fa-solid fa-angles-right"></i> แสดงท้ังหมด', ['/inventory/stock/warehouse'], ['class' => 'btn btn-sm btn-light','data' => ['pjax' => 0]]) ?>
            </div>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">ชื่อรายการ</th>
                    <th class="text-start">ประเภท</th>
                    <th scope="col" class="text-center">คงเหลือ</th>
                    <th scope="col" class="text-center">หน่วย</th>
                    <th scope="col" class="text-end">มูลค่า</th>
                    <th scope="col" class="text-end">ดำเนินการ</th>
                </tr>
            </thead>
            <tbody class="align-middle">
      <?php
      $models = $dataProvider->getModels();
    //   usort($models, function($a, $b) {
    //     return $b->SumPriceByItem() - $a->SumPriceByItem();
    // });
      ?>
                <?php foreach($models as $item):?>
                <tr>
                    <th scope="row"><?=Html::a($item->product->Avatar(),['/inventory/stock/view','id' => $item->id])?>
                    </th>
                    <td class="text-start">
                        <?=isset($item->product->productType->title) ? $item->product->productType->title : 'ไม่พบข้อมูล' ?>
                    </td>
                    <td class="text-center"><?=$item->SumQty()?></td>
                    <td class="text-center"><?=$item->product->data_json['unit']?></td>
                    <td class="text-end">
                        <span class="fw-semibold"><?=$item->SumPriceByItem()?></span>
                    </td>
                    <td class="text-end">
                        <?php if($item->SumQty() > 0):?>
                    <?=Html::a('<i class="fa-solid fa-circle-plus"></i> เลือก',['/inventory/sub-stock/add-to-cart','id' => $item->id],['class' => 'add-cart btn btn-sm btn-primary shadow rounded-pill'])?>
                   <?php else:?>
                    <button type="button" class="btn btn-sm btn-primary shadow rounded-pill" disabled><i class="fa-solid fa-circle-plus"></i> เลือก</button>
                    <?php endif?>
                </td>
                </tr>
                <?php endforeach;?>
            </tbody>
        </table>

        <div class="d-flex justify-content-center">
            <?= yii\bootstrap5\LinkPager::widget([
                'pagination' => $dataProvider->pagination,
                'firstPageLabel' => 'หน้าแรก',
                'lastPageLabel' => 'หน้าสุดท้าย',
                'options' => [
                    'class' => 'pagination pagination-sm',
                ],
            ]); ?>

        </div>
    </div>
    </div>
    <?php // Pjax::end(); ?>


    <?php
$js = <<< JS


JS;
$this->registerJS($js, View::POS_END);

?>