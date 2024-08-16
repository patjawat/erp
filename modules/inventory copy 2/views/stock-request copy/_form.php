<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;
use app\modules\inventory\models\Warehouse;
use yii\helpers\ArrayHelper;
/** @var yii\web\View $this */
/** @var app\modules\inventory\models\Stock $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="stock-movement-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'to_warehouse_id')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'ref')->hiddenInput()->label(false) ?>
    <?php
                echo $form->field($model, 'from_warehouse_id')->widget(Select2::classname(), [
                    'data' => ArrayHelper::map(Warehouse::find()->all(), 'id', 'warehouse_name'),
                    'options' => ['placeholder' => 'กรุณาเลือก'],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'dropdownParent' => '#main-modal',
                    ],
                ])->label('คลัง');
            ?>
    <?= $form->field($model, 'movement_type')->dropDownList([ 'receive' => 'รับเข้า', 'request' => 'เบิก', 'transfer' => 'Transfer', ], ['prompt' => '']) ?>

    <div class="form-group mt-3 d-flex justify-content-center">
        <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary', 'id' => 'summit']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
