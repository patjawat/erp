<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetItemSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>
    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>
    
<div class="d-flex justify-content-between align-items-center gap-2">

    <?= $form->field($model, 'title')->textInput(['placeholder' => 'ค้นหาชื่อ,ชื่อทรัพย์สิน...'])->label(false) ?>
    <?php
                // echo $form->field($model, 'category_id')->widget(Select2::classname(), [
                //     'data' =>[],
                //     'options' => ['placeholder' => 'ระบุประเภทรัพย์สิน...'],
                //     'pluginOptions' => [
                //         'allowClear' => true,
                //         'width' => '500px',
                //     ],
                //      'pluginEvents' => [
                //         "select2:select" => "function() { 
                //             $(this).submit(); 
                //         }",
                //     ],
                //     ])->label(false);
                    ?>

<div class="form-group">
    <?= Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i>', ['class' => 'btn btn-primary']) ?>
</div>

</div>
    <?php ActiveForm::end(); ?>

