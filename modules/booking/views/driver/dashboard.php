<?php
$this->title = "Dashboard"
?>
<?php $this->beginBlock('page-title'); ?>
<!-- <i class="bi bi-ui-checks"></i>-->
<i class="fa-solid fa-chart-pie fs-1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?php  echo $this->render('menu') ?>
<?php $this->endBlock(); ?>


<div class="row">
    <div class="col-3">
        <div class="card hover-card-under border-4 border-start-0 border-end-0 border-top-0 border-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between gap-1 mb-0">
                    <span class="h2 fw-semibold">0</span>
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
                    <span class="h2 fw-semibold">0</span>
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
                    <span class="h2 fw-semibold">0</span>
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
                    <span class="h2 fw-semibold">0</span>
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

<h6 class="text-center">ข้อมูลแผนภูมิข้อมูลการใช้ยานพาหนะ</h6>
<div class="row">
    <div class="col-6">
        <div class="card">
            <div class="card-body">
                <h6><i class="fa-solid fa-calendar-day"></i> จำนวนการใช้งานรถทั่วไป </h6>

            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h6><i class="fa-solid fa-calendar-day"></i> การขอใช้งานรถทั่วไปของหน่วยงานต่าง ๆ 10 อันดับ </h6>

            </div>
        </div>



    </div>
    <div class="col-6">
        <div class="card">
            <div class="card-body">
                <h6><i class="fa-regular fa-calendar-plus"></i> จำนวนการใช้งานรถฉุกเฉิน </h6>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h6><i class="fa-regular fa-calendar-plus"></i> รถที่มีการใช้งานบ่อย </h6>
            </div>
        </div>

    </div>
</div>

<div class="card">
            <div class="card-body">
                <h6><i class="fa-regular fa-calendar-plus"></i> ค่าใช้จ่ายยานพาหนะแยกรายเดือน </h6>
            </div>
        </div>
