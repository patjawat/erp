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
        <div class="card text-start">
            <div class="card-body d-flex justify-content-between align-items-center">
                <?= $model->employee->getAvatar(false) ?>
                <div class="d-flex align-items-center gap-3">
                <?php if ($model->status !== 'Cancel'): ?>
                    <?php if ($model->status == 'Allow'): ?>
                    <i class="bi bi-person-check fs-3 text-primary"></i> อนุมัติให้ลาได้
                    <?php else: ?>

                    <?= ($model->status == 'Pending') ?  Html::a('<i class="fa-regular fa-pen-to-square me-1"></i> แก้ไข', ['/hr/leave/update', 'id' => $model->id, 'title' => '<i class="fa-solid fa-calendar-plus"></i> แก้ไขวันลา'], ['class' => 'btn btn-warning rounded-pill open-modal', 'data' => ['size' => 'modal-lg']]) : ''?>

                    <?php if ($model->status == 'ReqCancel'): ?>
                    <?php echo Html::a('<i class="fa-solid fa-circle-exclamation text-danger"></i> อนุมัติยกเลิกวันลา',['/hr/leave/cancel', 'id' => $model->id],['class'=> 'btn btn-warning rounded-pill shadow cancel-btn'])?>

                    <?php else: ?>
                    <?= Html::a('<i class="bi bi-exclamation-circle"></i> ขอยกเลิก', ['/hr/leave/req-cancel', 'id' => $model->id], [
                            'class' => 'req-cancel-btn btn btn btn-danger rounded-pill shadow',
                        ]) ?>
                    <?php endif; ?>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                    <?= DetailView::widget([
    'model' => $model,
    'attributes' => array_filter([
        [
            'label' => 'สถานะ',
            'format' => 'html',
            'value' => $model->viewStatus()
        ],
        $model->status == 'Cancel' ? [
            'label' => 'ผู้ดำเนินการยกเลิก',
            'format' => 'html',
            'value' => ($model->data_json['cancel_fullname'] ?? '-'). (' วันเวลา '.Yii::$app->thaiFormatter->asDateTime($model->data_json['cancel_date'],'medium') ?? '')
        ] : null,
        [
            'label' => 'เรื่อง',
            'value' => 'ขอ' . ($model->leaveType->title ?? '-')
        ],
        [
            'label' => 'ตั้งแต่วันที่',
            'value' => AppHelper::convertToThai($model->date_start ?? '')
        ],
        [
            'label' => 'ถึงวันที่',
            'value' => AppHelper::convertToThai($model->date_end ?? '')
        ],
        [
            'label' => 'เป็นเวลา',
            'value' => $model->sum_days . ' วัน'
        ],
        [
            'label' => 'เหตุผล',
            'value' => $model->data_json['note'] ?? '-'
        ],
        [
            'label' => 'ระหว่างลาติดต่อ',
            'value' => $model->data_json['address'] ?? '-'
        ],
    ])
]) ?>


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