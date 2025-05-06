<?php
use yii\helpers\Url;
use app\components\ApproveHelper;
$notify = ApproveHelper::Info();
$totalLeave = $notify['leave']['total'];
$totalBookingCar = $notify['booking_car']['total'];
$totalPurchase = $notify['purchase']['total'];
$totalStock = $notify['stock']['total'];
$totalDevelopment= $notify['development']['total'];
$this->title = "รายการที่ต้องอนุมัติและตรวจสอบ";
?>
<?php $this->beginBlock('page-title'); ?>
<!-- <i class="bi bi-ui-checks"></i>-->
<i class="fa-solid fa-bell noti-animate"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?php  echo $this->render('@app/modules/me/menu') ?>
<?php $this->endBlock(); ?>


<?php
$items = [
    [
        'label' => 'ขออนุมัติวันลา',
        'url' => ['/approve/leave'],
        'icon' => '<i class="fa-solid fa-calendar-day fs-1 text-white"></i>',
        'count' => $totalLeave,
    ],
    [
        'label' => 'ขออนุญาตใช้รถ',
        'url' => ['/approve/vehicle'],
        'icon' => '<i class="fa-solid fa-car fs-1 text-white"></i>',
        'count' => $totalBookingCar,
    ],
    [
        'label' => 'ขออนุมัติจัดซื้อจัดจ้าง',
        'url' => ['/approve/approve','name' => 'purchase'],
        'icon' => '<i class="fa-solid fa-shopping-cart fs-1 text-white"></i>',
        'count' => $totalPurchase,
    ],
    [
        'label' => 'ขออนุมัติเบิกวัสดุ',
        'url' => ['/approve/main-stock'],
        'icon' => '<i class="fa-solid fa-box fs-1 text-white"></i>',
        'count' => $totalStock,
    ],
    [
        'label' => 'อบรม/ประชุม/ดูงาน',
        'url' => ['/approve/development'],
        'icon' => '<i class="fa-solid fa-briefcase fs-1 text-white"></i>',
        'count' => $totalDevelopment,
    ]
    
];
?>
<div class="container">

    <div class="row row-cols-1 row-cols-sm-6 row-cols-md-6 g-3">
        <?php foreach($items as $item):?>
        <div class="col">
            <a href="<?php echo Url::to($item['url'])?>">
                <div class="card border-0 shadow-sm hover-card position-relative">
                <?php if($item['count'] >=1):?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger text-white"><?php echo $item['count']?></span>
                    <?php endif;?>
                    <div class="d-flex justify-content-center align-items-center bg-secondary p-4 rounded-top">
                       <?=$item['icon']?>
                    </div>
                    <div class="card-body">
                        <h6 class="text-center"><?=$item['label']?></h6>
                    </div>
                </div>
            </a>
        </div>
<?php endforeach;?>
    </div>
</div>