<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\BookingCar $model */

$this->title = 'รายละเอียดขอใช้ยานพาหนะ';
$this->params['breadcrumbs'][] = ['label' => 'Booking Cars', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-route fs-1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>


<div class="flex-shrink-0 rounded p-5 mb-3" style="background-image:url(<?php echo $model->room ? $model->room->showImg() :  ''?>);background-size:cover;background-repeat:no-repeat;background-position:center;height:300px;">

</div>

<div class="d-flex justify-content-between align-items-center gap-3 mb-3">
    <h6 class="text-primary"><?php echo $model->reason?></h6>
    <div class="d-flex gap-3">
        <?php // Html::a('<i class="fa-solid fa-arrow-left"></i> กลับ', ['index'], ['class' => 'btn btn-light shadow rounded-pill']) ?>
        <?= Html::a('<i class="fa-solid fa-xmark"></i> ขอยกเลิก', ['delete', 'id' => $model->id], [
        'class' => 'btn btn-sm btn-danger shadow rounded-pill',
        'data' => [
            'confirm' => 'คุณต้องการลบรายการนี้ใช่หรือไม่?',
            'method' => 'post',
        ],
    ]) ?>
    </div>

</div>
<?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            [
                'label' => 'เริ่มวันเวลา',
                 'value' => Yii::$app->thaiFormatter->asDate($model->date_start, 'medium') . ' เวลา ' . $model->time_start
            ],
            [
                'label' => 'ถึงวันที่เวลา',
                 'value' => Yii::$app->thaiFormatter->asDate($model->date_end, 'medium') . ' เวลา ' . $model->time_end
            ],
            [
                'label' => 'ผู้ขอใช้บริการ',
                'value' => 'ขอ'.$model->reason
            ],
        ],
    ]) ?>

<div class="d-flex justify-content-center align-items-center gap-3">
    <?= Html::a('<i class="fa-solid fa-pen-to-square"></i> แก้ไข', ['update', 'id' => $model->id,'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไขการขอใช้ห้องประชุม'], ['class' => 'btn btn-warning shadow rounded-pill open-modal','data' => ['size' => 'modal-lg']]) ?>
    <button type="button" class="btn btn-secondary  rounded-pill shadow" data-bs-dismiss="modal"><i
            class="fa-regular fa-circle-xmark"></i> ปิด</button>
</div>
</div>


