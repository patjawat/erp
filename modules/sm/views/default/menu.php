<?php
use yii\helpers\Url;
use yii\helpers\Html;
$layout = app\components\SiteHelper::getInfo()['layout'];
$menus = [
    [
    'title' => 'Dashboard',
     'active' => 'dashboard',
    'url' => ['/sm'],
    'icon' => '<i class="fa-solid fa-gauge-high text-primary me-1"></i>'
    ],
        [
        'title' => 'ทะเบียนประวัติ',
        'active' => 'order',
        'url' => ['/purchase/order'],
        'icon' => '<i class="fa-solid fa-list-ul text-primary me-1"></i>'
        ],

];
?>
<?php if($layout == 'horizontal'):?>
<?php foreach($menus as $menu):?>
<li class="nav-item">
    <?=Html::a($menu['icon'].$menu['title'],$menu['url'],['class' => 'nav-link ' . (isset($active) && $active == $menu['active'] ? 'active' : '')])?>
</li>
<?php endforeach;?>

<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle <?=(isset($active) && $active == 'setting' ? 'active' : '')?>" href="#" id="topnav-dashboard" role="button" data-bs-toggle="dropdown"
        aria-haspopup="true" aria-expanded="false">
        <i class="fa-solid fa-gear me-1"></i> ตั้งค่า
        <i class="bx bx-chevron-down"></i>
    </a>
    <div class="dropdown-menu " aria-labelledby="topnav-dashboard">
        <?= Html::a('<i class="fa-solid fa-cash-register me-1"></i> ผู้แทนจำหน่าย', ['/sm/vendor'], ['class' => 'dropdown-item']) ?>
        <?= Html::a('<i class="fa-brands fa-product-hunt me-1"></i> วัสดุ', ['/sm/product', 'title' => 'ตั้งค่่าวัสดุ'], ['class' => 'dropdown-item']) ?>
        <?= Html::a('<i class="bi bi-box-fill me-1"></i> ทรัพย์สิน', ['/sm/asset-item', 'group' => 3, 'title' => 'ตั้งค่าครุภัณฑ์'], ['class' => 'dropdown-item']) ?>
        <?php //  Html::a('<i class="fa-solid fa-window-restore me-1"></i> หน่วยนับ', ['/sm/product-unit','title' => 'หน่วยนับ'], ['id' => 'unit', 'class' => 'dropdown-item']) ?>
        <?= Html::a('<i class="fa-solid fa-file-import me-1"></i> นำเข้า', ['/sm/vendor/import-csv'], ['class' => 'dropdown-item']) ?>
    </div>
</li>


<?php else:?>

<div class="d-flex gap-2">
    <?= Html::a('<i class="fa-solid fa-chart-simple me-1"></i> Dashbroad', ['/sm'], ['class' => 'btn btn-light']) ?>
    <?=  Html::a('<i class="bi bi-ui-checks"></i> ทะเบียนประวัติ', ['/purchase/order'], ['class' => 'btn btn-light']) ?>>
    <div class="btn-group">
        <span class="btn btn-light">
            <i class="fa-solid fa-gear"></i>
            ตั้งค่า
        </span>
        <button type="button" class="btn btn-warning dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown"
            aria-expanded="false" data-bs-reference="parent">
            <i class="bi bi-caret-down-fill"></i>
        </button>
        <ul class="dropdown-menu">
            <li><?= Html::a('<i class="fa-solid fa-cash-register me-1"></i> ผู้แทนจำหน่าย', ['/sm/vendor'], ['class' => 'dropdown-item']) ?>
            <li><?php //  Html::a('<i class="fa-solid fa-cash-register me-1"></i> กรรมการตรวจรับ', ['/sm/committee-group'], ['class' => 'dropdown-item']) ?>
            <li><?= Html::a('<i class="fa-brands fa-product-hunt me-1"></i> วัสดุ', ['/sm/product', 'title' => 'ตั้งค่่าวัสดุ'], ['class' => 'dropdown-item']) ?>
            <li><?= Html::a('<i class="bi bi-box-fill me-1"></i> ทรัพย์สิน', ['/sm/asset-item', 'group' => 3, 'title' => 'ตั้งค่าครุภัณฑ์'], ['class' => 'dropdown-item']) ?>
                <!-- <li><?php //  Html::a('<i class="bi bi-box-fill me-1"></i> จ้างเหมาบริการ', ['/sm/service-item', 'group' => 5, 'title' => 'ตั้งค่าครุภัณฑ์'], ['class' => 'dropdown-item']) ?> -->
                <!-- <li><?php Html::a('<i class="bi bi-box-fill me-1"></i> อาหารสด', ['/sm/food-item', 'group' => 6, 'title' => 'ตั้งค่าครุภัณฑ์'], ['class' => 'dropdown-item']) ?> -->
            <li><?= Html::a('<i class="fa-solid fa-window-restore me-1"></i> หน่วยนับ', ['/sm/product-unit','title' => 'หน่วยนับ'], ['id' => 'unit', 'class' => 'dropdown-item']) ?>
            <li><?= Html::a('<i class="fa-solid fa-file-import me-1"></i> นำเข้า', ['/sm/vendor/import-csv'], ['class' => 'dropdown-item']) ?>
            </li>
        </ul>
    </div>
</div>
<?php endif;?>