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

<?php //  Pjax::begin(['id' => 'inventory-container', 'enablePushState' => true]); ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h6><i class="bi bi-ui-checks"></i> ประวัติการเบิก <span class="badge rounded-pill text-bg-primary">
                        <?=$dataProvider->getTotalCount()?></span> รายการ</h6>
                        <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
                        <div>
                            <?php echo Html::a('<i class="fa-solid fa-cart-plus"></i> เบิกวัสดุ', ['/inventory/stock/in-stock'], ['class' => 'btn btn-sm btn-primary rounded-pill','data' =>['pjax' => 0]]) ?>
                    </div>

                </div>
                <table class="table table-striped table-sm mt-3">
                    <thead>
                        <tr>
                            <th style="width:110px">รหัส</th>
                            <th  style="width:120px" class="text-center">ปีงบประมาณ</th>
                            <th style="width:200px">วันที่</th>
                            <th scope="col">ผู้เบิก</th>
                            <th >มูลค่า</th>
                            <th class="text-center" style="width:300px">สถานะ</th>
                            <th style="width:100px">ดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody class="align-middle">
                        <?php foreach ($dataProvider->getModels() as $item): ?>
                        <tr>
                            <td><?=$item->code?></td>
                            <td class="text-center"><?=$item->thai_year?></td>
                            <td><?=$item->viewCreatedAt()?></td>
                            <td>
                                <?php
                                try {
                                   echo $item->CreateBy()['avatar'];
                                } catch (\Throwable $th) {
                                }
                                ?>
                            </td>
                            <td><?=number_format($item->getTotalOrderPrice(),2)?></td>
                            <td class="text-center"><?=$item->viewstatus()?></td>
                            <td>
                                <div class="btn-group">
                                    <?=Html::a('<i class="fa-regular fa-pen-to-square text-primary"></i>',['/inventory/stock-order/view','id' => $item->id],['class'=> 'btn btn-light'])?>

                                    <button type="button" class="btn btn-light dropdown-toggle dropdown-toggle-split"
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
            <div class="card-footer">
            <div class="d-flex justify-content-between">
                    
            <?= yii\bootstrap5\LinkPager::widget([
        'pagination' => $dataProvider->pagination,
        'firstPageLabel' => 'หน้าแรก',
        'lastPageLabel' => 'หน้าสุดท้าย',
        'options' => [
            'class' => 'pagination pagination-sm',
        ],
    ]); ?>

                        <div>
                            <?php echo Html::a('<i class="fa-solid fa-angles-right"></i> แสดงท้ังหมด', ['/inventory/order-request'], ['class' => 'btn btn-sm btn-light rounded-pill','data' =>['pjax' => 0]]) ?>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<?php // Pjax::end(); ?>