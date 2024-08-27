<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\components\AppHelper;
use kartik\select2\Select2;
use iamsaint\datetimepicker\Datetimepicker;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\SupRequest $model */
/** @var yii\widgets\ActiveForm $form */
?>
<div class="card" style="color: #000000;">
    <div class="card-body"> 
        <div class="sup-request-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'ref')->hiddenInput(['maxlength' => true])->label(false)?>

    <?= $form->field($model, 'req_code')->textInput(['maxlength' => true]) ?>

    <?=$form->field($model, 'req_date')->widget(Datetimepicker::className(),[
                    'options' => [
                        'timepicker' => false,
                        'datepicker' => true,
                        'mask' => '99/99/9999',
                        'lang' => 'th',
                        'yearOffset' => 543,
                        'format' => 'd/m/Y', 
                    ],
                    ])->label('วันที่ขอ');
            ?>

    <?= $form->field($model, 'req_detail')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'req_vendor')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'req_amount')->textInput(['maxlength' => true]) ?>

    <!-- <?= $form->field($model, 'req_status')->textInput(['maxlength' => true]) ?> -->

   <?php
                echo $form->field($model, 'req_dep')->widget(Select2::classname(), [
                    'data' => $model->ListDepartment(),
                    'options' => ['placeholder' => 'เลือกหน่วยงาน'],
                    'pluginOptions' => [
                    'allowClear' => true,
                    ],
                ])->label('หน่วยงานที่ขอ');
            ?>


    <!-- <?= $form->field($model, 'data_json')->textInput() ?> -->

    <div class="form-group mt-4 d-flex justify-content-center">
            <?= AppHelper::BtnSave(); ?>
    </div>

    <?php ActiveForm::end(); ?>

            </div>
        </div>
    </div>
</div>
</div>
