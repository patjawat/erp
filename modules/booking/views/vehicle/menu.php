<?php

use yii\helpers\Html;

$layout = app\components\SiteHelper::getInfo()['layout'];
$menus = [
    [
        'title' => 'ขอใช้รถพยาบาล',
        'active' => 'ambulance',
        'url' => ['/booking/vehicle/ambulance'],
        'icon' => '<i class="fa-solid fa-truck-medical me-2"></i>'
    ],
    [
        'title' => 'ทะเบียนจัดรถยนต์',
        'active' => 'work',
        'url' => ['/booking/vehicle/work'],
        'icon' => '<i class="fa-solid fa-car-on me-2"></i>'
    ],
    [
        'title' => 'ทะเบียนครุภัณฑ์',
        'active' => 'asset',
        'url' => ['/booking/asset'],
        'icon' => '<i class="bi bi-ui-checks me-2"></i>'

    ],
    [
        'title' => 'ตั้งค่าแบบฟอร์ม',
        'active' => 'setting',
        'url' => ['/booking/vehicle-form-layout'],
        'icon' => '<i class="fa-solid fa-gear me-2"></i>'
    ],

];
?>

<li class="nav-item">
    <?= Html::a('<i class="fa-solid fa-gauge-high me-1"></i>Dashboard', ['/booking/vehicle/dashboard'], ['class' => 'nav-link']) ?>
</li>
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle <?= (isset($active) && $active == 'official' ? 'active' : '') ?>" href="#"
            id="topnav-dashboard" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="bi bi-ui-checks me-2"></i> ขอใช้รถทั่วไป
            <i class="bx bx-chevron-down"></i>
        </a>
        <div class="dropdown-menu" aria-labelledby="topnav-dashboard">
            <?= Html::a('<i class="bi bi-ui-checks me-2"></i> ทะเบียนคำขอใช้รถ', ['/booking/vehicle'], ['class' => 'dropdown-item']) ?>
            <?= Html::a('<i class="fa-regular fa-calendar me-2"></i> ปฏิทินขอใช้รถยนต์ ', ['/booking/vehicle/calendar'], ['class' => 'dropdown-item']) ?>
            <?= Html::a('<i class="bi bi-ui-checks me-2"></i> ทะเบียนการจัดสรรรถ (พขร.)', ['/booking/vehicle/work'], ['class' => 'dropdown-item']) ?>
        </div>
    </li>

<?php if ($layout == 'horizontal'): ?>
    <?php foreach ($menus as $menu): ?>
        <li class="nav-item">
            <?= Html::a($menu['icon'] . $menu['title'], $menu['url'], ['class' => 'nav-link ' . (isset($active) && $active == $menu['active'] ? 'active' : '')]) ?>
        </li>
    <?php endforeach; ?>



<?php else: ?>

    <div class="d-flex gap-2">
        <?= Html::a('<i class="fa-solid fa-gauge-high"></i> Dashboard', ['/booking/vehicle/dashboard'], ['class' => 'btn btn-light']) ?>
        <?= Html::a('<i class="fa-solid fa-car"></i> ทะเบียนใช้รถยนต์ทั่วไป', ['/booking/vehicle/index'], ['class' => 'btn btn-light']) ?>
        <?= Html::a('<i class="fa-solid fa-truck-medical"></i> ทะเบียนใช้รถพยาบาล', ['/booking/vehicle/ambulance'], ['class' => 'btn btn-light']) ?>
        <?= Html::a('<i class="fa-solid fa-user-tag"></i> ทะเบียนจัดสรร', ['/booking/vehicle/work'], ['class' => 'btn btn-light']) ?>
        <?= Html::a('<i class="fa-solid fa-calendar"></i> ปฏิทิน', ['/booking/vehicle/calendar'], ['class' => 'btn btn-light']) ?>
    </div>
<?php endif; ?>