<?php
use yii\helpers\Url;
$this->title = 'ภาพรวมแผนงาน';
$this->params['breadcrumbs'][] = ['label' => 'แผนงาน', 'url' => ['/plan/dashboard']];
$this->params['breadcrumbs'][] = $this->title;
?>



<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2 mb-1">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14.106 5.553a2 2 0 0 0 1.788 0l3.659-1.83A1 1 0 0 1 21 4.619v12.764a1 1 0 0 1-.553.894l-4.553 2.277a2 2 0 0 1-1.788 0l-4.212-2.106a2 2 0 0 0-1.788 0l-3.659 1.83A1 1 0 0 1 3 19.381V6.618a1 1 0 0 1 .553-.894l4.553-2.277a2 2 0 0 1 1.788 0z"></path>
            <path d="M15 5.764v15"></path>
            <path d="M9 3.236v15"></path>
        </svg>
        <?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
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
<div class="col-4">
    <?=$this->render('_search',['model' => $searchModel])?>
</div>
</div>
<div class="row">
    <div class="col-md-6 col-lg-4">
        <div class="card border-4 border-secondary border-top-0 border-end-0 border-bottom-0">
            <div class="card-body">
                <div class="d-flex justify-content-between gap-1 mb-0">
                    <div class="d-flex flex-column">
                        <span class="h6">แบบร่าง</span>
                        <span class="h4"><?=$searchModel->countStatus('draft')?></span>
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
                        <span class="h4"><?=$searchModel->countStatus('submit')?></span>
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
                        <span class="h4"><?=$searchModel->countStatus('renew')?></span>
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
                        <span class="h4"><?=$searchModel->countStatus('approve')?></span>
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
                        <span class="h4"><?=$searchModel->countStatus('reject')?></span>
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
                        <span class="h4"><?=$searchModel->countStatus()?></span>
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
            <h2 class="fw-bold mb-0"><?=$searchModel->countStatus(null,'parcel')?></h2>
            <a href="<?=Url::to(['/plan/parcel'])?>" class="btn btn-link p-0 text-start mt-2">รายละเอียดเพิ่มเติม <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <h6 class="mb-0 text-muted">คำขอบุคลากร</h6>
                <div class="icon-box"><i class="bi bi-person-circle fs-4"></i></div>
            </div>
            <h2 class="fw-bold mb-0"><?=$searchModel->countStatus(null,'personnel')?></h2>
            <a href="<?=Url::to(['/plan/personnel'])?>" class="btn btn-link p-0 text-start mt-2">รายละเอียดเพิ่มเติม <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <h6 class="mb-0 text-muted">คำขอค่าใช้สอย</h6>
                <div class="icon-box"><i class="bi bi-folder fs-4"></i></div>
            </div>
            <h2 class="fw-bold mb-0"><?=$searchModel->countStatus(null,'expenses')?></h2>
            <a href="<?=Url::to(['/plan/expenses'])?>" class="btn btn-link p-0 text-start mt-2">รายละเอียดเพิ่มเติม <i class="bi bi-arrow-right"></i></a>
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