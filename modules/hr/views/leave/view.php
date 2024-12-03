<?php

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
        <div>
            <?php // $model->CreateBy()->getAvatar(false)?>
            <?=$model->employee->getAvatar(false)?>
        </div>

    <p>
    <?=Html::a('<i class="fa-regular fa-pen-to-square me-1"></i> แก้ไข',['/hr/leave/update','id' => $model->id,'title' => '<i class="fa-solid fa-calendar-plus"></i> แก้ไขวันลา'],['class' => 'btn btn-sm btn-warning rounded-pill open-modal','data' => ['size' => 'modal-lg']]) ?>

        <?= Html::a('<i class="fa-solid fa-xmark"></i> ยกเลิก', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-sm btn-danger rounded-pill shadow',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    </div>
</div>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            [
                'label' => 'เรื่อง',
                'value' => 'ขอ'.$model->leaveType->title
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
                'value' => $model->sum_days.' วัน'
            ],
            [
                'label' => 'เหตุผล',
                'value' =>$model->data_json['note'] ?? $model->data_json['note'] ?? '-'
            ],
           
            [
                'label' => 'ระหว่างลาติดต่อ',
                'value' =>$model->data_json['address'] ?? $model->data_json['address'] ?? '-'
            ],
            // [
            //     'label' => '  มอบหมายงานให้',
            //     'format' => 'html',
            //     'value' =>$model->data_json['leave_work_send_id'] ?? $model->data_json['leave_work_send_id'] ?? '-'
            // ],
          
        ],
    ]) ?>

<?=$this->render('view_summary',['model' => $model])?>
</div>
<div class="col-xl-6 col-sm-12">
 
<?=$this->render('checker',['model' => $model])?>



</div>
</div>

<?php Pjax::end(); ?>