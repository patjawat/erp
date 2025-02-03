<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\components\UserHelper;

$items = [
    [
        'title' => 'งานซ่อมบำรุง',
        'icon' => 'fa-solid fa-screwdriver-wrench fs-2',
        'url' => ['/helpdesk/general'],
        'padding' => 'p-3',
        'show' => Yii::$app->user->can('technician') ? true : false,
        // 'show' => true
    ],
    [
        'title' => 'ศูนย์คอม',
        'icon' => 'fa-solid fa-computer fs-2',
        'url' => ['/helpdesk/computer'],
        'padding' => 'p-3',
        'show' => Yii::$app->user->can('computer') ? true : false,
        // 'show' => true
    ],
    [
        'title' => 'เครื่องมือแพทย์',
        'icon' => 'fa-solid fa-briefcase-medical fs-2',
        'url' => ['/helpdesk/medical'],
        'padding' => 'p-3',
        'show' => Yii::$app->user->can('medical') ? true : false,
        // 'show' => true
    ],
    [
        'title' => 'ระบบลา',
        'icon' => 'fa-solid fa-calendar-day fs-2',
        'url' => ['/hr/leave/index','status' => 'Checking'],
        'padding' => 'p-3',
        'show' => Yii::$app->user->can('leave') ? true : false,
        // 'show' => true
    ],
    [
        'title' => 'สารบรรณ',
        'icon' => 'bi bi-journal-text fs-1',
        'url' => ['/dms/documents','document_group' => 'receive'],
        'padding' => 'p-2',
        'show' => Yii::$app->user->can('document') ? true : false,
        // 'show' => true
    ],
    [
        'title' => 'คลัง',
        'icon' => 'fa-solid fa-cubes-stacked fs-1',
        'url' => ['/inventory'],
        'padding' => 'p-3',
        'show' => Yii::$app->user->can('warehouse') ? true : false,
        // 'show' => true
        
    ],
    [
        'title' => 'พัสดุ',
        'icon' => 'bi bi-box fs-1',
        'url' => ['/sm'],
        'padding' => 'p-2',
        'show' => Yii::$app->user->can('purchase') ? true : false,
        // 'show' => true
    ],
    [
        'title' => 'บุคลากร',
        'icon' => 'fa-regular fa-circle-user fs-1',
        'url' => ['/hr'],
        'padding' => 'p-3',
        'show' => Yii::$app->user->can('hr') ? true : false,
        // 'show' => true
        
    ],
    [
        'title' => 'ทรัพย์สิน',
        'icon' => 'bi bi-folder-check fs-1',
        'url' => ['/am'],
        'padding' => 'p-2',
        'show' => Yii::$app->user->can('asset') ? true : false,
        // 'show' => true
    ],
    [
        'title' => 'การเงิน',
        'icon' => 'fa-solid fa-calculator fs-1',
        'url' => ['/finance'],
        'padding' => 'p-3',
        'show' => Yii::$app->user->can('finance') ? true : false,
        // 'show' => true
    ],
    [
        'title' => 'ยานพาหนะ',
        'icon' => 'fa-solid fa-car-side fs-1',
        'url' => ['/booking/car'],
        'padding' => 'p-3',
        'show' => Yii::$app->user->can('booking-car') ? true : false,
        // 'show' => true
    ],
    [
        'title' => 'ห้องประชุม',
        'icon' => 'fa-solid fa-person-chalkboard fs-1',
        'url' => ['/booking/conference'],
        'padding' => 'p-3',
        'show' => Yii::$app->user->can('booking-conference') ? true : false,
        // 'show' => true
    ]
    
];
?>
<div class="d-none d-lg-inline-flex ms-2 dropdown">
    <button data-bs-toggle="dropdown" aria-haspopup="true" type="button" id="page-header-app-dropdown"
        aria-expanded="false" class="btn header-item notify-icon">
        <i class="fa-solid fa-bars-progress"></i>
    </button>
    <div aria-labelledby="page-header-app-dropdown" class="dropdown-menu-lg dropdown-menu-right dropdown-menu"
        style="width: 600px;">
        <div class="px-lg-2">
            <h5 class="text-center mt-3"><i class="fa-solid fa-bars-progress"></i> ระบบงาน</h5>
            <div class="container">
                <div class="row row-cols-1 row-cols-sm-4 row-cols-md-4 g-3 mt-2">
                    <?php foreach ($items as $item): ?>
                    <?php if($item['show']):?>
                    <div class="col mt-1">
                        <a href="<?php echo Url::to($item['url']) ?>">
                            <div class="card border-0 shadow-sm hover-card bg-light">
                                <div
                                    class="d-flex justify-content-center align-items-center  bg-primary opacity-75 <?php echo $item['padding'] ?> rounded-top">
                                    <i class="<?php echo $item['icon'] ?> text-white"></i>
                                </div>
                                <div class="card-body">

                                    <p class="text-center fw-semibold mb-0"><?php echo $item['title'] ?></p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php  endif;?>
                    <?php endforeach; ?>

                </div>
            </div>


        </div>
    </div>
</div>