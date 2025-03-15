<?php
use yii\helpers\Url;
use yii\helpers\Html;
?>
<div class="d-flex gap-2">
    <?php echo Html::a('<i class="fa-solid fa-gauge me-1"></i> Dashbroad', ['/inventory/default/dashboard'], ['class' => 'btn btn-light']) ?>
    <?php echo Html::a('<i class="fa-solid fa-cubes-stacked me-1"></i> คลัง', ['/inventory/default/index'], ['class' => 'btn btn-light']) ?>
    <?php echo Html::a('<i class="fa-solid fa-chart-column me-1"></i> สรุปรายงานวัสดุคงคลัง', ['/inventory/report'], ['class' => 'btn btn-light']) ?>
    <?php echo Html::a('<i class="fa-solid fa-gear me-1"></i> ตั้งค่าคลัง', ['/inventory/warehouse/'], ['class' => 'btn btn-light']) ?>
    <?php Html::a('<i class="fa-solid fa-circle-down me-1 text-success"></i> เบิกคลัง', ['/inventory/stock-order'], ['class' => 'btn btn-light']) ?>
    <?php Html::a('<i class="fa-solid fa-circle-down me-1 text-success"></i> รับ', ['/inventory/stock-in'], ['class' => 'btn btn-light']) ?>
    <?php Html::a('<i class="fa-solid fa-circle-up me-1 text-danger"></i> จ่าย', ['/inventory/stock-out'], ['class' => 'btn btn-light']) ?>
    <?php  Html::a('<i class="fa-solid fa-circle-up me-1 text-danger"></i> จ่ายออก', ['/inventory/stock-request'], ['class' => 'btn btn-light']) ?>


</div>


