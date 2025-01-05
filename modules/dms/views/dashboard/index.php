<?php
$this->title = 'Dashboard DMS'
/** @var yii\web\View $this */
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-chart-simple me-1 fs-1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php  echo $this->render('@app/modules/dms/menu') ?>
<?php $this->endBlock(); ?>

<div class="row">
    <div class="col-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between gap-1 mb-0">
                    <span class="h5 fw-semibold"><?php echo number_format($searchModel->CountType('receive'))?> ทะเบียนรับ</span>
                    <div class="relative">
                        <i class="fa-solid fa-download text-black-50 fs-1 mt-1"></i>
                    </div>
                </div>
                <div class="d-flex justify-content-between gap-1 mb-0">
                    <span class="badge rounded-pill badge-soft-primary text-primary fs-13 px-2"><i class="bi bi-exclamation-circle-fill"></i> ทะเบียนรับ</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-3">
    <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between gap-1 mb-0">
                    <span class="h5 fw-semibold"><?php echo number_format($searchModel->CountType('send'))?> ทะเบียนรับส่ง</span>
                    <div class="relative">
                        <i class="fa-solid fa-paper-plane text-black-50 fs-1 mt-1"></i>
                    </div>
                </div>
                <div class="d-flex justify-content-between gap-1 mb-0">
                    <span class="badge rounded-pill badge-soft-primary text-primary fs-13 px-2"><i class="bi bi-exclamation-circle-fill"></i> ทะเบียนรับส่ง</span>
                </div>
            </div>
        </div>
        
    </div>
    <div class="col-3">
        <div class="card">
            <div class="card-body">
                <a href="/hr/leave/dashboard-approve">
                <div class="d-flex justify-content-between gap-1 mb-0">
                    <span class="h5 fw-semibold">0 ทะเบียนประกาศ</span>
                    <div class="relative">
                        <i class="bi bi-person-check text-black-50 fs-2"></i>
                    </div>
                </div>
                <div class="d-flex justify-content-between gap-1 mb-0">
                        <span class="badge rounded-pill badge-soft-primary text-primary fs-13 px-2"><i class="bi bi-exclamation-circle-fill"></i> ทะเบียนประกาศ<span>
                            </span></span></div>
                        </a></div><a href="/hr/leave/dashboard-approve">
                    </a></div><a href="/hr/leave/dashboard-approve">
                </a>
    </div>
    <div class="col-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between gap-1 mb-0">
                    <span class="h5 fw-semibold">0 นโยบาย</span>
                    <div class="relative">
                        <i class="bi bi-eraser text-black-50 fs-2"></i>
                    </div>
                </div>
                <div class="d-flex justify-content-between gap-1 mb-0">
                    <span class="badge rounded-pill badge-soft-primary text-primary fs-13 px-2"><i class="bi bi-exclamation-circle-fill"></i> นโยบาย</span>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="row">
<div class="col-7"> <?php echo $this->render('chart_receive', ['model' => $searchModel]); ?></div>
<div class="col-5"> <?php echo $this->render('chart_send', ['model' => $searchModel]); ?></div>
<div class="col-12"> <?php echo $this->render('org_summary', ['model' => $searchModel]); ?></div>
</div>

