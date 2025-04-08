<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\BookingCar $model */

$this->title = 'รายละเอียดขอใช้ยานพาหนะ';
$this->params['breadcrumbs'][] = ['label' => 'Booking Cars', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

$msg = "ขออนุญาตใช้รถยนต์".($model->carType->title);
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-route fs-1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<div class="row">
    <div class="col-12">

        <div class="card">
            <div class="card-body">
                <div class="mb-2">
                    <?php // echo $model->user->employee->getavatar(false,$msg)?>
                </div>



                <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'reason',
            [
                'label' => 'สถานที่ไป',
                'value' => function($model){
                    return $model->locationOrg->title;
                }
            ],

            [
                'label' => 'วันที่',
                'value' => function($model){
                    return "วันที่ " . Yii::$app->thaiFormatter->asDate($model->date_start, 'medium').' ถึง '.Yii::$app->thaiFormatter->asDate($model->date_end, 'medium');
                }
            ],
            [
                'label' => 'ผู้ร่วมเดินทาง',
                'value' => function($model){
                    // return $model->data_json['total_person_count']." คน";
                }
            ],
            [
                'label' => 'หนังอ้างอิง',
                'value' => function($model){
                    return '-';
                }
            ],
        ],
    ]) ?>


            </div>
        </div>


    </div>
</div>