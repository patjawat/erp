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
        'title' => 'เบิกวัสดุ',
        'icon' => 'fa-solid fa-cart-shopping fs-1',
        'url' => ['/inventory'],
    ],
    [
        'title' => 'จองรถ',
        'icon' => 'fa-solid fa-route fs-1',
        'url' => ['/me/booking-car'],
    ],
    [
        'title' => 'จองห้องประชุม',
        'icon' => 'fa-solid fa-person-chalkboard fs-1',
        'url' => ['/me/booking-room'],
    ],
    [
        'title' => 'ความเสี่ยง',
        'icon' => 'fa-solid fa-triangle-exclamation fs-1',
        'url' => ['/me/risk'],
    ],
   
];
?>
 <!-- App Service -->
 <div class="container">
                <div class="row row-cols-1 row-cols-sm-4 row-cols-md-3 g-3 mt-2">
                    <?php foreach($items as $item):?>
                    <div class="col mt-1">
                        <a href="<?php echo Url::to($item['url'])?>">
                            <div class="card border-0 shadow-sm hover-card bg-light">
                                <div
                                    class="d-flex justify-content-center align-items-center bg-primary opacity-75 p-4 rounded-top">
                                    <i class="<?php echo $item['icon']?> text-white"></i>
                                </div>
                                <div class="card-body">
                    
                                    <h6 class="text-center"><?php echo $item['title']?></h6>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php endforeach;?>
                    
                </div>
            </div>