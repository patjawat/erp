<?php
use yii\helpers\Url;
use yii\helpers\Html;

$layout = app\components\SiteHelper::getInfo()['layout'];
$menus = [
    [
    'title' => 'Dashboard',
     'active' => 'dashboard',
    'url' => ['/inventory/default/dashboard'],
    'icon' => '<i class="fa-solid fa-gauge-high text-primary me-1"></i>'
    ],
    [
        'title' => 'คลัง',
        'active' => 'index',
        'url' => ['/inventory/default/index'],
        'icon' => '<i class="fa-solid fa-cubes-stacked text-primary me-1"></i>'
    ],
      [
        'title' => 'สรุปรายงานวัสดุคงคลัง',
        'active' => 'report',
        'url' => ['/inventory/report'],
        'icon' => '<i class="fa-solid fa-chart-column text-primary me-1"></i>'
    ],
     [
        'title' => 'ตั้งค่าคลัง',
        'active' => 'warehouse',
        'url' => ['/inventory/warehouse'],
        'icon' => '<i class="fa-solid fa-gear text-primary me-1"></i>'
    ],
        

];
?>
<?php if($layout == 'horizontal'):?>
<?php foreach($menus as $menu):?>
<li class="nav-item">
    <?=Html::a($menu['icon'].$menu['title'],$menu['url'],['class' => 'nav-link ' . (isset($active) && $active == $menu['active'] ? 'active' : '')])?>
</li>
<?php endforeach;?>


<?php else:?>
<div class="d-flex gap-2">    
    <?php Html::a('<i class="fa-solid fa-circle-down me-1 text-success"></i> เบิกคลัง', ['/inventory/stock-order'], ['class' => 'btn btn-light']) ?>
    <?php Html::a('<i class="fa-solid fa-circle-down me-1 text-success"></i> รับ', ['/inventory/stock-in'], ['class' => 'btn btn-light']) ?>
    <?php Html::a('<i class="fa-solid fa-circle-up me-1 text-danger"></i> จ่าย', ['/inventory/stock-out'], ['class' => 'btn btn-light']) ?>
    <?php  Html::a('<i class="fa-solid fa-circle-up me-1 text-danger"></i> จ่ายออก', ['/inventory/stock-request'], ['class' => 'btn btn-light']) ?>
</div>
<?php endif;?>


