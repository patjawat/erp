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


<div class="flex-shrink-0 rounded p-5 mb-3" style="background-image:url(<?php echo $model->room->showImg()?>);background-size:cover;background-repeat:no-repeat;background-position:center;height:300px;">

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



