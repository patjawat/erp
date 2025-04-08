<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\RoomSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="room-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

<div class="d-flex gap-2">
<?php echo $form->field($model, 'title')->textInput(['placeholder' => 'คำค้นหา...','class' => 'form-control'])->label('ชื่อห้องประชุม') ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
