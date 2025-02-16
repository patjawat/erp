<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\Room $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Rooms', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="room-view">
               
    <?php echo Html::img($model->showImg(),['class' => 'rounded-3','style' => 'max-width:200px']);?>
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'code',
            'title',
        ],
    ]) ?>

<div class="d-flex justify-content-center gap-3">

    <?php echo Html::a('<i class="fa-solid fa-pencil"></i> แก้ไข',['/booking/meeting-room/update','id' => $model->id,'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'],['class' => 'open-modal btn btn-warning ','data' => ['size' => 'modal-lg']])?>
    <?php echo Html::a('<i class="fa-regular fa-trash-can"></i> ลบทิ้ง',['/booking/meeting-room/delete','id' => $model->id,'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'],['class' => 'btn btn-danger delete-item','data' => ['size' => 'modal-lg']])?>
    </div>
</div>
