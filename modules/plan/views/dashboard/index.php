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
<?php
// การ์ดหลัก — 4 ต่อแถว พอดี (ยอดรวม + 3 ขั้น pipeline)
$statusCards = [
    ['label' => 'ทั้งหมด',    'status' => null,      'color' => 'primary',   'icon' => 'fa-solid fa-ranking-star'],
    ['label' => 'แบบร่าง',    'status' => 'draft',   'color' => 'secondary', 'icon' => 'bi bi-file-earmark-text'],
    ['label' => 'รออนุมัติ', 'status' => 'submit',  'color' => 'info',      'icon' => 'bi bi-hourglass-split'],
    ['label' => 'อนุมัติ',    'status' => 'approve', 'color' => 'success',   'icon' => 'fa-regular fa-circle-check'],
];
// สถานะรอง — โชว์เฉพาะเมื่อมีข้อมูล (ปรับแผน / ไม่อนุมัติ มักเป็น 0)
$statusPills = [
    ['label' => 'ปรับแผน',   'status' => 'renew',  'color' => 'warning', 'icon' => 'fa-solid fa-repeat'],
    ['label' => 'ไม่อนุมัติ', 'status' => 'reject', 'color' => 'danger',  'icon' => 'fa-solid fa-hand'],
];
?>
<div class="row g-3">
    <?php foreach ($statusCards as $c): ?>
        <div class="col-6 col-md-3">
            <div class="card h-100 border-start border-4 border-<?= $c['color'] ?> border-top-0 border-end-0 border-bottom-0">
                <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center">
                    <div class="min-w-0">
                        <div class="text-muted small text-nowrap"><?= $c['label'] ?> · <?= $searchModel->countStatus($c['status']) ?> รายการ</div>
                        <div class="fs-5 fw-bold text-<?= $c['color'] ?> text-nowrap"><?= number_format($searchModel->sumStatus($c['status']), 2) ?> <small class="fw-normal">บาท</small></div>
                    </div>
                    <i class="<?= $c['icon'] ?> fs-2 text-<?= $c['color'] ?> opacity-50 ms-2"></i>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php
$activePills = array_filter($statusPills, fn($p) => $searchModel->countStatus($p['status']) > 0);
if ($activePills): ?>
    <div class="d-flex flex-wrap gap-2 mt-2">
        <?php foreach ($activePills as $p): ?>
            <span class="badge rounded-pill text-bg-<?= $p['color'] ?> fs-6 fw-normal py-2 px-3">
                <i class="<?= $p['icon'] ?> me-1"></i><?= $p['label'] ?>
                <?= $searchModel->countStatus($p['status']) ?> รายการ ·
                <strong><?= number_format($searchModel->sumStatus($p['status']), 2) ?></strong> บาท
            </span>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<h5 class="mb-3 text-muted">แบบคำขอทั้งหมด</h5>
<div class="row g-4 mb-5">
    <div class="col-md-4">
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <h6 class="mb-0 text-muted">คำขอพัสดุ</h6>
                <div class="icon-box"><i class="bi bi-box-seam fs-4"></i></div>
            </div>
            <h2 class="fw-bold mb-0"><?=$searchModel->countStatus(null,'parcel')?> <small class="text-muted fs-6">รายการ</small></h2>
            <div class="fs-4 fw-bold text-success"><i class="fa-solid fa-coins me-1"></i><?= number_format($searchModel->sumStatus(null,'parcel'), 2) ?> บาท</div>
            <a href="<?=Url::to(['/plan/parcel'])?>" class="btn btn-link p-0 text-start mt-2">รายละเอียดเพิ่มเติม <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <h6 class="mb-0 text-muted">คำขอบุคลากร</h6>
                <div class="icon-box"><i class="bi bi-person-circle fs-4"></i></div>
            </div>
            <h2 class="fw-bold mb-0"><?=$searchModel->countStatus(null,'personnel')?> <small class="text-muted fs-6">รายการ</small></h2>
            <div class="fs-4 fw-bold text-success"><i class="fa-solid fa-coins me-1"></i><?= number_format($searchModel->sumStatus(null,'personnel'), 2) ?> บาท</div>
            <a href="<?=Url::to(['/plan/personnel'])?>" class="btn btn-link p-0 text-start mt-2">รายละเอียดเพิ่มเติม <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <h6 class="mb-0 text-muted">คำขอค่าใช้สอย</h6>
                <div class="icon-box"><i class="bi bi-folder fs-4"></i></div>
            </div>
            <h2 class="fw-bold mb-0"><?=$searchModel->countStatus(null,'expenses')?> <small class="text-muted fs-6">รายการ</small></h2>
            <div class="fs-4 fw-bold text-success"><i class="fa-solid fa-coins me-1"></i><?= number_format($searchModel->sumStatus(null,'expenses'), 2) ?> บาท</div>
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