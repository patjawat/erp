<?php

use app\components\AppHelper;
use kartik\form\ActiveForm;
// use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetDetail $model */
/** @var yii\widgets\ActiveForm $form */
?>
<style>
.col-form-label {
    text-align: end;
}

/* #assetdetail-data_json-date_start,
#assetdetail-data_json-date_start2 {
    width: 130px;
} */
</style>
<div class="form-tax">

    <?php $form = ActiveForm::begin([
    'id' => 'form-tax',
    'type' => ActiveForm::TYPE_HORIZONTAL,
    'formConfig' => ['labelSpan' => 4, 'deviceSize' => ActiveForm::SIZE_X_LARGE],
    'fieldConfig' => ['options' => ['class' => 'form-group mb-1 mr-2 me-2']],
    'enableAjaxValidation'      => true,//เปิดการใช้งาน AjaxValidation
    'validationUrl' =>['/am/asset-detail/validator']
]);?>

    <?=$form->field($model, 'ref')->hiddenInput()->label(false)?>
    <?=$form->field($model, 'name')->hiddenInput()->label(false)?>

    <h5><i class="fa-solid fa-user-injured"></i> พรบ.</h5>
    <div class="row">
        <div class="col-6">
            <?=$form->field($model, 'data_json[company]')->textInput(['maxlength' => true])->label('บริษัท')?>
            <?=$form->field($model, 'data_json[number]')->textInput(['maxlength' => true])->label('กรมธรรม์เลขที่')?>
            <?=$form->field($model, 'data_json[price]', ['labelSpan' => 4])->textInput(['type' => 'number'])->label('เบี้ยประกัน')?>
            <?=$form->field($model, 'data_json[sale]')->textInput(['maxlength' => true])->label('ตัวแทน')?>
        </div>

        <div class="col-6">
            <?php echo $form->field($model, 'data_json[date_start]', ['labelSpan' => 4],
                        )->widget(\yii\widgets\MaskedInput::className(), [
                            'mask' => '99/99/9999 99:99',
                            ])->label('วันที่') ?>

            <?php echo $form->field($model, 'data_json[date_end]')->widget(\yii\widgets\MaskedInput::className(), [
    'mask' => '99/99/9999 99:99',
    ])->label('ถึง') ?>
            <?=$form->field($model, 'data_json[price]', ['labelSpan' => 4])->textInput(['type' => 'number'])->label('เบี้ยประกัน')?>
            <?=$form->field($model, 'data_json[phone]', ['labelSpan' => 4])->textInput(['type' => 'number'])->label('โทร')?>

        </div>
    </div>
    <hr>



    <div class="row">



        <div class="col-6">
            <h5><i class="fa-solid fa-user-injured"></i> ข้อมูลการต่อภาษี</h5>
            <?php echo $form->field($model, 'date_start')->widget(\yii\widgets\MaskedInput::className(), [
                            'mask' => '99/99/9999',
                        ])->label('วันที่ต่อภาษี') ?>
            <?php echo $form->field($model, 'date_end')->widget(\yii\widgets\MaskedInput::className(), [
                            'mask' => '99/99/9999',
                        ])->label('วันที่ครบกำหนดชำระ') ?>
            <?=$form->field($model, 'data_json[price]', ['labelSpan' => 4])->textInput(['type' => 'number'])->label('ค่าภาษี')?>
        </div>



        <div class="col-6">
            <h5><i class="fa-solid fa-car-burst"></i> ประกันภัย</h5>
            <?=$form->field($model, 'data_json[company2]')->textInput(['maxlength' => true])->label('บริษัท')?>
            <?=$form->field($model, 'data_json[number2]')->textInput(['maxlength' => true])->label('กรมธรรม์เลขที่')?>
            <div class="d-flex">
                <div class="col-6">
                    <?php echo $form->field($model, 'data_json[date_start2]',['labelSpan' => 8],
                        )->widget(\yii\widgets\MaskedInput::className(), [
                            'mask' => '99/99/9999 99:99',
                        ])->label('วันที่') ?>
                </div>
                <div class="col-6">
                    <?php echo $form->field($model, 'data_json[date_end2]')->widget(\yii\widgets\MaskedInput::className(), [
                    'mask' => '99/99/9999 99:99',
                ])->label('ถึง') ?>
                </div>
            </div>
            <?=$form->field($model, 'data_json[price2]', ['labelSpan' => 4])->textInput(['type' => 'number'])->label('เบี้ยประกัน')?>
            <?=$form->field($model, 'data_json[sale2]')->textInput(['maxlength' => true])->label('ตัวแทน')?>
            <?=$form->field($model, 'data_json[phone2]', ['labelSpan' => 4])->textInput(['maxlength' => true])->label('โทร')?>
        </div>
    </div>
    <hr>
    <div class="col-12">
        <div class="form-group mb-1 mr-2 me-2 highlight-addon row field-assetdetail-data_json-phone">
            <label class="col-form-label has-star col-md-2"></label>
            <div class="col-md-10">
                <div class="form-group d-flex justify-content-center">
                    <?=AppHelper::btnSave();?>
                </div>

            </div>
        </div>
    </div>
</div>
<hr>
<div class="col-sm-12">
    <?=$model->Upload($model->ref, 'asset_pic')?>
</div>

<?php ActiveForm::end();?>

</div>


<?php
$js = <<<JS

$('#form-tax').on('beforeSubmit', function (e) {
    var form = $(this);
    $.ajax({
        url: form.attr('action'),
        type: 'post',
        data: form.serialize(),
        dataType: 'json',
        success: async function (response) {
            form.yiiActiveForm('updateMessages', response, true);
            if(response.status == 'success') {
                closeModal()
                success()
                await  $.pjax.reload({ container:"#view-container", history:false,replace: false,timeout: false});
            }
        }
    });
    return false;
});

JS;
$this->registerJS($js)
?>