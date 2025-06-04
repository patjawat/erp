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
        [
    'title' => 'ตั้งค่าห้องประชุม',
    'active' => 'room',
    'url' => ['/booking/room'],
    'icon' => '<i class="fa-solid fa-gear text-primary me-1"></i>'
    ]
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
    <?=Html::a('<i class="fa-solid fa-gauge-high"></i> Dashboard',['/booking/meeting/dashboard'],['class' => 'btn btn-light'])?>
    <?=Html::a('<i class="bi bi-ui-checks"></i> ทะเบียนประวัติ',['/booking/meeting/index'],['class' => 'btn btn-light'])?>
    <?=Html::a('<i class="fa-solid fa-calendar"></i> ปฏิทิน',['/booking/meeting/calendar'],['class' => 'btn btn-light'])?>
    <?=Html::a('<i class="fa-solid fa-gear"></i> ตั้งค่าห้องประชุม',['/booking/room'],['class' => 'btn btn-light'])?>
</div>
<?php endif;?>