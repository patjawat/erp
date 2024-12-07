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
<div class="col-xl-6 col-sm-12">
<div class="card text-start">
    <div class="card-body d-flex justify-content-between align-items-center">
            <?= $model->employee->getAvatar(false) ?>
    <p>
    <?php if ($model->status !== 'Cancel'): ?>
    <?= Html::a('<i class="fa-regular fa-pen-to-square me-1"></i> แก้ไข', ['/hr/leave/update', 'id' => $model->id, 'title' => '<i class="fa-solid fa-calendar-plus"></i> แก้ไขวันลา'], ['class' => 'btn btn-sm btn-warning rounded-pill open-modal', 'data' => ['size' => 'modal-lg']]) ?>
        <?= Html::a('<i class="fa-solid fa-xmark"></i> ยกเลิก', ['/hr/leave/req-cancel', 'id' => $model->id], [
            'class' => 'req-cancel-btn btn btn-sm btn-danger rounded-pill shadow',
        ]) ?>
    </p>
    <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-body">
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            [
                'label' => 'เรื่อง',
                'value' => 'ขอ' . $model->leaveType->title
            ],
            [
                'label' => 'ตั้งแต่วันที่',
                'value' => AppHelper::convertToThai($model->date_start)
            ],
            [
                'label' => 'ถึงวันที่',
                'value' => AppHelper::convertToThai($model->date_end)
            ],
            [
                'label' => 'เป็นเวลา',
                'value' => $model->sum_days . ' วัน'
            ],
            [
                'label' => 'เหตุผล',
                'value' => $model->data_json['note'] ?? $model->data_json['note'] ?? '-'
            ],
            [
                'label' => 'ระหว่างลาติดต่อ',
                'value' => $model->data_json['address'] ?? $model->data_json['address'] ?? '-'
            ],
            // [
            //     'label' => '  มอบหมายงานให้',
            //     'format' => 'html',
            //     'value' =>$model->data_json['leave_work_send_id'] ?? $model->data_json['leave_work_send_id'] ?? '-'
            // ],
        ],
    ]) ?>

</div>
</div>

<div class="card">
    <div class="card-body">

<?= $this->render('view_summary', ['model' => $model]) ?>
</div>
</div>
</div>
<div class="col-xl-6 col-sm-12">
 
<div class="card">
    <div class="card-body d-flex justify-content-between p-4">
        <h5><?php echo $model->leaveStatus->title ?></h5>
        <?php if ($model->status == 'ReqCancel'): ?>
            <?php echo Html::a('<i class="fa-solid fa-xmark"></i> อนุมัติยกเลิก', ['/hr/leave/req-cancel', 'id' => $model->id], [
                'class' => 'cancel-btn btn btn-sm btn-primary rounded-pill shadow',
            ]) ?>
            <?php else: ?>
        <p class="card-text">สถานะ</p>
        <?php endif; ?>
    </div>
</div>

<?= $this->render('checker', ['model' => $model]) ?>



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
            text: "คุณต้องการยกเลิกใช่หรือไม!",
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