<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\ProductTypeSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>
    <div class="row">
        <div class="col-11">
            <?php echo $form->field($model, 'q')->textInput(['placeholder' => 'ค้นหา...'])->label(false) ?>
        </div>
        <div class="col-1">
            <?= Html::submitButton('<i class="bi bi-search"></i>', ['class' => 'btn btn-primary', 'id' => 'summit']) ?>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
