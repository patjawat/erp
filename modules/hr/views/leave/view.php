<?php

use yii\web\View;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\widgets\DetailView;
use app\components\AppHelper;

/** @var yii\web\View $this */
/** @var app\modules\lm\models\Leave $model */
$this->title = 'ระบบลา';
$this->params['breadcrumbs'][] = ['label' => 'Leaves', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-folder-check"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php Pjax::begin(['id' => 'leave', 'timeout' => 500000]); ?>
<div class="row">
    <div class="col-xl-8 col-sm-12">


        <div class="row">
            <div class="col-12">


            <div class="card border-0 rounded-3">
    <div class="card-body">
        <div class="d-flex justify-content-between">
        <?= $model->employee->getAvatar(false) ?>
                <div class="d-flex align-items-center gap-3">
                <?php if ($model->status !== 'Cancel'): ?>
                    <?php if ($model->status == 'Allow'): ?>
                    <i class="bi bi-person-check fs-3 text-primary"></i> อนุมัติให้ลาได้
                    <?php else: ?>

                    <?= ($model->status == 'Pending') ? Html::a('<i class="fa-regular fa-pen-to-square me-1"></i> แก้ไข', ['/hr/leave/update', 'id' => $model->id, 'title' => '<i class="fa-solid fa-calendar-plus"></i> แก้ไขวันลา'], ['class' => 'btn btn-warning rounded-pill open-modal', 'data' => ['size' => 'modal-lg']]) : '' ?>

                    <?php if ($model->status == 'ReqCancel'): ?>
                    <?php echo Html::a('<i class="fa-solid fa-circle-exclamation text-danger"></i> อนุมัติยกเลิกวันลา', ['/hr/leave/cancel', 'id' => $model->id], ['class' => 'btn btn-warning rounded-pill shadow cancel-btn']) ?>

                    <?php else: ?>
                    <?= $model->status !== 'Reject' ? Html::a('<i class="bi bi-exclamation-circle"></i> ขอยกเลิก', ['/hr/leave/req-cancel', 'id' => $model->id], [
                        'class' => 'req-cancel-btn btn btn btn-danger rounded-pill shadow',
                    ]) : '' ?>
                    <?php endif; ?>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
        </div>

        <table class="table border-0 table-striped-columns mt-3">
            <tbody>
                <tr>
                    <td>เรื่อง : </td>
                    <td><span class="text-pink fw-semibold">ขอ<?php echo ($model->leaveType->title ?? '-') ?></span></td>

                    <td>เขียนเมื่อ : </td>
                    <td><span class="text-pink fw-semibold"><?php echo $model->viewCreated() ?></span></td>
                </tr>
                <tr>
                    <td>ระหว่างวันที่ : </td>
                    <td>
                    <i class="fa-solid fa-calendar-check"></i> <?php echo AppHelper::convertToThai($model->date_start ?? '') ?> ถึงวันที่ <i class="fa-solid fa-calendar-check"></i> <?php echo AppHelper::convertToThai($model->date_end ?? '') ?>
                    </td>

                    <td>เป็นเวลา : </td>
                    <td>
                    <span class="badge rounded-pill badge-soft-danger text-primary fs-13 "><?php echo $model->total_days ?> วัน</span></td>
                </tr>

                <tr>
                    <td>เหตุผล : </td>
                    <td colspan="4"><?php echo $model->data_json['reason'] ?? '-' ?></td>
                    
                   
                </tr>
                <tr>
                    <td>ระหว่างลาติดต่อ : </td>
                    <td><?php echo $model->data_json['address'] ?? '-' ?></td>
                    <td>โทรศัพท์ : </td>
                    <td><?php echo $model->data_json['phone'] ?? '-' ?></td>
                </tr>
                <tr>
                    <?php if ($model->status == 'Cancel'): ?>
                    <td>สถานะ : </td>
                    <td><?php echo $model->viewStatus() ?></td>
                    <td>ผู้ดำเนินการยกเลิก : </td>
                    <td><?php echo ($model->data_json['cancel_fullname'] ?? '-') . (' วันเวลา ' . Yii::$app->thaiFormatter->asDateTime($model->data_json['cancel_date'], 'medium') ?? '') ?></td>
                    <?php else: ?>
                        <td>สถานะ : </td>
                        <td  colspan="4"><?php echo $model->viewStatus() ?></td>
                    <?php endif ?>
                </tr>

                <tr>
                <td>ประวัติการลา : </td>
                <td><?php echo Html::a('<i class="bi bi-clock-history"></i> ดูประวัติเพิ่มเติม', ['/hr/leave/view-history','emp_id' => $model->emp_id], ['class' => 'btn btn-sm btn-primary rounded-pill shadow open-modal','data' => ['size' => 'modal-xxl']]) ?></td>
                <td>วันลาพักผ่อนสม : </td>
                <td><?php echo $model->sumLeavePermission()['sum']?></td>
                </tr>
                    
                
            </tbody>
        </table>
    </div>
</div>

            
            </div>
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <?= $this->render('view_summary', ['model' => $model]) ?>
                    </div>
                </div>
            </div>
        </div>


    </div>
    <div class="col-xl-4 col-sm-12">

        <?php echo $this->render('list_approve', ['model' => $model]) ?>
    </div>
</div>

<?php Pjax::end(); ?>

<?php
$js = <<< JS

    const confirmCancel = (e) => {
        e.preventDefault();
        const url = e.target.href;
        Swal.fire({
            title: 'ยืนยัน?',
            text: "อนุมัติให้ยกเลิกวันลาใช่หรือไม!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'ใช้',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        })
    }

    const cancelButtons = document.querySelectorAll('.cancel-btn');
    cancelButtons.forEach(button => {
        button.addEventListener('click', confirmCancel);
    });

    const confirmReqCancel = (e) => {
        e.preventDefault();
        const url = e.target.href;
        Swal.fire({
            title: 'ยืนยัน?',
            text: "คุณต้องการขอยกเลิกใช่หรือไม!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'ใช่',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        })
    }
    const reqCancelButtons = document.querySelectorAll('.req-cancel-btn');
    reqCancelButtons.forEach(button => {
        button.addEventListener('click', confirmReqCancel);
    });


    JS;
$this->registerJS($js, View::POS_END);
?>