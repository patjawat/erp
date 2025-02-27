<?php
use yii\helpers\Url;
use app\components\NotificationHelper;
$notify = NotificationHelper::Info();
$totalLeave = $notify['leave']['total'];
$totalPurchase = $notify['purchase']['total'];
$this->title = "รายการที่ต้องอนุมัติและตรวจสอบ";
?>
<?php $this->beginBlock('page-title'); ?>
<!-- <i class="bi bi-ui-checks"></i>-->
<i class="fa-solid fa-bell noti-animate"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?php  echo $this->render('@app/modules/me/menu') ?>
<?php $this->endBlock(); ?>


<div class="container">

    <div class="row row-cols-1 row-cols-sm-6 row-cols-md-6 g-3">
        <div class="col">
            <a href="<?php echo Url::to(['/me/approve-leave'])?>">
                <div class="card border-0 shadow-sm hover-card position-relative">
                <?php if($totalLeave >=1):?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger text-white"><?php echo $totalLeave?></span>
                    <?php endif;?>
                    <div class="d-flex justify-content-center align-items-center bg-secondary p-4 rounded-top">
                        <i class="fa-solid fa-calendar-day fs-1 text-white"></i>
                    </div>
                    <div class="card-body">
                        <h6 class="text-center">ขออนุมัติวันลา</h6>
                    </div>
                </div>
            </a>
        </div>

        <div class="col">
            <a href="<?php echo Url::to(['/me/approve-purchase'])?>">
            <div class="card border-0 shadow-sm hover-card position-relative">
                <?php if($totalPurchase >=1):?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger text-white"><?php echo $totalPurchase?></span>
            <?php endif;?>
                    <div class="d-flex justify-content-center align-items-center bg-secondary p-4 rounded-top">
                        <i class="fa-solid fa-calendar-day fs-1 text-white"></i>
                    </div>
                    <div class="card-body">
                        <h6 class="text-center">ขออนุมัติจัดซื้อจัดจ้าง</h6>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>