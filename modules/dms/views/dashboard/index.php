<?php
$this->title = 'ภาพรวม';
$this->params['breadcrumbs'][] = ['label' => 'งานสารบรรณ', 'url' => ['/me']];
$this->params['breadcrumbs'][] = $this->title;
/** @var yii\web\View $this */
?>

<!-- <?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect width="7" height="9" x="3" y="3" rx="1"></rect>
            <rect width="7" height="5" x="14" y="3" rx="1"></rect>
            <rect width="7" height="9" x="14" y="12" rx="1"></rect>
            <rect width="7" height="5" x="3" y="16" rx="1"></rect>
        </svg>
        <?= $this->title; ?>
    </h4>
</div>
<?php $this->endBlock(); ?> -->


<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex flex-column flex-sm-row align-items-center gap-2 mb-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary">
            <rect width="7" height="9" x="3" y="3" rx="1"></rect>
            <rect width="7" height="5" x="14" y="3" rx="1"></rect>
            <rect width="7" height="9" x="14" y="12" rx="1"></rect>
            <rect width="7" height="5" x="3" y="16" rx="1"></rect>
        </svg>
        
        <span class="d-block">
            <?= $this->title; ?>
        </span>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?php echo $this->render('@app/modules/dms/menu', ['model' => $searchModel, 'active' => 'dashboard']) ?>
<?php $this->endBlock(); ?>


<div class="row">
    <div class="col-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between gap-1 mb-0">
                    <span class="h5"><?php echo number_format($searchModel->CountType('receive')) ?>
                        ทะเบียนรับ</span>
                    <div class="relative">
                        <i class="fa-solid fa-download text-black-50 fs-1 mt-1"></i>
                    </div>
                </div>
                <div class="d-flex justify-content-between gap-1 mb-0">
                    <span class="badge rounded-pill badge-soft-primary text-primary fs-13 px-2"><i
                            class="bi bi-exclamation-circle-fill"></i> ทะเบียนรับ</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between gap-1 mb-0">
                    <span class="h5"><?php echo number_format($searchModel->CountType('send')) ?>
                        ทะเบียนรับส่ง</span>
                    <div class="relative">
                        <i class="fa-solid fa-paper-plane text-black-50 fs-1 mt-1"></i>
                    </div>
                </div>
                <div class="d-flex justify-content-between gap-1 mb-0">
                    <span class="badge rounded-pill badge-soft-primary text-primary fs-13 px-2"><i
                            class="bi bi-exclamation-circle-fill"></i> ทะเบียนรับส่ง</span>
                </div>
            </div>
        </div>

    </div>
    <div class="col-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between gap-1 mb-0">
                    <span class="h5">0 คำสั่ง</span>
                    <div class="relative">
                        <i class="fa-solid fa-bullhorn text-black-50 fs-1"></i>
                    </div>
                </div>
                <div class="d-flex justify-content-between gap-1 mb-0">
                    <span class="badge rounded-pill badge-soft-primary text-primary fs-13 px-2"><i
                            class="bi bi-exclamation-circle-fill"></i> ทะเบียนประกาศ<span>
                        </span></span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between gap-1 mb-0">
                    <span class="h5">0 ทะเบียนประกาศ/นโยบาย</span>
                    <div class="relative">
                        <i class="bi bi-eraser text-black-50 fs-2"></i>
                    </div>
                </div>
                <div class="d-flex justify-content-between gap-1 mb-0">
                    <span class="badge rounded-pill badge-soft-primary text-primary fs-13 px-2"><i
                            class="bi bi-exclamation-circle-fill"></i> นโยบาย</span>
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