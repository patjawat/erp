<?php

use kartik\widgets\ActiveForm;
use yii\helpers\Html;
use kartik\select2\Select2;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\ProductSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="product-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-start gap-2">
            <?php echo $form->field($model, 'title')->textInput(['placeholder' => 'ค้นหา...'])->label(false) ?>
            <?= Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i>', ['class' => 'btn btn-primary', 'id' => 'summit']) ?>
        </div>
    </div>
</div>
    <?php ActiveForm::end(); ?>

</div>
