<?php
use yii\helpers\Url;
use yii\helpers\Html;

$layout = app\components\SiteHelper::getInfo()['layout'];

?>
<?php if($layout == 'horizontal'):?>


<li class="nav-item">
<?=Html::a('<i class="fa-solid fa-gauge-high text-primary me-1"></i> Dashboard ',['/inventory/default/dashboard'],['class' => 'nav-link'])?>
</li>
<li class="nav-item">
<?=Html::a('<i class="fa-solid fa-cubes-stacked text-primary me-1"></i> คลัง ',['/inventory/default/index'],['class' => 'nav-link'])?>
</li>

<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle <?=(isset($active) && $active == 'report' ? 'active' : '')?>" href="#"
        id="topnav-dashboard" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="fa-solid fa-chart-column text-primary me-1"></i> รายงานวัสดุคงคลัง
        <i class="bx bx-chevron-down"></i>
    </a>
    <div class="dropdown-menu" aria-labelledby="topnav-dashboard">
        <?=Html::a('<i class="fa-solid fa-gauge me-2"></i> สรุปรายงานวัสดุคงคลัง ',['/inventory/report'],['class' => 'dropdown-item'])?>
        <?= Html::a('<i class="fa-solid fa-cube me-2"></i> รายงานวัสดุรับ-จ่าย ',['/inventory/report/list-summary'],['class' => 'dropdown-item'])?>
        <?= Html::a('<i class="fa-solid fa-cube me-2"></i> รายงานวัสดุคงคลังหลักรายตัว ',['/inventory/report/list-by-item'],['class' => 'dropdown-item'])?>
    </div>
</li>

<li class="nav-item">
<?=Html::a('<i class="fa-solid fa-gear text-primary me-1"></i> ตั้งค่าคลัง ',['/inventory/warehouse'],['class' => 'nav-link'])?>
</li>




<?php else:?>
<div class="d-flex gap-2">    
    <?php Html::a('<i class="fa-solid fa-circle-down me-1 text-success"></i> เบิกคลัง', ['/inventory/stock-order'], ['class' => 'btn btn-light']) ?>
    <?php Html::a('<i class="fa-solid fa-circle-down me-1 text-success"></i> รับ', ['/inventory/stock-in'], ['class' => 'btn btn-light']) ?>
    <?php Html::a('<i class="fa-solid fa-circle-up me-1 text-danger"></i> จ่าย', ['/inventory/stock-out'], ['class' => 'btn btn-light']) ?>
    <?php  Html::a('<i class="fa-solid fa-circle-up me-1 text-danger"></i> จ่ายออก', ['/inventory/stock-request'], ['class' => 'btn btn-light']) ?>
</div>
<?php endif;?>


