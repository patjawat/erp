 <?php

use yii\helpers\Url;

$items = [
    [
        'title' => 'แจ้งซ่อม',
        'icon' => 'fa-solid fa-circle-exclamation fs-1',
        'url' => ['/me/repair'],
    ],
    [
        'title' => 'ขอซื้อขอจ้าง',
        'icon' => 'fa-solid fa-bag-shopping fs-1',
        'url' => ['/me/purchase'],
    ],
    [
        'title' => 'จองรถ',
        'icon' => 'fa-solid fa-car fs-1',
        'url' => ['/me/booking-vehicle/calendar'],
    ],
    [
        'title' => 'ห้องประชุม',
        'icon' => 'fa-solid fa-person-chalkboard fs-1',
        'url' => ['/me/booking-meeting/calendar'],
    ],    [
        'title' => 'คลังหน่วยงาน',
        'icon' => 'bi bi-shop fs-2',
        'url' => ['/me/store-v2/set-warehouse'],
        // 'url' => ['/me/store-v2/index'],
    ]
   
];
?>

 <!-- App Service -->
 <div class="container">
                <div class="row row-cols-1 row-cols-sm- row-cols-md-2 g-3 mt-2">
                    <?php foreach($items as $item):?>
                    <div class="col mt-1">
                        <a href="<?php echo Url::to($item['url'])?>">
                            <div class="card border-0 shadow-sm hover-card bg-light">
                                <div
                                    class="d-flex justify-content-center align-items-center bg-primary opacity-75 p-3 rounded-top">
                                    <i class="<?php echo $item['icon']?> text-white"></i>
                                </div>
                                <div class="card-body p-1">
                    
                                    <p class="text-center fw-semibold mb-0"><?php echo $item['title']?></p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php endforeach;?>
                    
                </div>
            </div>