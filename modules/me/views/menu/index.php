<!-- Header -->
<!-- <div class="text-center text-white mb-5">
    <h1 class="display-4 fw-light mb-3">ระบบจัดการงานบริการ</h1>
    <p class="lead">เลือกบริการที่ต้องการใช้งาน</p>
</div> -->
<?php
use yii\helpers\Html;
use yii\helpers\Url;
?>
<div class="row g-3">
    <!-- รายการ -->
    <div class="col-6 col-lg-2">
        <a href="#">
            <div class="card h-100 shadow border-0 rounded-3 hover-card">
                <div class="card-body text-center p-3 p-lg-4">
                    <div class="bg-danger rounded-circle d-inline-flex align-items-center justify-content-center mb-2 mb-lg-3" style="width: 50px; height: 50px;">
                        <i class="fas fa-list-ul text-white fs-5 fs-lg-1"></i>
                    </div>
                    <h6 class="card-title fw-bold text-dark mb-2 mb-lg-3">รายการ</h6>
                    <p class="card-text text-muted d-none d-lg-block small">
                        ดูรายการข้อมูลต่างๆ ในระบบ<br>
                        จัดการและติดตามสถานะ
                    </p>
                    <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1 d-none d-lg-inline" style="font-size: 0.7rem;">พร้อมใช้งาน</span>
                </div>
            </div>
        </a>
        </div>

    <!-- แจ้งซ่อม -->
    <div class="col-6 col-lg-2">
        <a href="<?=Url::to(['/me/repair-v2'])?>">
            <div class="card h-100 shadow border-0 rounded-3 hover-card">
                <div class="card-body text-center p-3 p-lg-4">
                    <div class="bg-info rounded-circle d-inline-flex align-items-center justify-content-center mb-2 mb-lg-3" style="width: 50px; height: 50px;">
                        <i class="fas fa-tools text-white fs-5 fs-lg-1"></i>
                    </div>
                    <h6 class="card-title fw-bold text-dark mb-2 mb-lg-3">แจ้งซ่อม</h6>
                    <p class="card-text text-muted d-none d-lg-block small">
                        แจ้งปัญหาและขอรับบริการซ่อมแซม<br>
                        อุปกรณ์และสิ่งของต่างๆ
                    </p>
                    <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1 d-none d-lg-inline" style="font-size: 0.7rem;">พร้อมใช้งาน</span>
                </div>
            </div>
        </a>
    </div>

    <!-- ขอซื้อขอจ้าง -->
    <div class="col-6 col-lg-2">
        <div class="card h-100 shadow border-0 rounded-3 hover-card">
            <div class="card-body text-center p-3 p-lg-4">
                <div class="bg-success rounded-circle d-inline-flex align-items-center justify-content-center mb-2 mb-lg-3" style="width: 50px; height: 50px;">
                    <i class="fas fa-shopping-cart text-white fs-5 fs-lg-1"></i>
                </div>
                <h6 class="card-title fw-bold text-dark mb-2 mb-lg-3">ขอซื้อขอจ้าง</h6>
                <p class="card-text text-muted d-none d-lg-block small">
                    ยื่นคำขอซื้อสินค้าและบริการ<br>
                    ตามระเบียบของหน่วยงาน
                </p>
                <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1 d-none d-lg-inline" style="font-size: 0.7rem;">พร้อมใช้งาน</span>
            </div>
        </div>
    </div>

    <!-- จองรถ -->
    <div class="col-6 col-lg-2">
        <div class="card h-100 shadow border-0 rounded-3 hover-card">
            <div class="card-body text-center p-3 p-lg-4">
                <div class="bg-warning rounded-circle d-inline-flex align-items-center justify-content-center mb-2 mb-lg-3" style="width: 50px; height: 50px;">
                    <i class="fas fa-car text-white fs-5 fs-lg-1"></i>
                </div>
                <h6 class="card-title fw-bold text-dark mb-2 mb-lg-3">จองรถ</h6>
                <p class="card-text text-muted d-none d-lg-block small">
                    จองรถยนต์สำหรับเดินทาง<br>
                    ราชการและงานต่างๆ
                </p>
                <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1 d-none d-lg-inline" style="font-size: 0.7rem;">พร้อมใช้งาน</span>
            </div>
        </div>
    </div>

    <!-- ห้องประชุม -->
    <div class="col-6 col-lg-2">
        <div class="card h-100 shadow border-0 rounded-3 hover-card">
            <div class="card-body text-center p-3 p-lg-4">
                <div class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center mb-2 mb-lg-3" style="width: 50px; height: 50px;">
                    <i class="fas fa-users text-white fs-5 fs-lg-1"></i>
                </div>
                <h6 class="card-title fw-bold text-dark mb-2 mb-lg-3">ห้องประชุม</h6>
                <p class="card-text text-muted d-none d-lg-block small">
                    จองห้องประชุมสำหรับการประชุม<br>
                    และกิจกรรมต่างๆ
                </p>
                <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1 d-none d-lg-inline" style="font-size: 0.7rem;">พร้อมใช้งาน</span>
            </div>
        </div>
    </div>

    <!-- อบรม/ประชุม/ดูงาน -->
    <div class="col-6 col-lg-2">
        <div class="card h-100 shadow border-0 rounded-3 hover-card">
            <div class="card-body text-center p-3 p-lg-4">
                <div class="bg-dark rounded-circle d-inline-flex align-items-center justify-content-center mb-2 mb-lg-3" style="width: 50px; height: 50px;">
                    <i class="fas fa-graduation-cap text-white fs-5 fs-lg-1"></i>
                </div>
                <h6 class="card-title fw-bold text-dark mb-2 mb-lg-3">อบรม/ประชุม/ดูงาน</h6>
                <p class="card-text text-muted d-none d-lg-block small">
                    ลงทะเบียนเข้าร่วมกิจกรรม<br>
                    อบรม ประชุม และดูงาน
                </p>
                <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1 d-none d-lg-inline" style="font-size: 0.7rem;">พร้อมใช้งาน</span>
            </div>
        </div>
    </div>
</div>





