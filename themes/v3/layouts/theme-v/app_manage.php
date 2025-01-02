<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\components\UserHelper;

$items = [
    [
        'title' => 'งานซ่อมบำรุง',
        'icon' => 'fa-solid fa-screwdriver-wrench fs-3',
        'url' => ['/helpdesk/general'],
    ],
    [
        'title' => 'ศูนย์คอมพิวเตอร์',
        'icon' => 'fa-solid fa-computer fs-3',
        'url' => ['/helpdesk/computer'],
    ],
    [
        'title' => 'ศูนย์เครื่องมือแพทย์',
        'icon' => 'fa-solid fa-briefcase-medical fs-3',
        'url' => ['/helpdesk/medical'],
    ],
    [
        'title' => 'ระบบลา',
        'icon' => 'bi bi-calendar-check-fill fs-3',
        'url' => ['/hr/leave'],
    ],
    [
        'title' => 'สารบรรณ',
        'icon' => 'bi bi-journal-text fs-3',
        'url' => ['/dms/dashboard'],
    ],
    [
        'title' => 'คลัง',
        'icon' => 'fa-solid fa-cubes-stacked fs-1',
        'url' => ['/inventory'],
    ],
    [
        'title' => 'พัสดุ',
        'icon' => 'bi bi-box fs-3',
        'url' => ['/sm'],
    ],
    [
        'title' => 'บุคลากร',
        'icon' => 'fa-regular fa-circle-user fs-1',
        'url' => ['/hr'],
    ],
    [
        'title' => 'ทรัพย์สิน',
        'icon' => 'bi bi-folder-check fs-3',
        'url' => ['/am'],
    ]
];
?>
<div class="d-none d-lg-inline-flex ms-2 dropdown" data-aos="zoom-in" data-aos-delay="100">
    <button data-bs-toggle="dropdown" aria-haspopup="true" type="button" id="page-header-app-dropdown"
        aria-expanded="false" class="btn header-item notify-icon">
        <i class="fa-solid fa-bars-progress"></i>
    </button>
    <div aria-labelledby="page-header-app-dropdown" class="dropdown-menu-lg dropdown-menu-right dropdown-menu"
        style="width: 600px;">
        <div class="px-lg-2">
            <h5 class="text-center mt-3"><i class="fa-solid fa-bars-progress"></i> ระบบงาน</h5>

           

            <div class="container">
                <div class="row row-cols-1 row-cols-sm-4 row-cols-md-3 g-3 mt-2">
                    <?php foreach($items as $item):?>
                    <div class="col mt-1">
                        <a href="<?php echo Url::to($item['url'])?>">
                            <div class="card border-0 shadow-sm hover-card bg-light">
                                <div
                                    class="d-flex justify-content-center align-items-center  bg-primary opacity-75 p-4 rounded-top">
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

            
        </div>
    </div>
</div>