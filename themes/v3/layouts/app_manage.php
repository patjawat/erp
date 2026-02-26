<?php

use yii\helpers\Url;
use yii\helpers\Html;
use app\components\UserHelper;

$items = [
    [
        'title' => 'บุคลากร',
        'icon' => 'fa-regular fa-circle-user fs-1',
        'url' => ['/hr'],
        'padding' => 'p-3',
        'show' => Yii::$app->user->can('hr') ? true : false,
        // 'show' => true

    ],
    [
        'title' => 'ระบบลา',
        'icon' => 'fa-solid fa-calendar-day fs-1',
        'url' => ['/hr/leave/dashboard'],
        'padding' => 'p-3',
        'show' => Yii::$app->user->can('leave') ? true : false,
        // 'show' => true
    ],
    [
        'title' => 'สารบรรณ',
        'icon' => 'bi bi-journal-text fs-1',
        'url' => ['/dms/dashboard'],
        'padding' => 'p-2',
        'show' => Yii::$app->user->can('document') ? true : false,
        // 'show' => true
    ],
    [
        'title' => 'พัสดุ/จัดซื้อ',
        'icon' => 'bi bi-box fs-1',
        'url' => ['/sm'],
        'padding' => 'p-2',
        'show' => Yii::$app->user->can('purchase') ? true : false,
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
        'title' => 'ทรัพย์สิน',
        'icon' => 'bi bi-folder-check fs-1',
        'url' => ['/am'],
        'padding' => 'p-2',
        'show' => Yii::$app->user->can('asset') ? true : false,
        // 'show' => true
    ],
    [
        'title' => 'ยานพาหนะ',
        'icon' => 'fa-solid fa-car-side fs-1',
        'url' => ['/booking/vehicle/calendar'],
        'padding' => 'p-3',
        'show' => Yii::$app->user->can('driver') ? true : false,
        // 'show' => true
    ],
    [
        'title' => 'ห้องประชุม',
        'icon' => 'fa-solid fa-person-chalkboard fs-1',
        'url' => ['/booking/meeting/calendar'],
        'padding' => 'p-3',
        'show' => Yii::$app->user->can('meeting') ? true : false,
        // 'show' => true
    ],
    [
        'title' => 'งานซ่อมบำรุง',
        'icon' => 'fa-solid fa-screwdriver-wrench fs-2',
        'url' => ['/helpdesk/general/dashboard'],
        'padding' => 'p-3',
        'show' => Yii::$app->user->can('technician') ? true : false,
        // 'show' => true
    ],
    [
        'title' => 'ศูนย์คอม',
        'icon' => 'fa-solid fa-computer fs-2',
        'url' => ['/helpdesk/computer/dashboard'],
        'padding' => 'p-3',
        'show' => Yii::$app->user->can('computer') ? true : false,
        // 'show' => true
    ],
    [
        'title' => 'เครื่องมือแพทย์',
        'icon' => 'fa-solid fa-briefcase-medical fs-2',
        'url' => ['/helpdesk/medical/dashboard'],
        'padding' => 'p-3',
        'show' => Yii::$app->user->can('medical') ? true : false,
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
        'title' => 'อบรม/ประชุม',
        'icon' => 'fa-solid fa-briefcase fs-1',
        'url' => ['/development/default/dashboard'],
        'padding' => 'p-3',
        'show' => (Yii::$app->user->can('hr') || Yii::$app->user->can('user')) ? true : false,
    ],
    [
        'title' => 'แผนงาน',
        'icon' => 'fa-solid fa-ranking-star fs-1',
        'url' => ['/plan/dashboard'],
        'padding' => 'p-3',
        'show' => Yii::$app->user->can('plan') ? true : false,
    ],



];
?>





<div class="d-none d-md-inline-flex ms-2 dropdown">
    <button data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight" class="btn header-item notify-icon">
        <i class="fa-solid fa-bars-progress"></i>
    </button>

</div>


<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasRightLabel"><i class="fa-solid fa-bars-progress"></i> Admin Menu</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="container">
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-2 g-3 mt-2">
                <?php foreach ($items as $item): ?>
                    <?php if ($item['show']): ?>
                        <div class="col mt-1">
                            <a href="<?php echo Url::to($item['url']) ?>">
                                <div class="card border-0 shadow-sm hover-card bg-light">
                                    <div
                                        class="d-flex justify-content-center align-items-center  bg-primary <?php echo $item['padding'] ?> rounded-top">
                                        <i class="<?php echo $item['icon'] ?> text-white"></i>
                                    </div>
                                    <div class="card-body">

                                        <h6 class="text-center mb-0 text-dark"><?php echo $item['title'] ?></p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

            </div>
        </div>

    </div>
</div>