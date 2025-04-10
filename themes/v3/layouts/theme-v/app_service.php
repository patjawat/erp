<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\components\UserHelper;


$items = [
    [
        'title' => 'ขอลา',
        'icon' => 'fa-solid fa-calendar-day fs-1',
        'url' => ['/me/leave/create','title' => '<i class="fa-solid fa-calendar-plus"></i> บันทึกขออนุมัติการลา'],
        'modal' => true,
        'size' => 'modal-lg'
    ],
    [
        'title' => 'จองรถ',
        'icon' => 'fa-solid fa-car-side fs-1',
        // 'url' => ['/me/booking-car/select-type','title' => '<i class="bi bi-ui-checks-grid"></i> เลือกประเภทรถที่ต้องการใช้งาน'],
        'url' => ['/me/booking-vehicle/create','title' => '<i class="bi bi-ui-checks-grid"></i> ขอใช้ยานพาหนะ(จองรถ)'],
        'modal' => true,
        'size' => 'modal-lg'
    ],
    [
        'title' => 'จองห้องประชุม',
        'icon' => 'fa-solid fa-person-chalkboard fs-1',
    //    'url' => ['/me/booking-meeting/dashboard','title' => 'ขอใช้ห้องประชุม'],
       'url' => ['/me/booking-meeting/create','date_start' => date('Y-m-d'),'title' => '<i class="fa-solid fa-calendar-plus"></i> ขอให้ห้องประชุม'],['class' => 'btn btn-primary shadow rounded-pill open-modal','data' => ['size' => 'modal-xl']],
        'modal' => true,
        'size' => 'modal-xl'
    ],
    [
        'title' => 'แจ้งซ่อม',
        'icon' => 'fa-solid fa-circle-exclamation fs-1',
        'url' => ['/helpdesk/default/repair-select', 'title' => '<i class="fa-regular fa-circle-check"></i> เลือกประเภทการซ่อม'],
        'modal' => true,
        'size' => 'modal-md'
    ],
    [
        'title' => 'ขอซื้อขอจ้าง',
        'icon' => 'fa-solid fa-bag-shopping fs-1',
        'url' => ['/purchase/pr-order/create', 'name' => 'order', 'title' => '<i class="bi bi-plus-circle"></i> เพิ่มใบขอซื้อ-ขอจ้าง'],
        'modal' => true,
        'size' => 'modal-md'
    ],
];
?>
<div class="d-none d-lg-inline-flex ms-2 dropdown">
    <button data-bs-toggle="dropdown" aria-haspopup="true" type="button" id="page-header-app-dropdown"
        aria-expanded="false" class="btn header-item notify-icon">
        <i class="bi bi-ui-checks-grid"></i>
    </button>
    <div aria-labelledby="page-header-app-dropdown" class="dropdown-menu-lg dropdown-menu-right dropdown-menu"
        style="width: 600px;">
        <div class="px-lg-2">

            <h5 class="text-center mt-3"><i class="bi bi-ui-checks-grid"></i> เมนูด่วน</h5>

            <div class="container">
                <div class="row row-cols-1 row-cols-sm-4 row-cols-md-4 g-3 mt-2">
                    <?php foreach($items as $item):?>
                    <div class="col mt-1">
                        <a href="<?php echo Url::to($item['url'])?>" class="<?php echo $item['modal'] ? 'open-modal' : null;?>" data-size="<?php echo $item['modal'] ? $item['size'] : null;?>">
                            <div class="card border-0 shadow-sm hover-card bg-light">
                                <div
                                    class="d-flex justify-content-center align-items-center bg-warning  p-4 rounded-top">
                                    <i class="<?php echo $item['icon']?>"></i>
                                </div>
                                <div class="card-body p-1">
                    
                                    <p class="text-center fw-semibold mb-0"><i class="fa-solid fa-plus"></i> <?php echo $item['title']?></p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php endforeach;?>
                    
                </div>
            </div>
            
            <!-- App Service -->
            <div class="row p-3">
                <!-- <div class="col-4 mt-1">
                    <a href="<?= Url::to(['/helpdesk/default/repair-select', 'title' => '<i class="fa-regular fa-circle-check"></i> เลือกประเภทการซ่อม']); ?>"
                        class="open-modal" data-title="xxx">
                        <div
                            class="d-flex flex-column align-items-center justify-content-center bg-light p-4 rounded-2">
                            <i class="fa-solid fa-triangle-exclamation fs-2"></i>
                            <div>แจ้งซ่อม</div>
                        </div>
                    </a>
                </div>

                <div class="col-4 mt-1">
                    <a href="<?= Url::to(['/purchase/pr-order/create', 'name' => 'order', 'title' => '<i class="bi bi-plus-circle"></i> เพิ่มใบขอซื้อ-ขอจ้าง']); ?>"
                        class="open-modal" data-title="xxx" data-size="modal-md">
                        <div
                            class="d-flex flex-column align-items-center justify-content-center bg-light p-4 rounded-2">
                            <i class="fa-solid fa-bag-shopping fs-2"></i>
                            <div>ขอซื้อ-ขอจ้าง</div>
                        </div>
                    </a>
                </div>

                <div class="col-4 mt-1">
                    <a href="<?= Url::to(['/hr/leave/create','title' => '<i class="fa-solid fa-calendar-plus"></i> บันทึกขออนุมัติการลา']); ?>" class="open-modal" data-title="xxx" data-size="modal-lg">
                        <div
                            class="d-flex flex-column align-items-center justify-content-center bg-light p-4 rounded-2">
                            <i class="fa-solid fa-calendar-day fs-2"></i>
                            <div>ขอลา</div>
                        </div>
                    </a>
                </div> -->
                <!-- <div class="col-4">
                                <div
                                    class="d-flex flex-column align-items-center justify-content-center bg-light p-3 rounded-2">
                                    <?= html::img('@web/images/svg-icons/leave.svg', ['width' => '50px']) ?>
                                    <div>ระบบลา</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div
                                    class="d-flex flex-column align-items-center justify-content-center bg-light p-3 rounded-2">
                                    <?= html::img('@web/images/svg-icons/booking.svg', ['width' => '50px']) ?>
                                    <div>ระบบจองรถ</div>
                                </div>
                            </div>

                            <div class="col-4">
                                <div
                                    class="d-flex flex-column align-items-center justify-content-center bg-light p-3 rounded-2">
                                    <?= html::img('@web/images/svg-icons/meeting.svg', ['width' => '50px']) ?>
                                    <div>ระบบจองห้องประชุม</div>
                                </div>
                            </div>

                            <div class="col-4 mt-3">
                                <div
                                    class="d-flex flex-column align-items-center justify-content-center bg-light p-3 rounded-2">
                                    <?= html::img('@web/images/svg-icons/document.svg', ['width' => '50px']) ?>
                                    <div>ระบบสารบัญ</div>
                                </div>
                            </div>
                            <div class="col-4 mt-3">
                                <div
                                    class="d-flex flex-column align-items-center justify-content-center bg-light p-3 rounded-2">
                                    <?= html::img('@web/images/svg-icons/check-list.svg', ['width' => '50px']) ?>
                                    <div>ระบบความเสี่ยง</div>
                                </div>
                            </div> -->


            </div>

        </div>
    </div>
</div>