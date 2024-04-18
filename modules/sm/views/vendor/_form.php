<?php

use yii\web\View;
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use app\components\AppHelper;
use kartik\select2\Select2;
use iamsaint\datetimepicker\Datetimepicker;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\SupVendor $model */
/** @var yii\widgets\ActiveForm $form */
?>
<div class="card" style="color: #000000;">
    <div class="card-body">
        <div class="sup-vendor-form">
            <?php $form = ActiveForm::begin([
    'id' => 'form-vendor',
    'enableAjaxValidation'=> true,//เปิดการใช้งาน AjaxValidation
    'validationUrl' =>['/sm/vendor/validator']
    ]); ?>

            <?= $form->field($model, 'ref')->hiddenInput(['maxlength' => true])->label(false)?>
            <?= $form->field($model, 'name')->hiddenInput(['maxlength' => true])->label(false) ?>

            <div class="row">
                <div class="col-6">
                    <?= $form->field($model, 'title')->textInput(['maxlength' => true])->label('ตัวแทนจำหน่าย') ?>

                </div>
                <div class="col-6">
                    <?= $form->field($model, 'code')->textInput(['maxlength' => true])->label('เลขประจำตัวผู้เสียภาษี')  ?>
                </div>

                <div class="col-12">
                    <?= $form->field($model, 'data_json[address]')->textArea(['maxlength' => true])->label('ที่อยู่') ?>
                </div>

                <div class="col-6">
                    <?= $form->field($model, 'data_json[contact_name]')->textInput(['maxlength' => true])->label('ชื่อผู้ติดต่อ') ?>
                </div>
                <div class="col-6">
                    <div class="d-flex gap-4">
                        <?= $form->field($model, 'data_json[phone]')->textInput(['maxlength' => true])->label('เบอร์โทร')  ?>
                        <?= $form->field($model, 'data_json[email]')->textInput(['maxlength' => true])->label('อีเมล')  ?>

                    </div>
                </div>
                <div class="col-6">
                    <?= $form->field($model, 'data_json[account_name]')->textInput(['maxlength' => true])->label('ชื่อบัญชี') ?>
                </div>
                <div class="col-6">
                <div class="d-flex gap-4">
                    <?= $form->field($model, 'data_json[bank_name]')->textInput(['maxlength' => true])->label('ชื่อธนาคาร') ?>
                    <?= $form->field($model, 'data_json[account_number]')->textInput(['maxlength' => true])->label('เลขบัญชี') ?>
                </div>
                </div>
              
                 
                    <!-- End Row On col-6 -->
                    <?= $form->field($model, 'data_json[fax]')->textInput(['maxlength' => true])->label('Fax') ?>
           
          


               


                </div>

                <div class="form-group mt-4 d-flex justify-content-center">
                    <?= AppHelper::BtnSave(); ?>
                </div>

                <?php ActiveForm::end(); ?>

            </div>
        </div>
    </div>
</div>



<?php

$js = <<<JS

 $('#form-vendor').on('beforeSubmit', function (e) {
    var form = $(this);
    console.log('Submit');
    $.ajax({
        url: form.attr('action'),
        type: 'post',
        data: form.serialize(),
        dataType: 'json',
        success: async function (response) {
            console.log(response);
            form.yiiActiveForm('updateMessages', response, true);
            if(response.status == 'success') {
                success()
                closeModal()
                $.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                                                                                 
            }
        }
    });
    return false;
});
 


JS;
$this->registerJS($js, View::POS_END)
    ?>