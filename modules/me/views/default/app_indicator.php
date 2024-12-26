 <?php

use yii\helpers\Url;

$items = [
    [
        'title' => 'แจ้งซ่อม',
        'note' => 'ระบบแจ้งซ่อมทั่วไปและทรัพย์สิน',
        'icon' => ' <i class="fa-solid fa-triangle-exclamation mb-3 text-primary fs-1"></i>',
        'url' => ['/me/repair']
    ],
    [
        'title' => 'ขอซื้อขอจ้าง',
        'note' => 'ขอซื้อขอจ้างและติดตาม',
        'icon' => '<i class="fa-solid fa-shop mb-3 text-primary fs-1"></i>',
        'url' => ['/me/purchase']
    ],
    [
        'title' => 'งานสารบรรณ',
        'note' => 'หนังสือรับ-ส่งของหน่วยงาน',
        'icon' => ' <i class="fa-solid fa-calendar-day mb-3 text-primary fs-1"></i>',
        'url' =>  ['/me/documents']
    ],
    [
        'title' => 'เบิกวัสดุ',
        'note' => 'เบิกวัสดุสำหรับในหน่วยงาน',
        'icon' => ' <i class="fa-solid fa-cart-plus mb-3 text-primary fs-1"></i>',
        'url' =>  ['/inventory']
    ],
];
?>
 <!-- App Service -->
 <div class="row mt-4">
<?php foreach($items as $item):?>
     <div class="col-6">
         <div class="card bg-light position-relative border border-primary border-4 border-top-0 border-end-0 border-start-0 border-bottom-1 zoom-in">
             
         <div class="card-body">
         <!-- <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger text-white">2</span> -->
         <a href="<?=Url::to($item['url'])?>">
             <div>
                 <div class="d-flex justify-content-between align-items-center">
                     <?=$item['icon']?>
                         <h6 class="mb-0 font-size-15"><?=$item['title']?></h6>
                         
                     </div>

                     <div>

                         <div class="d-flex align-items-end overflow-hidden">
                             <div class="flex-grow-1">
                                 <p class="text-muted mb-0 text-truncate"><?=$item['note']?></p>
                             </div>
                             <div class="flex-shrink-0" style="position: relative;">

                             </div>
                         </div>
                     </div>
                 </div>
             </div>
         </div>
         </a>      

     </div>
<?php endforeach;?>

 </div>