<?php
use yii\helpers\Html;
use yii\helpers\Url;
?>
<div class="d-flex gap-2">
    <?php echo Html::a('<i class="fa-solid fa-chart-column me-1"></i> สรุปรายงานวัสดุคงคลัง', ['/inventory/report'], ['class' => 'btn btn-light']) ?>
    <?php Html::a('<i class="fa-solid fa-gauge me-1"></i> Dashbroad', ['/inventory'], ['class' => 'btn btn-light']) ?>
    <?php Html::a('<i class="fa-solid fa-chart-simple me-1"></i> เลือกคลัง', ['/inventory/warehouse/clear'], ['class' => 'btn btn-light']) ?>
    <?php Html::a('<i class="fa-solid fa-circle-down me-1 text-success"></i> เบิกคลัง', ['/inventory/stock-order'], ['class' => 'btn btn-light']) ?>
    <?php Html::a('<i class="fa-solid fa-circle-down me-1 text-success"></i> รับ', ['/inventory/stock-in'], ['class' => 'btn btn-light']) ?>
    <?php Html::a('<i class="fa-solid fa-circle-up me-1 text-danger"></i> จ่าย', ['/inventory/stock-out'], ['class' => 'btn btn-light']) ?>

    <?php  Html::a('<i class="fa-solid fa-circle-up me-1 text-danger"></i> จ่ายออก', ['/inventory/stock-request'], ['class' => 'btn btn-light']) ?>


</div>


