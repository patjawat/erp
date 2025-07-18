
<!-- Start BxStatus -->
<div class="row">
<div class="col-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between gap-1 mb-0">
                    <span class="h5 fw-semibold"><?php echo $searchModel->sumLeaveType('ReqCancel') ?> ขออนุมัติวันลา</span>
                    <div class="relative">
                    <i class="bi bi-hourglass-split text-black-50 fs-2"></i>
                    </div>
                </div>
                <div class="d-flex justify-content-between gap-1 mb-0">
                    <span class="badge rounded-pill badge-soft-primary text-primary fs-13 px-2"><i class="bi bi-exclamation-circle-fill"></i> ล่าสุด</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-3">

    <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between gap-1 mb-0">
                    <span class="h5 fw-semibold"><?php echo $searchModel->sumLeaveType('LT3') ?>
                    ลากิจ</span>
                    <div class="relative">
                    <i class="bi bi-person-fill-exclamation text-black-50 fs-2"></i>
                    </div>
                </div>
                <div class="d-flex justify-content-between gap-1 mb-0">
                    <span class="badge rounded-pill badge-soft-primary text-primary fs-13 px-2"><i class="bi bi-exclamation-circle-fill"></i> ล่าสุด</span>
                </div>
            </div>
        </div>
        
    </div>
    <div class="col-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between gap-1 mb-0">
                    <span class="h5 fw-semibold"><?php echo $searchModel->sumLeaveType('LT1') ?> ลาป่วย</span>
                    <div class="relative">
                        <i class="bi bi-clipboard2-pulse text-black-50 fs-2"></i>
                    </div>
                </div>
                <div class="d-flex justify-content-between gap-1 mb-0">
                    <span class="badge rounded-pill badge-soft-primary text-primary fs-13 px-2"><i class="bi bi-exclamation-circle-fill"></i> ล่าสุด<span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between gap-1 mb-0">
                    <span class="h5 fw-semibold"><?php echo $searchModel->sumLeaveType('LT4') ?>
                    ลาพักผ่อน</span>
                    <div class="relative">
                        <i class="bi bi-person-walking  text-black-50 fs-2"></i>
                    </div>
                </div>
                <div class="d-flex justify-content-between gap-1 mb-0">
                    <span class="badge rounded-pill badge-soft-primary text-primary fs-13 px-2"><i class="bi bi-exclamation-circle-fill"></i> คงเหลือ</span>
                    <span class="text-black bg-primary-subtle badge rounded-pill fw-ligh fs-13"><?php echo $searchModel->sumLeavePermission()['sum'] ?></span>
                </div>
            </div>
        </div>
    </div>
</div>
<!--End  BoxStatus -->
<?php
// echo $searchModel->thai_year;
// echo $searchModel->emp_id;
?>