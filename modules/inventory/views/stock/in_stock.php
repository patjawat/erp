<?php

use app\modules\inventory\models\Stock;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
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

<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('../default/menu') ?>
<?php $this->endBlock(); ?>
<?php Pjax::begin(['id' => 'inventory-container']); ?>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h6><i class="bi bi-ui-checks"></i> วัสดุในสต๊อก <span class="badge rounded-pill text-bg-primary">
                    <?=$dataProvider->getTotalCount();?> </span> รายการ</h6>
            <div class="d-flex">

                <?=$this->render('_search', ['model' => $searchModel]); ?>
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
                </tr>
            </thead>
            <tbody class="align-middle">
                <?php foreach($dataProvider->getModels() as $item):?>
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
    <?php Pjax::end(); ?>