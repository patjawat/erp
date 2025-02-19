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


<?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            [
                'label' => 'เรื่อง',
                'value' => 'ขอ'.$model->reason
            ],
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
    <?= Html::a('<i class="fa-solid fa-xmark"></i> ขอยกเลิก', ['delete', 'id' => $model->id], [
        'class' => 'btn btn-danger shadow rounded-pill',
        'data' => [
            'confirm' => 'คุณต้องการลบรายการนี้ใช่หรือไม่?',
            'method' => 'post',
        ],
    ]) ?>

</div>


