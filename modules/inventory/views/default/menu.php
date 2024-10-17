<?php
use yii\helpers\Html;
use yii\helpers\Url;
$warehouse = Yii::$app->session->get('warehouse');

?>

<div class="d-flex gap-2">
    <?= Html::a('<i class="fa-solid fa-gauge me-1"></i> Dashbroad', ['/inventory'], ['class' => 'btn btn-light']) ?>
    <?= Html::a('<i class="fa-solid fa-house"></i> หน้าหลัก', ['/inventory/warehouse'], ['class' => 'btn btn-light']) ?>
    <?php Html::a('<i class="fa-solid fa-chart-simple me-1"></i> เลือกคลังหลัก', ['/inventory/warehouse/clear'], ['class' => 'btn btn-light']) ?>
    <?= $warehouse['warehouse_type'] == 'MAIN' ? Html::a('<i class="fa-solid fa-circle-down me-1 text-success"></i> ทะเบียนรับเข้า', ['/inventory/stock-in'], ['class' => 'btn btn-light']) : '' ?>
    <?php if($warehouse['warehouse_type'] == 'MAIN'):?>
        <?php //  Html::a('<i class="bi bi-cart-check-fill"></i> ทะเบียนขอเบิก', ['/inventory/order-request'], ['class' => 'btn btn-light']) ?>
        <?php else:?>
            <?php echo Html::a('<i class="fa-solid fa-store"></i> เบิกวัสดุคลังหลัก', ['/inventory/main-stock/store'], ['class' => 'btn btn-light']) ?>
            <?php echo Html::a('<i class="fa-solid fa-list-ul"></i> ทะเบียนประวัติ', ['/inventory/main-stock'], ['class' => 'btn btn-light']) ?>
            
        <?php endif;?>

    <?php  Html::a('<i class="fa-solid fa-circle-up me-1 text-danger"></i> จ่ายออก', ['/inventory/stock-request'], ['class' => 'btn btn-light']) ?>
    <button class="btn btn-light" onclick="openTour()">
        <span>แนะนำ</span>
    </button>
    

</div>


