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
        'title' => 'ห้องประชุม',
        'icon' => 'fa-solid fa-person-chalkboard fs-1',
        'url' => ['/me/booking-room'],
    ]
   
];
?>

 <!-- App Service -->
 <div class="container">
 <div class="row row-cols-2 row-cols-sm-2 row-cols-md-3 g-3">
                    <?php foreach($items as $item):?>
                        <div class="col">
            <a href="/settings/company">
                <div class="card border-0 shadow-sm hover-card">
                    <div class="d-flex justify-content-center align-items-center bg-secondary p-4 rounded-top">
                        <i class="fa-solid fa-house-medical-flag fs-1 text-white"></i>
                    </div>
                    <div class="card-body">
                        <h6 class="text-center">ข้อมูลองค์กร</h6>
                    </div>
                </div>
            </a>
        </div>
                    <?php endforeach;?>
                    
                </div>
            </div>