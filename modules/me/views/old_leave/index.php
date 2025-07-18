<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\modules\lm\models\Leave;
/** @var yii\web\View $this */
/** @var app\modules\lm\models\LeaveSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'ทะเบียนประวัติการลา';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-folder-check"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>



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
                    <span class="badge rounded-pill badge-soft-primary text-primary fs-13 px-2"><i class="bi bi-exclamation-circle-fill"></i> อยู่ระหว่างรอผู้อำนวยการ/อนุมัติ<span>
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

<div class="card text-start">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h6><i class="bi bi-ui-checks"></i> ทะเบียนประวัติการลา <span class="badge rounded-pill text-bg-primary"><?php echo number_format($dataProvider->getTotalCount(),0)?></span> รายการ</h6>
         
            
            <div class="d-flex gap-3">
                <?php foreach($searchModel->listStatusSummary() as $status):?>
                    <?php echo Html::a('<span class="'.(($searchModel->status == $status['code']) ? 'border border-primary' : '').' badge rounded-pill badge-soft-primary text-primary fs-13 ">'.$status["title"].' <span class="badge text-bg-primary">  '.$status["total"].'</span></span>',[$status['code']],['data' => ['id' => $status['code']],'class' => 'filter-status'])?>
                <?php endforeach;?>
            </div>
            <h2>&nbsp;</h2>
                
        </div>
        <div class="d-flex justify-content-between  align-top align-items-center mt-4">
                <?php  echo $this->render('_search', ['model' => $searchModel]); ?>
                <?= Html::a('<i class="fa-solid fa-plus"></i> สร้างใหม่', ['/hr/leave/create','title' => '<i class="fa-solid fa-calendar-plus"></i> บันทึกขออนุมัติการลา'], ['class' => 'btn btn-primary shadow open-modal','data' => ['size' => 'modal-lg']]) ?>
        </div>

        
<?php echo  $this->render('@app/modules/hr/views/leave/list', [
        'searchModel' => $searchModel,
        'dataProvider' => $dataProvider
    ]);
      ?>

</div>
</div>