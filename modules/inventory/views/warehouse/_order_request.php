<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use yii\grid\ActionColumn;
use app\modules\inventory\models\StockEvent;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockEventSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$warehouse = Yii::$app->session->get('warehouse');
$this->title = $warehouse['warehouse_name'];
$this->params['breadcrumbs'][] = ['label' => 'ระบบคลัง', 'url' => ['/inventory']];
$this->params['breadcrumbs'][] = $this->title;
$this->params['breadcrumbs'][] = 'ทะเบียนขอเบิก';
// $this->params['breadcrumbs'][] = ['label' => $this->title, 'url' => ['/inventory/warehouse']];

$createIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-plus-2"><path d="M4 22h14a2 2 0 0 0 2-2V7l-5-5H6a2 2 0 0 0-2 2v4"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M3 15h6"/><path d="M6 12v6"/></svg>';
?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-cubes-stacked"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
<p class="mb-0 text-white">ทะเบียนขอเบิก</p>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('../default/menu') ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?=$this->render('../default/menu',['active' => 'request'])?>
<?php $this->endBlock(); ?>




<?php  // Pjax::begin(['id' => 'inventory-container', 'enablePushState' => true]); ?>

<div class="card">
    <div class="card-body">
          <?php echo $this->render('_search_order', ['model' => $searchModel,'dataProvider' => $dataProvider]); ?>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>

                        <h6><i class="bi bi-ui-checks"></i> ขอเบิกจำนวน <span
                                class="badge rounded-pill text-bg-primary"> <?=$dataProvider->getTotalCount()?></span>
                            รายการ</h6>
                      
                    </div>
                    <div>
                        <?php // echo Html::a('<i class="fa-solid fa-angles-right"></i> แสดงท้ังหมด', ['/inventory/stock-order'], ['class' => 'btn btn-sm btn-light rounded-pill','data' =>['pjax' => 0]]) ?>
                        <?php echo Html::a('<i class="fa-solid fa-angles-right"></i> แสดงท้ังหมด', ['/inventory/warehouse/order-request'], ['class' => 'btn btn-sm btn-light rounded-pill','data' =>['pjax' => 0]]) ?>
                    </div>

                </div>


                <?= $this->render('list_order',[  'searchModel' => $searchModel,'dataProvider' => $dataProvider,])?>

            </div>
        </div>
    </div>
</div>

<?php // Pjax::end(); ?>