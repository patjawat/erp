<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\db\Expression;
use app\modules\inventory\models\Warehouse;
$id = \Yii::$app->user->id;
$warehouse = Yii::$app->session->get('warehouse');
$checkOffer = Warehouse::find()->andWhere(new Expression("JSON_CONTAINS(data_json->'\$.officer','\"$id\"')"))->count();

?>

<div class="d-flex gap-2">
    <?= Html::a('<i class="fa-solid fa-gauge me-1"></i> Dashbroad', ['/inventory/default/dashboard'], ['class' => 'btn btn-light']) ?>
    <?= Html::a('<i class="fa-solid fa-house"></i> หน้าหลัก', ['/inventory/warehouse'], ['class' => 'btn btn-light']) ?>
    <?php if($checkOffer >=2):?>
        <?php echo Html::a('<i class="fa-solid fa-chart-simple me-1"></i> คลัง', ['/inventory/default/index'], ['class' => 'btn btn-light']) ?>
        
    <?php endif;?>
    <?php Html::a('<i class="fa-solid fa-chart-simple me-1"></i> เลือกคลังหลัก', ['/inventory/warehouse/clear'], ['class' => 'btn btn-light']) ?>
    <?php if(isset($warehouse) && $warehouse['warehouse_type'] == 'MAIN'):?>
        <?php echo Html::a('<i class="fa-solid fa-cube"></i> สต๊อก', ['/inventory/stock/in-stock'], ['class' => 'btn btn-light']) ?>
        <?=Html::a('<i class="fa-solid fa-circle-down me-1 text-success"></i> ทะเบียนรับเข้า', ['/inventory/stock-in'], ['class' => 'btn btn-light'])  ?>
        <?php // Html::a('<i class="fa-solid fa-circle-up me-1 text-danger"></i> ทะเบียนขอเบิก', ['/inventory/stock-order'], ['class' => 'btn btn-light'])  ?>
        <?=Html::a('<i class="fa-solid fa-circle-up me-1 text-danger"></i> ทะเบียนขอเบิก', ['/inventory/warehouse/order-request'], ['class' => 'btn btn-light'])  ?>
        <?php //  Html::a('<i class="bi bi-cart-check-fill"></i> ทะเบียนขอเบิก', ['/inventory/order-request'], ['class' => 'btn btn-light']) ?>
        <?php else:?>
            
            <?php echo Html::a('<i class="fa-solid fa-cube"></i> สต๊อก/เบิกใช้งาน', ['/inventory/stock/in-stock'], ['class' => 'btn btn-light']) ?>
            
            <?php echo Html::a('<i class="fa-solid fa-file-pen"></i> ทะเบียนรับเข้า', ['/inventory/stock-order'], ['class' => 'btn btn-light']) ?>
            <?php echo Html::a('<i class="fa-solid fa-store"></i> เบิกวัสดุคลังหลัก', ['/inventory/main-stock/store'], ['class' => 'btn btn-light','data-pjax' => '0']) ?>
        <?php endif;?>

    <?php  Html::a('<i class="fa-solid fa-circle-up me-1 text-danger"></i> จ่ายออก', ['/inventory/stock-request'], ['class' => 'btn btn-light']) ?>
    <!-- <button class="btn btn-light" onclick="openTour()">
        <span>แนะนำ</span>
    </button> -->
</div>


