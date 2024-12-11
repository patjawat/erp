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
<i class="bi bi-box-seam"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('@app/modules/hr/views/leave/menu') ?>
<?php $this->endBlock(); ?>
<?php Pjax::begin(['id' => 'leave-container', 'timeout' => 500000]); ?>

<!-- Start BxStatus -->
<div class="row">
    <div class="col-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between gap-1 mb-0">
                    <span class="h5 fw-semibold"><?php echo $searchModel->countLeaveStatus('Pending') ?>
                        รายการ</span>
                    <div class="relative">
                        <i class="bi bi-calendar-check-fill text-black-50 fs-2"></i>
                    </div>
                </div>
                <div class="d-flex justify-content-between gap-1 mb-0">
                    <span class="badge rounded-pill badge-soft-primary text-primary fs-13 px-2"><i class="bi bi-exclamation-circle-fill"></i> ขออนุมัติเห็นชอบ</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-3">

    <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between gap-1 mb-0">
                    <span class="h5 fw-semibold"><?php echo $searchModel->countLeaveStatus('Pending') ?>
                        รายการ</span>
                    <div class="relative">
                        <i class="bi bi-hourglass-split text-black-50 fs-2"></i>
                    </div>
                </div>
                <div class="d-flex justify-content-between gap-1 mb-0">
                    <span class="badge rounded-pill badge-soft-primary text-primary fs-13 px-2"><i class="bi bi-exclamation-circle-fill"></i> รอดำเนินการตรวจสอบ</span>
                </div>
            </div>
        </div>
        
    </div>
    <div class="col-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between gap-1 mb-0">
                    <span class="h5 fw-semibold"><?php echo $searchModel->countLeaveStatus('Checking') ?> รายการ</span>
                    <div class="relative">
                        <i class="bi bi-person-check text-black-50 fs-2"></i>
                    </div>
                </div>
                <div class="d-flex justify-content-between gap-1 mb-0">
                    <span class="badge rounded-pill badge-soft-primary text-primary fs-13 px-2"><i class="bi bi-exclamation-circle-fill"></i> อยู่ระหว่างรอผู้อำนวนการ/อนุมัติ<span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between gap-1 mb-0">
                    <span class="h5 fw-semibold"><?php echo $searchModel->countLeaveStatus('ReqCancel') ?> รายการ</span>
                    <div class="relative">
                        <i class="bi bi-eraser text-black-50 fs-2"></i>
                    </div>
                </div>
                <div class="d-flex justify-content-between gap-1 mb-0">
                    <span class="badge rounded-pill badge-soft-primary text-primary fs-13 px-2"><i class="bi bi-exclamation-circle-fill"></i> ขอยกเลิกวันลา</span>
                </div>
            </div>
        </div>
    </div>
</div>
<!--End  BoxStatus -->

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h6>
                <i class="bi bi-ui-checks"></i> ทะเบียนประวัติการลา
                <span
                    class="badge rounded-pill text-bg-primary"><?php echo number_format($dataProvider->getTotalCount(), 0) ?></span>
                รายการ
            </h6>
        </div>
        <div class="d-flex justify-content-between  align-top align-items-center mt-2">
            <?php echo $this->render('_search', ['model' => $searchModel]); ?>
            <?= Html::a('<i class="fa-solid fa-plus"></i> สร้างใหม่', ['/hr/leave/create', 'title' => '<i class="fa-solid fa-calendar-plus"></i> บันทึกขออนุมัติการลา'], ['class' => 'btn btn-primary shadow open-modal', 'data' => ['size' => 'modal-lg']]) ?>
        </div>

        <?php
        echo $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider
        ]);
        ?>

    </div>
</div>
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
<?php Pjax::end(); ?>