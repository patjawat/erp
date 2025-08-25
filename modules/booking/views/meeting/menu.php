<?php

use yii\helpers\Html;

$layout = app\components\SiteHelper::getInfo()['layout'];
$menus = [
    [
        'title' => 'Dashboard',
        'active' => 'dashboard',
        'url' => ['/booking/meeting/dashboard'],
        'icon' => '<i class="fa-solid fa-gauge-high text-primary me-1"></i>'
    ],
    [
        'title' => 'ทะเบียนประวัติ',
        'active' => 'index',
        'url' => ['/booking/meeting/index'],
        'icon' => '<i class="fa-solid fa-list-ul text-primary me-1"></i>'
    ],
    [
        'title' => 'ปฏิทิน',
        'active' => 'calendar',
        'url' => ['/booking/meeting/calendar'],
        'icon' => '<i class="fa-solid fa-calendar text-primary me-1"></i>'
    ],
];
?>
<?php if ($layout == 'horizontal'): ?>
    <?php foreach ($menus as $menu): ?>
        <li class="nav-item">
            <?= Html::a($menu['icon'] . $menu['title'], $menu['url'], ['class' => 'nav-link ' . (isset($active) && $active == $menu['active'] ? 'active' : '')]) ?>
        </li>
    <?php endforeach; ?>

    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle <?= (isset($active) && $active == 'setting' ? 'active' : '') ?>" href="#" id="topnav-dashboard" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fa-solid fa-gear me-1 fs-5"></i> การตั้งค่า
            <i class="bx bx-chevron-down"></i>
        </a>
        <div class="dropdown-menu" aria-labelledby="topnav-dashboard">
            <?= Html::a('<i class="fa-solid fa-angle-right me-1"></i> ห้องประชุม', ['/booking/room'], ['class' => 'dropdown-item']) ?>
            <?= Html::a('<i class="fa-solid fa-angle-right me-1"></i> รูปแบบห้องประชุม', ['/booking/room-layout'], ['class' => 'dropdown-item']) ?>
            <?= Html::a('<i class="fa-solid fa-angle-right me-1"></i> สถานะห้องประชุม', ['/booking/meeting-status'], ['class' => 'dropdown-item']) ?>
            <?= Html::a('<i class="fa-solid fa-angle-right me-1"></i> รายการอุปกรณ์', ['/booking/room-device'], ['class' => 'dropdown-item']) ?>
        </div>
    </li>


<?php else: ?>
    <div class="d-flex gap-2">
        <?= Html::a('<i class="fa-solid fa-gauge-high"></i> Dashboard', ['/booking/meeting/dashboard'], ['class' => 'btn btn-light']) ?>
        <?= Html::a('<i class="bi bi-ui-checks"></i> ทะเบียนประวัติ', ['/booking/meeting/index'], ['class' => 'btn btn-light']) ?>
        <?= Html::a('<i class="fa-solid fa-calendar"></i> ปฏิทิน', ['/booking/meeting/calendar'], ['class' => 'btn btn-light']) ?>
        <?= Html::a('<i class="fa-solid fa-gear"></i> ตั้งค่าห้องประชุม', ['/booking/room'], ['class' => 'btn btn-light']) ?>
    </div>
<?php endif; ?>