<?php
use yii\helpers\Url;
use app\components\EmployeeHelper;
$menuItems = [
    [
        'title' => '<i class="bi bi-bar-chart"></i> Dashboard',
        'url' => '/hr'
    ],
    [
        'title' => '<i class="bi bi-person-check-fill"></i> ทะเบียนประวัติ <span class="badge text-bg-secondary text-light rounded-pill align-text-bottom">'.EmployeeHelper::Summary()['total'].'</span>',
        'url' => '/hr/employees'
    ],
    [
        'title' => '<i class="bi bi-gear-fill"></i> ตั้งค่าบุคลากร',
        'url' => '/hr/setting'
    ]
];
?>
  <nav class="nav" aria-label="Secondary navigation">
    <?php foreach($menuItems as $menu):?>
    <a class="nav-link active" aria-current="page" href="<?=Url::to([$menu['url']])?>"><?=$menu['title']?></a>
    <?php endforeach;?>
  </nav>
