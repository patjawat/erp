<?php

use kartik\widgets\ActiveForm;
use yii\helpers\Html;

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
<div class="d-none">
    <?php
        echo $form->field($model, 'q_category')->checkboxList(
            $model->ListProductType(),
            ['custom' => true, 'id' => 'custom-checkbox-list']
        )->label(false);
    ?>
</div>
    <?php echo $form->field($model, 'title')->textInput(['placeholder' => 'ค้นหา...'])->label(false) ?>
    <?php ActiveForm::end(); ?>

</div>
