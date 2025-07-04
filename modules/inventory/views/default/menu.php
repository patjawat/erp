<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\db\Expression;
use app\modules\inventory\models\Warehouse;
$id = \Yii::$app->user->id;
$warehouse = Yii::$app->session->get('warehouse');
$checkOffer = Warehouse::find()->andWhere(new Expression("JSON_CONTAINS(data_json->'\$.officer','\"$id\"')"))->count();
$layout = app\components\SiteHelper::getInfo()['layout'];
?>

<?php if($layout == 'horizontal'):?>
<li class="nav-item">
    <?= Html::a('<i class="fa-solid fa-gauge me-1"></i> Dashbroad', ['/inventory/default/dashboard'], ['class' => 'nav-link']) ?>
</li>
<li class="nav-item">
    <?= Html::a('<i class="fa-solid fa-house me-1"></i> หน้าหลัก', ['/inventory/warehouse'], ['class' => 'nav-link ' . (isset($active) && $active == 'index' ? 'active' : '')]) ?>
</li>
<?php if($checkOffer >=2):?>
<li class="nav-item">
    <?php echo Html::a('<i class="fa-solid fa-chart-simple me-1"></i> คลัง', ['/inventory/default/index'], ['class' => 'nav-link']) ?>
</li>
<?php endif;?>
<li class="nav-item">
    <?php Html::a('<i class="fa-solid fa-chart-simple me-1"></i> เลือกคลังหลัก', ['/inventory/warehouse/clear'], ['class' => 'nav-link']) ?>
</li>
<?php if(isset($warehouse) && $warehouse['warehouse_type'] == 'MAIN'):?>
<li class="nav-item">
    <?php echo Html::a('<i class="fa-solid fa-cube me-1"></i> สต๊อก', ['/inventory/stock/in-stock'], ['class' => 'nav-link ' . (isset($active) && $active == 'in_stock' ? 'active' : '')]) ?>
</li>
<li class="nav-item">
    <?=Html::a('<i class="fa-solid fa-circle-down me-1 text-success"></i> ทะเบียนรับเข้า', ['/inventory/stock-in'], ['class' => 'nav-link ' . (isset($active) && $active == 'stock_in' ? 'active' : '')]) ?>
</li>
<li class="nav-item">
    <?=Html::a('<i class="fa-solid fa-circle-up me-1 text-danger"></i> ทะเบียนขอเบิก', ['/inventory/warehouse/order-request'],['class' => 'nav-link ' . (isset($active) && $active == 'request' ? 'active' : '')]) ?>
</li>
<?php else:?>
<li class="nav-item">
    <?php echo Html::a('<i class="fa-solid fa-cube me-1"></i> สต๊อก/เบิกใช้งาน', ['/inventory/stock/in-stock'], ['class' => 'nav-link']) ?>
</li>
<li class="nav-item">
    <?php echo Html::a('<i class="fa-solid fa-file-pen me-1"></i> ทะเบียนรับเข้า', ['/inventory/stock-order'], ['class' => 'nav-link']) ?>
</li>
<li class="nav-item">
    <?php echo Html::a('<i class="fa-solid fa-store me-1"></i> เบิกวัสดุคลังหลัก', ['/inventory/main-stock/store'], ['class' => 'nav-link']) ?>
</li>
<?php endif;?>
<li class="nav-item">
    <?php  Html::a('<i class="fa-solid fa-circle-up me-1 text-danger"></i> จ่ายออก', ['/inventory/stock-request'], ['class' => 'nav-link']) ?>
</li>



<!-- ############ แบบแนวนอน #########-->
<?php else:?>
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
<?php endif;?>