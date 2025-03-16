<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\modules\inventory\models\StockEvent;
$warehouse = Yii::$app->session->get('sub-warehouse');
$this->title = 'คลัง'.$warehouse->warehouse_name;

$cart = Yii::$app->cartSub;
$products = $cart->getItems();
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-shop fs-1"></i> <?php echo $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('@app/modules/me/views/store-v2/menu') ?>
<?php $this->endBlock(); ?>


<div class="stock-event-index">

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <div class="card">
        <div class="card-body">
 
    <div
        class="table-responsive"
    >
        <table
            class="table table-striped table-hover"
        >
            <thead>
                <tr>
                    <th scope="col">รายการ</th>
                    <th scope="col">คลัง</th>
                </tr>
            </thead>
            <tbody class="align-middle table-group-divider">
                <?php foreach($dataProvider->getModels() as $item):?>
                <tr class="">
                    <td scope="row">

                        <?php
                        $msg = $item->viewCreatedAt();
                        ?>
                        <?php echo $item->CreateBy($msg)['avatar']?>
                    </td>
                    
                    <td>
                    <?=$item->ShowPlayer()['avatar'];?>
                        <?php // echo Html::a('<span class="badge rounded-pill badge-soft-primary text-primary fs-13 "><i class="bi bi-exclamation-circle-fill"></i> เพิ่มเติม...</span>',['/me/stock-event/view','id' => $item->id],['class' => 'open-modal','data' => ['size' => 'modal-lg']])?>
                    </td>

                </tr>
                <?php endforeach;?>
            </tbody>
        </table>
    </div>
    
           
    </div>
    </div>
    
    <?php Pjax::end(); ?>

</div>
