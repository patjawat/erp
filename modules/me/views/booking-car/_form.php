<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\components\UserHelper;
use app\modules\dms\models\DocumentsDetail;

$me = UserHelper::GetEmployee();
$documents = DocumentsDetail::find()->where(['name' => 'comment','to_id' => $me->id])->all();
$list =  ArrayHelper::map($documents, 'id',function($model){
    return $model->document->topic;
});
/** @var yii\web\View $this */
/** @var app\modules\booking\models\BookingCar $model */
/** @var yii\widgets\ActiveForm $form */
?>
<div class="row">
    <div class="col-8">

        <div class="card">
            <div class="card-body">

                <?php $form = ActiveForm::begin(); ?>
                <div class="row">
<div class="col-6">
    <div class="row">
        <div class="col-8"><?= $form->field($model, 'date_start')->textInput()->label('ต้องการใช้รถตั้งแต่วันที่') ?></div>
        <div class="col-4"><?= $form->field($model, 'time_start')->textInput(['maxlength' => true]) ?></div>
    </div>
    </div>
    <div class="col-6">
    <div class="row">
        <div class="col-8"><?= $form->field($model, 'date_end')->textInput()->label('ถึงวันที่') ?></div>
        <div class="col-4"><?= $form->field($model, 'time_end')->textInput(['maxlength' => true]) ?></div>
    </div>

</div>
                </div>

                <?php
                echo $form->field($model, 'document_id')->widget(Select2::classname(), [
                    'data' => $list,
                    'options' => ['placeholder' => 'เลือกหนังสืออ้างอิง ...'],
                    'pluginOptions' => [
                        'allowClear' => true,
                        // 'dropdownParent' => '#main-modal',
                    ],
                ])->label('หนังสืออ้างอิง');
                ?>
                <?= $form->field($model, 'booking_type')->textInput(['maxlength' => true]) ?>


                <?= $form->field($model, 'urgent')->textInput(['maxlength' => true]) ?>

                <?= $form->field($model, 'asset_item_id')->textInput(['maxlength' => true]) ?>

                <?= $form->field($model, 'location')->textInput(['maxlength' => true]) ?>

                <?= $form->field($model, 'data_json')->textInput() ?>

                <?= $form->field($model, 'status')->textInput(['maxlength' => true]) ?>

               
                <?= $form->field($model, 'driver_id')->textInput(['maxlength' => true]) ?>

                <?= $form->field($model, 'leader_id')->textInput(['maxlength' => true]) ?>

                <div class="form-group">
                    <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
                </div>

                <?php ActiveForm::end(); ?>

            </div>
        </div>


    </div>
    <div class="col-4">
        <div class="card">
            <div class="card-body">

                lisCaritems
            </div>
        </div>

    </div>
</div>