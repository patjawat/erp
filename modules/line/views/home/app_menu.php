<?php
use yii\helpers\Url;
use yii\helpers\Html;

$items = [
    [
        'title' => 'แจ้งซ่อม',
        'url' => ['/'],
        'icon' => '<i class="fa-solid fa-screwdriver-wrench fs-1"></i>'
    ],
    [
        'title' => 'ขอลา',
        'url' => ['/'],
        'icon' => '<i class="fa-solid fa-screwdriver-wrench fs-1"></i>'
    ],
    [
        'title' => 'จองรถ',
        'url' => ['/'],
        'icon' => '<i class="fa-solid fa-car-side fs-1"></i>'
    ],
    [
        'title' => 'ห้องประชุม',
        'url' => ['/'],
        'icon' => '<i class="fa-solid fa-person-chalkboard fs-1"></i>'
    ]
];
?>
<div class="p-2 mb-3">
        <h6 class="text-white mb-2">App Menu</h6>
        <div class="overflow-scroll d-flex flex-row borde-0 gap-4 mt-4"
            style="white-space: nowrap; max-width: 100%; height: 100px;">
            <?php foreach($items as $item):?>
            <div class="d-flex flex-column gap-3 border-0 text-white">
                <div class=" bg-primary rounded-4 p-3 shadow border border-white bg-opacity-10">
                   <?php echo $item['icon']?>
                </div>
                <p class="text-center"><?php echo $item['title']?></p>
            </div>
            <?php endforeach?>
        </div>
    </div>