<?php

$this->title = 'Dashboard';
$this->params['breadcrumbs'][] = $this->title;
?>


<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-gauge me-1"></i> </i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?= $this->render('@app/modules/plan/menu', ['active' => 'dashboard']) ?>
<?php $this->endBlock(); ?>


<!-- <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0 text-muted">Dashboard</h3>
        <select class="form-select w-auto">
            <option>แผนประจำปี 2569</option>
            <option>แผนประจำปี 2568</option>
        </select>
    </div> -->

<div class="row">
    <div class="col-md-6 col-lg-4">
        <div class="card border-4 border-secondary border-top-0 border-end-0 border-bottom-0">
            <div class="card-body">
                <div class="d-flex justify-content-between gap-1 mb-0">
                    <div class="d-flex flex-column">
                        <span class="h6">แบบร่าง</span>
                        <span class="h3 fw-semibold">200</span>
                    </div>
                    <div class="relative">
                        <i class="bi bi-file-earmark-text text-black-50 fs-1 mt-1"></i>
                    </div>
                </div>
                <div class="d-flex justify-content-between gap-1 mb-0">
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4">
        <div class="card border-4 border-info border-top-0 border-end-0 border-bottom-0">
            <div class="card-body">
                <div class="d-flex justify-content-between gap-1 mb-0">
                    <div class="d-flex flex-column">
                        <span class="h6">รออนุมัติ</span>
                        <span class="h3 fw-semibold">0</span>
                    </div>
                    <div class="relative">
                        <i class="bi bi-hourglass-split text-info text-opacity-75 fs-1 mt-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4">
        <div class="card border-4 border-warning border-top-0 border-end-0 border-bottom-0">
            <div class="card-body">
                <div class="d-flex justify-content-between gap-1 mb-0">
                    <div class="d-flex flex-column">
                        <span class="h6">ปรับแผน</span>
                        <span class="h3 fw-semibold">0</span>
                    </div>
                    <div class="relative">
                        <i class="fa-solid fa-repeat text-black-50 fs-1 mt-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4">
        <div class="card border-4 border-success border-top-0 border-end-0 border-bottom-0">
            <div class="card-body">
                <div class="d-flex justify-content-between gap-1 mb-0">
                    <div class="d-flex flex-column">
                        <span class="h6">อนุมัติ</span>
                        <span class="h3 fw-semibold">0</span>
                    </div>
                    <div class="relative">
                        <i class="fa-regular fa-circle-check text-success text-opacity-75 fs-1 mt-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4">
        <div class="card border-4 border-danger border-top-0 border-end-0 border-bottom-0">
            <div class="card-body">
                <div class="d-flex justify-content-between gap-1 mb-0">
                    <div class="d-flex flex-column">
                        <span class="h6">ไม่อนุมัติ</span>
                        <span class="h3 fw-semibold">0</span>
                    </div>
                    <div class="relative">
                        <i class="fa-solid fa-hand text-danger text-opacity-75 fs-1 mt-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4">
        <div class="card border-4 border-primary border-top-0 border-end-0 border-bottom-0">
            <div class="card-body">
                <div class="d-flex justify-content-between gap-1 mb-0">
                    <div class="d-flex flex-column">
                        <span class="h6">ทั้งหมด</span>
                        <span class="h3 fw-semibold">0</span>
                    </div>
                    <div class="relative">
                        <i class="fa-solid fa-ranking-star text-primary text-opacity-75 fs-1 mt-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<h5 class="mb-3 text-muted">แบบคำขอทั้งหมด</h5>
<div class="row g-4 mb-5">
    <div class="col-md-4">
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <h6 class="mb-0 text-muted">คำขอพัสดุ</h6>
                <div class="icon-box"><i class="bi bi-box-seam fs-4"></i></div>
            </div>
            <h2 class="fw-bold mb-0">1</h2>
            <a href="#" class="btn btn-link p-0 text-start mt-2">รายละเอียดเพิ่มเติม <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <h6 class="mb-0 text-muted">คำขอบุคลากร</h6>
                <div class="icon-box"><i class="bi bi-person-circle fs-4"></i></div>
            </div>
            <h2 class="fw-bold mb-0">2</h2>
            <a href="#" class="btn btn-link p-0 text-start mt-2">รายละเอียดเพิ่มเติม <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <h6 class="mb-0 text-muted">คำขอค่าใช้สอย</h6>
                <div class="icon-box"><i class="bi bi-folder fs-4"></i></div>
            </div>
            <h2 class="fw-bold mb-0">7</h2>
            <a href="#" class="btn btn-link p-0 text-start mt-2">รายละเอียดเพิ่มเติม <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
</div>

<h5 class="mb-3 text-muted">แบบคำขอพัสดุ 7 หมวด</h5>
<div class="card p-4">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">หมวด</th>
                    <th scope="col" colspan="2" class="text-center">คำขอทั้งหมด</th>
                    <th scope="col" colspan="2" class="text-center">อนุมัติแล้ว</th>
                </tr>
                <tr>
                    <th scope="col"></th>
                    <th scope="col"></th>
                    <th scope="col">รายการ</th>
                    <th scope="col">วงเงิน (บาท)</th>
                    <th scope="col">รายการ</th>
                    <th scope="col">วงเงิน (บาท)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>ที่ดิน</td>
                    <td>0</td>
                    <td>0.00</td>
                    <td>0</td>
                    <td>0.00</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>อาคาร</td>
                    <td>0</td>
                    <td>0.00</td>
                    <td>0</td>
                    <td>0.00</td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>สิ่งปลูกสร้าง</td>
                    <td>0</td>
                    <td>0.00</td>
                    <td>0</td>
                    <td>0.00</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>