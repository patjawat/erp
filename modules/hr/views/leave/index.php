<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\modules\hr\models\Leave;

/** @var yii\web\View $this */
/** @var app\modules\lm\models\LeaveSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'ระบบการลา';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-calendar-day fs-1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('@app/modules/hr/views/leave/menu') ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?=$this->render('@app/modules/hr/views/leave/menu',['active' => 'index'])?>
<?php $this->endBlock(); ?>
<?php  Pjax::begin(['id' => 'leave', 'timeout' => 500000]); ?>



<style>
.hover-card-under {

    transition: border-color 0.3s ease, transform 0.3s ease;
}

.hover-card-under:hover {
    border: 3px solid transparent !important;
    border-color: #dc3545 !important;
    border-top: 0 !important;
    border-left: 0 !important;
    border-right: 0 !important;
    border-left: 0 !important;
    transform: scale(1.04);
}
</style>
<!-- Start BxStatus -->
<div class="row">
    <div class="col-3">
        <a href="<?php echo Url::to(['/hr/leave/index','status' => 'Pending'])?>">
            <div
                class="card hover-card-under <?php echo (isset($searchModel->status[0]) && $searchModel->status[0]  == 'Pending') ? 'border-4 border-start-0 border-end-0 border-top-0 border-danger' :''?>">
                <div class="card-body">
                    <div class="d-flex justify-content-between gap-1 mb-0">
                        <span
                            class="h2 fw-semibold text-primary"><?php echo $searchModel->countLeaveStatus('Pending') ?></span>
                        <div class="relative">
                            <i class="bi bi-calendar-check-fill text-black-50 fs-2"></i>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between gap-1 mb-0">
                        <span class="text-primary fs-6 px-2"><i class="bi bi-exclamation-circle-fill"></i>
                            ขออนุมัติเห็นชอบ</span>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-3">
        <a href="<?php echo Url::to(['/hr/leave/index','status' => 'Checking'])?>">
            <div
                class="card hover-card-under <?php echo (isset($searchModel->status[0]) && $searchModel->status[0]  == 'Checking') ? 'border-4 border-start-0 border-end-0 border-top-0 border-danger' :''?>">
                <div class="card-body">
                    <div class="d-flex justify-content-between gap-1 mb-0">
                        <span class="h2 fw-semibold"><?php echo $searchModel->countLeaveStatus('Checking') ?></span>
                        <div class="relative">
                            <i class="bi bi-hourglass-split text-black-50 fs-2"></i>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between gap-1 mb-0">
                        <span class="text-primary fs-13 px-2"><i
                                class="bi bi-exclamation-circle-fill"></i> รอดำเนินการตรวจสอบ</span>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-3">
        <div
            class="card hover-card-under <?php echo (isset($searchModel->status[0]) && $searchModel->status[0]  == 'Verify') ? 'border-4 border-start-0 border-end-0 border-top-0 border-danger' :''?>">
            <div class="card-body">
                <a href="<?php echo Url::to(['/hr/leave/index','status' => 'Verify'])?>">
                    <!-- <a href="<?php echo Url::to(['/hr/leave/dashboard-approve'])?>"> -->
                    <div class="d-flex justify-content-between gap-1 mb-0">
                        <span class="h2 fw-semibold"><?php echo $searchModel->countLeaveStatus('Verify') ?></span>
                        <div class="relative">
                            <i class="bi bi-person-check text-black-50 fs-2"></i>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between gap-1 mb-0">
                        <span class="text-primary fs-13 px-2"><i
                                class="bi bi-exclamation-circle-fill"></i> อยู่ระหว่างรอผู้อำนวยการ/อนุมัติ<span>
                    </div>
            </div>
        </div>
        </a>
    </div>
    <div class="col-3">
            <a href="<?php echo Url::to(['/hr/leave/index','status' => 'ReqCancel'])?>">
            <div
                class="card hover-card-under <?php echo (isset($searchModel->status[0]) && $searchModel->status[0]  == 'ReqCancel') ? 'border-4 border-start-0 border-end-0 border-top-0 border-danger' :''?>">
                <div class="card-body">
                    <div class="d-flex justify-content-between gap-1 mb-0">
                        <span class="h2 fw-semibold"><?php echo $searchModel->countLeaveStatus('ReqCancel') ?></span>
                        <div class="relative">
                            <i class="bi bi-eraser text-black-50 fs-2"></i>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between gap-1 mb-0">
                        <span class="text-primary fs-13 px-2"><i
                                class="bi bi-exclamation-circle-fill"></i> ขอยกเลิกวันลา</span>
                    </div>
                </div>
            </div>
            </a>
        </div>
</div>
<!--End  BoxStatus -->

<div class="card">
    <div class="card-body d-flex justify-content-center">
        <?php echo $this->render('_search', ['model' => $searchModel]); ?>

    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h6>
                <i class="bi bi-ui-checks"></i> ทะเบียนประวัติการลา
                <span
                    class="badge rounded-pill text-bg-primary"><?php echo number_format($dataProvider->getTotalCount(), 0) ?></span>
            </h6>
        </div>


        <?php
        echo $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider
        ]);
        ?>

    </div>
</div>

<?php  Pjax::end(); ?>
<?php
$js = <<< JS

    

    \$('.filter-status').click(function (e) { 
        e.preventDefault();
        var id = \$(this).data('id');
        \$('#leavesearch-status').val(id);
        \$('#w0').submit();
        console.log(id);
        
        
    });
    JS;
$this->registerJs($js);
?>
