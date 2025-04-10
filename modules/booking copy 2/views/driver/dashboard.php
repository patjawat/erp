<?php
$this->title = "ระบบยานาหนะ"
?>
<?php $this->beginBlock('icon'); ?>
<i class="fa-solid fa-chart-pie fs-1"></i>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-title'); ?>
<!-- <i class="bi bi-ui-checks"></i>-->
 <?= $this->title; ?>
<?php $this->endBlock(); ?>


<?php $this->beginBlock('sub-title'); ?>
Dashboard
<?php $this->endBlock(); ?>


<?php  echo $this->render('menu') ?>

<div class="row">
    <div class="col-3">
        <div class="card hover-card-under border-4 border-start-0 border-end-0 border-top-0 border-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between gap-1 mb-0">
                    <span class="h2 fw-semibold"><?php echo number_format($searchModel->SummaryCarType()['general'],0)?></span>
                    <div class="relative">
                        <i class="fa-solid fa-car fs-1"></i>
                    </div>
                </div>
                <div class="d-flex justify-content-between gap-1 mb-0">
                    <h6>ทั่วไป</h6>
                </div>
            </div>
        </div>
    </div>
    <div class="col-3">
        <div class="card hover-card-under border-4 border-start-0 border-end-0 border-top-0 border-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between gap-1 mb-0">
                    <span class="h2 fw-semibold"><?php echo number_format($searchModel->SummaryCarType()['refer'],0)?></span>
                    <div class="relative">
                        <i class="fa-solid fa-truck-medical fs-1"></i>
                    </div>
                </div>
                <div class="d-flex justify-content-between gap-1 mb-0">
                    <h6>REFER</h6>
                </div>
            </div>
        </div>
    </div>
    <div class="col-3">
        <div class="card hover-card-under border-4 border-start-0 border-end-0 border-top-0 border-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between gap-1 mb-0">
                    <span class="h2 fw-semibold"><?php echo number_format($searchModel->SummaryCarType()['ems'],0)?></span>
                    <div class="relative">
                        <i class="fa-solid fa-truck fs-1"></i>
                    </div>
                </div>
                <div class="d-flex justify-content-between gap-1 mb-0">
                    <h6>EMS</h6>
                </div>
            </div>
        </div>
    </div>
    <div class="col-3">
        <div class="card hover-card-under border-4 border-start-0 border-end-0 border-top-0 border-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between gap-1 mb-0">
                    <span class="h2 fw-semibold"><?php echo number_format($searchModel->SummaryCarType()['normal'],0)?></span>
                    <div class="relative">
                        <i class="fa-solid fa-car-side fs-1"></i>
                    </div>
                </div>
                <div class="d-flex justify-content-between gap-1 mb-0">
                    <h6>รับ-ส่ง</h6>
                </div>
            </div>
        </div>
    </div>
</div>
<div
    class="alert alert-primary p-2"role="alert"
>

<div class="d-flex justify-content-between align-items-center gap-3">
    
    <h5 class="text-center text-primary"><i class="fa-solid fa-chart-simple fs-2"></i> ข้อมูลแผนภูมิข้อมูลการใช้ยานพาหนะ</h5>
    <?php echo $this->render('_search_year',['model' => $searchModel])?>
</div>
</div>
<div class="row">
    <div class="col-6">
        <div class="card">
            <div class="card-body">
                <h6><i class="fa-solid fa-calendar-day"></i> จำนวนการใช้งานรถทั่วไป </h6>
<?php echo $this->render('chart_general',['model' => $searchModel])?>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h6><i class="fa-solid fa-calendar-day"></i> การขอใช้งานรถทั่วไปของหน่วยงานต่าง ๆ 10 อันดับ </h6>
                <?php echo $this->render('chart_general_topten',['model' => $searchModel])?>
            </div>
        </div>
<div class="card">
            <div class="card-body">
                <h6><i class="fa-regular fa-calendar-plus"></i> ค่าใช้จ่ายยานพาหนะแยกรายเดือน </h6>
            </div>
        </div>



    </div>
    <div class="col-6">
        <div class="card">
            <div class="card-body">
                <h6><i class="fa-regular fa-calendar-plus"></i> จำนวนการใช้งานรถฉุกเฉิน </h6>
                <?php echo $this->render('chart_ambulance',['model' => $searchModel])?>
            </div>
        </div>

        <!-- <div class="card">
            <div class="card-body"> -->
                <h6><i class="fa-regular fa-calendar-plus"></i> รถที่มีการใช้งานบ่อย </h6>
                <?php echo $this->render('list_car_items',['model' => $searchModel])?>
            <!-- </div>
        </div> -->

    </div>
</div>

